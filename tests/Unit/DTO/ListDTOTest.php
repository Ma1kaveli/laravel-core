<?php

namespace Tests\Unit\DTO;

use Core\DTO\ListDTO;
use Converter\DTO\ConverterDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class ListDTOTest extends TestCase
{
    /**
     * Тест: создание ListDTO из Request с параметрами
     */
    public function test_from_request_with_params(): void
    {
        // Мокаем пользователя
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        // Правильно мокаем статический метод ConverterDTO::getQueryParams
        $converterMock = Mockery::mock('overload:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->withArgs(function ($request, $params, $mapParams) {
                // Проверяем что Request передан
                return $request instanceof Request &&
                    // Проверяем что нужные параметры в массиве (порядок не важен)
                    in_array('chatTypeId', $params) &&
                    in_array('houseId', $params) &&
                    in_array('search', $params) &&
                    in_array('showDeleted', $params) &&
                    in_array('rowsPerPage', $params) &&
                    in_array('sortBy', $params) &&
                    in_array('descending', $params) &&
                    // Проверяем mapParams
                    empty($mapParams);
            })
            ->andReturn([
                'chatTypeId' => 5,
                'houseId' => 10,
                'showDeleted' => false,
                'rowsPerPage' => 20,
                'sortBy' => 'created_at',
                'descending' => true,
                'search' => 'test'
            ]);

        // Создаем запрос
        $request = Request::create('/test', 'GET', [
            'chatTypeId' => '5',
            'houseId' => '10',
            'showDeleted' => 'false',
            'rowsPerPage' => '20',
            'sortBy' => 'created_at',
            'descending' => 'true',
            'search' => 'test'
        ]);

        // Создаем DTO
        $dto = ListDTO::fromRequest($request, ['chatTypeId', 'houseId']);

        // Проверяем результат
        $this->assertInstanceOf(ListDTO::class, $dto);

        // Проверяем параметры из конвертера
        $this->assertEquals(5, $dto->params['chatTypeId']);
        $this->assertEquals(10, $dto->params['houseId']);
        $this->assertEquals(20, $dto->params['rowsPerPage']);
        $this->assertEquals('created_at', $dto->params['sortBy']);
        $this->assertTrue($dto->params['descending']);
        $this->assertEquals('test', $dto->params['search']);
        $this->assertFalse($dto->params['showDeleted']);

        // Проверяем добавленные параметры пользователя
        $this->assertSame($user, $dto->params['auth_user']);
        $this->assertEquals(1, $dto->params['auth_user_id']);
    }

    /**
     * Тест: создание ListDTO из Request с пустыми параметрами
     */
    public function test_from_request_with_empty_params(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        // Используем any() для упрощения
        $converterMock = Mockery::mock('overload:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $request = Request::create('/test', 'GET', []);

        $dto = ListDTO::fromRequest($request, []);

        $this->assertInstanceOf(ListDTO::class, $dto);

        // Должны быть только параметры пользователя
        $this->assertSame($user, $dto->params['auth_user']);
        $this->assertEquals(1, $dto->params['auth_user_id']);
    }

    /**
     * Тест: создание ListDTO из Request с mapParams
     */
    public function test_from_request_with_map_params(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $converterMock = Mockery::mock('overload:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->withAnyArgs() // Упрощаем, проверяем логику DTO
            ->andReturn([
                'category_id' => 5,
                'rowsPerPage' => 10
            ]);

        $request = Request::create('/test', 'GET', [
            'category_id' => '5',
            'rowsPerPage' => '10'
        ]);

        $dto = ListDTO::fromRequest($request, ['category_id'], ['category_id' => 'categoryId']);

        $this->assertEquals(5, $dto->params['category_id']);
        $this->assertEquals(10, $dto->params['rowsPerPage']);
        $this->assertSame($user, $dto->params['auth_user']);
    }

    /**
     * Тест: создание ListDTO по умолчанию
     */
    public function test_from_default(): void
    {
        $dto = ListDTO::fromDefault([
            'page' => 1,
            'limit' => 20,
            'filter' => 'active'
        ]);

        $this->assertInstanceOf(ListDTO::class, $dto);
        $this->assertEquals([
            'page' => 1,
            'limit' => 20,
            'filter' => 'active'
        ], $dto->params);

        // Без параметров
        $emptyDto = ListDTO::fromDefault();
        $this->assertEmpty($emptyDto->params);
    }

    /**
     * Тест: добавление параметров
     */
    public function test_append_params(): void
    {
        // Создаем начальный DTO
        $dto = new ListDTO([
            'page' => 1,
            'limit' => 20
        ]);

        // Добавляем параметры
        $newDto = $dto->appendParams([
            'filter' => 'active',
            'sort' => 'name'
        ]);

        // Проверяем, что это новый объект
        $this->assertNotSame($dto, $newDto);

        // Проверяем параметры
        $this->assertEquals(1, $newDto->params['page']);
        $this->assertEquals(20, $newDto->params['limit']);
        $this->assertEquals('active', $newDto->params['filter']);
        $this->assertEquals('name', $newDto->params['sort']);

        // Исходный DTO не должен измениться
        $this->assertArrayNotHasKey('filter', $dto->params);
        $this->assertArrayNotHasKey('sort', $dto->params);
    }

    /**
     * Тест: добавление параметров перезаписывает существующие
     */
    public function test_append_params_overwrites_existing(): void
    {
        $dto = new ListDTO([
            'page' => 1,
            'limit' => 20,
            'filter' => 'old'
        ]);

        $newDto = $dto->appendParams([
            'filter' => 'new',
            'sort' => 'name'
        ]);

        $this->assertEquals('new', $newDto->params['filter']); // Перезаписано
        $this->assertEquals('name', $newDto->params['sort']); // Добавлено
        $this->assertEquals(1, $newDto->params['page']); // Сохранено
        $this->assertEquals(20, $newDto->params['limit']); // Сохранено
    }

    /**
     * Тест: свойства только для чтения
     */
    public function test_properties_are_readonly(): void
    {
        $dto = new ListDTO(['test' => 'value']);

        $reflection = new \ReflectionObject($dto);
        $property = $reflection->getProperty('params');

        $this->assertTrue($property->isReadOnly());
    }

    /**
     * Тест: реализация интерфейса IListDTO
     */
    public function test_implements_ilist_dto_interface(): void
    {
        $dto = new ListDTO([]);

        $this->assertInstanceOf(\Core\Interfaces\IListDTO::class, $dto);
    }

    /**
     * Тест: конструктор с пустым массивом
     */
    public function test_constructor_with_empty_array(): void
    {
        $dto = new ListDTO([]);

        $this->assertIsArray($dto->params);
        $this->assertEmpty($dto->params);
    }

    /**
     * Тест: параметры передаются в правильном порядке
     */
    public function test_parameters_order_in_from_request(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        // Создаем мок и ловим аргументы
        $capturedArgs = [];

        $converterMock = Mockery::mock('overload:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->withArgs(function ($request, $params, $mapParams) use (&$capturedArgs) {
                $capturedArgs = [$params, $mapParams];
                return true;
            })
            ->andReturn([]);

        $request = Request::create('/test', 'GET', []);

        ListDTO::fromRequest($request, ['param1', 'param2'], ['old' => 'new']);

        // Проверяем порядок параметров
        $this->assertEquals(
            ['param1', 'param2', 'search', 'showDeleted', 'rowsPerPage', 'sortBy', 'archived', 'descending'],
            $capturedArgs[0]
        );

        // Проверяем mapParams
        $this->assertEquals(['old' => 'new'], $capturedArgs[1]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
