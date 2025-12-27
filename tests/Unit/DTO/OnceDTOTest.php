<?php

namespace Tests\Unit\DTO;

use Core\DTO\OnceDTO;
use Core\DTO\Resolvers\BaseAwareOrganizationContextResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class OnceDTOTest extends TestCase
{
    /**
     * Тест: создание OnceDTO через make()
     */
    public function test_make_creates_instance_with_correct_properties(): void
    {
        // Мокаем пользователя
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        // Мокаем ресолвер
        $resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $resolverMock->shouldReceive('resolve')
            ->with($user, [])
            ->andReturn([
                'organization_id' => 10,
                'role_id' => 5
            ]);

        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $resolverMock);

        // Создаем OnceDTO
        $dto = OnceDTO::make(100, ['param1' => 'value1', 'param2' => 'value2']);

        // Проверяем свойства
        $this->assertSame($user, $dto->authUser);
        $this->assertSame($user, $dto->user);
        $this->assertSame(100, $dto->id);
        $this->assertSame(10, $dto->organizationId);
        $this->assertSame(5, $dto->roleId);
        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2'], $dto->params);
    }

    /**
     * Тест: make() с пустыми params
     */
    public function test_make_with_empty_params(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $resolverMock->shouldReceive('resolve')
            ->with($user, [])
            ->andReturn([
                'organization_id' => 15,
                'role_id' => 3
            ]);

        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $resolverMock);

        $dto = OnceDTO::make(200);

        $this->assertSame($user, $dto->authUser);
        $this->assertSame($user, $dto->user);
        $this->assertSame(200, $dto->id);
        $this->assertSame(15, $dto->organizationId);
        $this->assertSame(3, $dto->roleId);
        $this->assertEmpty($dto->params);
    }

    /**
     * Тест: appendParams возвращает новый экземпляр с добавленными параметрами
     */
    public function test_append_params_returns_new_instance(): void
    {
        // Создаем исходный DTO напрямую (без make)
        $user = new FakeUser();

        $originalDto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100,
            params: ['existing' => 'value']
        );

        // Добавляем параметры
        $newDto = $originalDto->appendParams(['new' => 'data', 'another' => 'param']);

        // Проверяем, что это новый объект
        $this->assertNotSame($originalDto, $newDto);

        // Проверяем свойства нового DTO
        $this->assertSame($user, $newDto->authUser);
        $this->assertSame($user, $newDto->user);
        $this->assertSame(100, $newDto->id);
        $this->assertSame(10, $newDto->organizationId);
        $this->assertSame(5, $newDto->roleId);

        // Проверяем параметры (старые + новые)
        $this->assertEquals([
            'existing' => 'value',
            'new' => 'data',
            'another' => 'param'
        ], $newDto->params);

        // Исходный DTO не должен измениться
        $this->assertEquals(['existing' => 'value'], $originalDto->params);
    }

    /**
     * Тест: appendParams перезаписывает существующие параметры
     */
    public function test_append_params_overwrites_existing(): void
    {
        $user = new FakeUser();

        $originalDto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100,
            params: ['param1' => 'old', 'param2' => 'keep']
        );

        $newDto = $originalDto->appendParams(['param1' => 'new', 'param3' => 'added']);

        // param1 перезаписан, param2 сохранен, param3 добавлен
        $this->assertEquals('new', $newDto->params['param1']);
        $this->assertEquals('keep', $newDto->params['param2']);
        $this->assertEquals('added', $newDto->params['param3']);
    }

    /**
     * Тест: свойства только для чтения
     */
    public function test_properties_are_readonly(): void
    {
        $user = new FakeUser();

        $dto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100,
            params: ['test' => 'value']
        );

        $reflection = new \ReflectionObject($dto);

        $this->assertTrue($reflection->getProperty('authUser')->isReadOnly());
        $this->assertTrue($reflection->getProperty('params')->isReadOnly());
    }

    /**
     * Тест: наследование от FormDTO
     */
    public function test_inherits_from_form_dto(): void
    {
        $user = new FakeUser();

        $dto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100
        );

        $this->assertInstanceOf(\Core\DTO\FormDTO::class, $dto);
    }

    /**
     * Тест: authUser и user ссылаются на один объект
     */
    public function test_auth_user_and_user_are_same(): void
    {
        $user = new FakeUser();

        $dto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100
        );

        $this->assertSame($dto->authUser, $dto->user);
    }

    /**
     * Тест: использование разных ресолверов из конфига
     */
    public function test_uses_resolver_from_config(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        // Используем другой ресолвер
        $differentResolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $differentResolverMock->shouldReceive('resolve')
            ->with($user, [])
            ->andReturn([
                'organization_id' => 99,
                'role_id' => 88
            ]);

        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $differentResolverMock);

        $dto = OnceDTO::make(100);

        $this->assertSame(99, $dto->organizationId);
        $this->assertSame(88, $dto->roleId);
    }

    /**
     * Тест: null значения в контексте
     */
    public function test_handles_null_context_values(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $resolverMock->shouldReceive('resolve')
            ->with($user, [])
            ->andReturn([]); // пустой массив

        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $resolverMock);

        $dto = OnceDTO::make(100);

        $this->assertNull($dto->organizationId);
        $this->assertNull($dto->roleId);
    }

    /**
     * Тест: цепочка вызовов appendParams
     */
    public function test_method_chaining_with_append_params(): void
    {
        $user = new FakeUser();

        // Создаем исходный DTO
        $dto = new OnceDTO(
            authUser: $user,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100,
            params: ['initial' => 'value']
        );

        // Цепочка добавления параметров
        $resultDto = $dto
            ->appendParams(['step1' => 'data1'])
            ->appendParams(['step2' => 'data2'])
            ->appendParams(['step3' => 'data3']);

        // Проверяем все добавленные параметры
        $this->assertEquals('value', $resultDto->params['initial']);
        $this->assertEquals('data1', $resultDto->params['step1']);
        $this->assertEquals('data2', $resultDto->params['step2']);
        $this->assertEquals('data3', $resultDto->params['step3']);

        // Проверяем, что каждый шаг создавал новый объект
        $this->assertNotSame($dto, $resultDto);
    }

    /**
     * Тест: конструктор с null authUser
     */
    public function test_constructor_with_null_auth_user(): void
    {
        $user = new FakeUser();

        $dto = new OnceDTO(
            authUser: null,
            user: $user,
            organizationId: 10,
            roleId: 5,
            id: 100
        );

        $this->assertNull($dto->authUser);
        $this->assertSame($user, $dto->user);
        $this->assertSame(10, $dto->organizationId);
        $this->assertSame(5, $dto->roleId);
        $this->assertSame(100, $dto->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
