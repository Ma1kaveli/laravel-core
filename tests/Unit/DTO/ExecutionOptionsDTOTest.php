<?php

namespace Tests\Unit\DTO;

use Core\DTO\ExecutionOptionsDTO;
use Core\DTO\Resolvers\BaseAwareOrganizationContextResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class ExecutionOptionsDTOTest extends TestCase
{
    private $user;
    private $resolverMock;

    public function setUp(): void
    {
        parent::setUp();

        // Создаем пользователя для всех тестов
        $this->user = new FakeUser();
        $this->user->id = 1;
        $this->user->role = (object) ['organization_id' => 10, 'id' => 5];

        // Создаем мок ресолвера
        $this->resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $this->resolverMock->shouldReceive('resolve')
            ->andReturn([
                'organization_id' => 10,
                'role_id' => 5
            ]);

        // Настраиваем конфиг
        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $this->resolverMock);
    }

    /**
     * Тест: создание DTO через make() с настройками по умолчанию
     */
    public function test_make_creates_instance_with_defaults(): void
    {
        // Мокаем Auth::user() для одного вызова
        Auth::shouldReceive('user')->once()->andReturn($this->user);

        // Создаем DTO
        $dto = ExecutionOptionsDTO::make();

        // Проверяем значения по умолчанию
        $this->assertFalse($dto->getFunc);
        $this->assertTrue($dto->withTransaction);
        $this->assertTrue($dto->withValidation);
        $this->assertTrue($dto->writeErrorLog);

        // Проверяем родительские свойства
        $this->assertSame($this->user, $dto->user);
        $this->assertSame(10, $dto->organizationId);
        $this->assertSame(5, $dto->roleId);
        $this->assertNull($dto->id);
    }

    /**
     * Тест: appendGetFunc возвращает новый экземпляр с getFunc = true
     */
    public function test_append_get_func_sets_get_func_to_true(): void
    {
        // Мокаем Auth::user() для двух вызовов (исходный DTO + новый)
        Auth::shouldReceive('user')->twice()->andReturn($this->user);

        $originalDto = ExecutionOptionsDTO::make();
        $newDto = $originalDto->appendGetFunc();

        // Проверяем, что это новый объект
        $this->assertNotSame($originalDto, $newDto);

        // Проверяем, что getFunc = true, остальные свойства сохранились
        $this->assertTrue($newDto->getFunc);
        $this->assertTrue($newDto->withTransaction);
        $this->assertTrue($newDto->withValidation);
        $this->assertTrue($newDto->writeErrorLog);

        // Проверяем родительские свойства
        $this->assertSame($this->user, $newDto->user);
        $this->assertSame(10, $newDto->organizationId);
        $this->assertSame(5, $newDto->roleId);
    }

    /**
     * Тест: withoutTransaction возвращает новый экземпляр с withTransaction = false
     */
    public function test_without_transaction_sets_with_transaction_to_false(): void
    {
        Auth::shouldReceive('user')->twice()->andReturn($this->user);

        $originalDto = ExecutionOptionsDTO::make();
        $newDto = $originalDto->withoutTransaction();

        $this->assertNotSame($originalDto, $newDto);
        $this->assertFalse($newDto->withTransaction);
        $this->assertFalse($newDto->getFunc);
        $this->assertTrue($newDto->withValidation);
        $this->assertTrue($newDto->writeErrorLog);
    }

    /**
     * Тест: withoutValidation возвращает новый экземпляр с withValidation = false
     */
    public function test_without_validation_sets_with_validation_to_false(): void
    {
        Auth::shouldReceive('user')->twice()->andReturn($this->user);

        $originalDto = ExecutionOptionsDTO::make();
        $newDto = $originalDto->withoutValidation();

        $this->assertNotSame($originalDto, $newDto);
        $this->assertFalse($newDto->withValidation);
        $this->assertFalse($newDto->getFunc);
        $this->assertTrue($newDto->withTransaction);
        $this->assertTrue($newDto->writeErrorLog);
    }

    /**
     * Тест: withoutErrorLog возвращает новый экземпляр с writeErrorLog = false
     */
    public function test_without_error_log_sets_write_error_log_to_false(): void
    {
        Auth::shouldReceive('user')->twice()->andReturn($this->user);

        $originalDto = ExecutionOptionsDTO::make();
        $newDto = $originalDto->withoutErrorLog();

        $this->assertNotSame($originalDto, $newDto);
        $this->assertFalse($newDto->writeErrorLog);
        $this->assertFalse($newDto->getFunc);
        $this->assertTrue($newDto->withTransaction);
        $this->assertTrue($newDto->withValidation);
    }

    /**
     * Тест: цепочка методов изменяет несколько свойств
     */
    public function test_method_chaining_changes_multiple_properties(): void
    {
        // 5 вызовов: исходный + 4 метода
        Auth::shouldReceive('user')->times(5)->andReturn($this->user);

        $dto = ExecutionOptionsDTO::make()
            ->appendGetFunc()
            ->withoutTransaction()
            ->withoutValidation()
            ->withoutErrorLog();

        $this->assertTrue($dto->getFunc);
        $this->assertFalse($dto->withTransaction);
        $this->assertFalse($dto->withValidation);
        $this->assertFalse($dto->writeErrorLog);
    }

    /**
     * Тест: исправленный баг в методе appendGetFunc
     */
    public function test_append_get_func_fixed_bug(): void
    {
        // 3 вызова: исходный + withoutTransaction + appendGetFunc
        Auth::shouldReceive('user')->times(3)->andReturn($this->user);

        $originalDto = ExecutionOptionsDTO::make();
        $dtoWithoutTransaction = $originalDto->withoutTransaction();
        $dtoWithGetFunc = $dtoWithoutTransaction->appendGetFunc();

        // Теперь writeErrorLog должно быть true (по умолчанию), а не false
        $this->assertTrue($dtoWithGetFunc->writeErrorLog);
        $this->assertTrue($dtoWithGetFunc->getFunc);
        $this->assertFalse($dtoWithGetFunc->withTransaction);
        $this->assertTrue($dtoWithGetFunc->withValidation);
    }

    /**
     * Тест: DTO использует конфигурацию для выбора ресолвера
     */
    public function test_uses_resolver_from_config(): void
    {
        // Тестируем другой ресолвер
        $differentResolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $differentResolverMock->shouldReceive('resolve')
            ->andReturn(['organization_id' => 99, 'role_id' => 88]);

        $this->app->instance(BaseAwareOrganizationContextResolver::class, $differentResolverMock);

        Auth::shouldReceive('user')->once()->andReturn($this->user);

        $dto = ExecutionOptionsDTO::make();

        $this->assertSame(99, $dto->organizationId);
        $this->assertSame(88, $dto->roleId);
    }

    /**
     * Тест: свойства только для чтения
     */
    public function test_properties_are_readonly(): void
    {
        Auth::shouldReceive('user')->once()->andReturn($this->user);

        $dto = ExecutionOptionsDTO::make();

        $reflection = new \ReflectionObject($dto);

        $this->assertTrue($reflection->getProperty('getFunc')->isReadOnly());
        $this->assertTrue($reflection->getProperty('withTransaction')->isReadOnly());
        $this->assertTrue($reflection->getProperty('withValidation')->isReadOnly());
        $this->assertTrue($reflection->getProperty('writeErrorLog')->isReadOnly());
    }

    /**
     * Тест: все методы сохраняют другие настройки
     */
    public function test_methods_preserve_other_settings(): void
    {
        Auth::shouldReceive('user')->times(3)->andReturn($this->user);

        // Создаем DTO с измененными настройками
        $dto = ExecutionOptionsDTO::make()
            ->appendGetFunc()
            ->withoutErrorLog();

        // Проверяем, что оба изменения сохранились
        $this->assertTrue($dto->getFunc);
        $this->assertFalse($dto->writeErrorLog);
        $this->assertTrue($dto->withTransaction); // осталось по умолчанию
        $this->assertTrue($dto->withValidation); // осталось по умолчанию
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
