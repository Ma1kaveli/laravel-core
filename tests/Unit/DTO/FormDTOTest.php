<?php

namespace Tests\Unit\DTO;

use Core\DTO\FormDTO;
use Core\DTO\Resolvers\BaseAwareOrganizationContextResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Fakes\FakeFormDTO;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class FormDTOTest extends TestCase
{
    /**
     * Тест: основной use-case - создание DTO из Request с processBaseData
     */
    public function test_main_use_case_from_request(): void
    {
        // 1. Настройка конфигурации
        Config::set('core.form_dto.common_request_fields', ['organization_id']);
        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);

        // 2. Пользователь
        $user = new FakeUser();
        $user->id = 1;
        $user->role = (object) ['organization_id' => 10, 'id' => 5, 'is_base' => true];

        Auth::shouldReceive('user')->once()->andReturn($user);

        // 3. Мокаем ConverterDTO ПРАВИЛЬНО
        $converterMock = Mockery::mock('overload:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getRequestData')
            ->andReturnUsing(function($data) {
                // Просто возвращаем данные как есть (симуляция работы конвертера)
                return $data;
            });

        // 4. Мокаем ресолвер
        $resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $resolverMock->shouldReceive('resolve')
            ->andReturn([
                'organization_id' => 15,
                'role_id' => 5
            ]);

        $this->app->instance(BaseAwareOrganizationContextResolver::class, $resolverMock);

        // 5. Создаем Request
        $request = Request::create('/test', 'POST', [
            'organizationId' => '15',
            'name' => 'John',
            'email' => 'john@example.com',
            'customField' => '42'
        ]);

        // 6. Вызываем fromRequest
        $dto = FakeFormDTO::fromRequest($request, 100);

        // 7. Проверяем результат
        $this->assertSame($user, $dto->user);
        $this->assertSame(15, $dto->organizationId);
        $this->assertSame(5, $dto->roleId);
        $this->assertSame(100, $dto->id);
        $this->assertSame('John', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
    }

    /**
     * Тест: базовый конструктор работает
     */
    public function test_constructor_sets_base_properties(): void
    {
        $user = new FakeUser();

        $dto = new FakeFormDTO(
            name: 'Test',
            email: 'test@example.com',
            customField: 123,

            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100
        );

        $this->assertSame($user, $dto->user);
        $this->assertSame(10, $dto->organizationId);
        $this->assertSame(5, $dto->roleId);
        $this->assertSame(100, $dto->id);
        $this->assertSame('Test', $dto->name);
        $this->assertSame('test@example.com', $dto->email);
        $this->assertSame(123, $dto->customField);
    }

    /**
     * Тест: getCommonRequestFields возвращает конфиг
     */
    public function test_get_common_request_fields(): void
    {
        Config::set('core.form_dto.common_request_fields', ['org_id', 'tenant_id']);

        $reflection = new \ReflectionClass(FormDTO::class);
        $method = $reflection->getMethod('getCommonRequestFields');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertEquals(['org_id', 'tenant_id'], $result);
    }

    /**
     * Тест: абстрактный класс нельзя инстанцировать напрямую
     */
    public function test_cannot_instantiate_abstract_class(): void
    {
        $this->expectException(\Error::class);

        new FormDTO(new FakeUser(), 1, 2, 3);
    }

    /**
     * Тест: наследник может быть инстанцирован
     */
    public function test_can_instantiate_concrete_subclass(): void
    {
        $user = new FakeUser();

        $dto = new FakeFormDTO(
            name: 'Test',
            email: 'test@example.com',
            customField: 1,

            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100
        );

        $this->assertInstanceOf(FormDTO::class, $dto);
    }

    /**
     * Тест: создание DTO без Request
     */
    public function test_create_directly_without_request(): void
    {
        $user = new FakeUser();

        $dto = FakeFormDTO::createDirect(
            user: $user,
            name: 'Direct DTO',
            organizationId: 20,
            roleId: 7
        );

        $this->assertSame($user, $dto->user);
        $this->assertSame(20, $dto->organizationId);
        $this->assertSame(7, $dto->roleId);
        $this->assertSame('Direct DTO', $dto->name);
        $this->assertNull($dto->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
