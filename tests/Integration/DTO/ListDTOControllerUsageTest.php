<?php

namespace Tests\Integration\DTO;

use Core\DTO\ListDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class ListDTOControllerUsageTest extends TestCase
{
    /**
     * Тест: использование ListDTO в контроллере (как в примере с index методом)
     */
    public function test_usage_in_controller_index_method(): void
    {
        // Мокаем зависимости
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $converterMock = Mockery::mock('alias:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->with(
                Mockery::type(Request::class),
                ['showDeleted', 'rowsPerPage', 'sortBy', 'descending', 'search'],
                []
            )
            ->andReturn([
                'rowsPerPage' => 15,
                'sortBy' => 'created_at',
                'descending' => false
            ]);

        // Имитация запроса как в контроллере
        $request = Request::create('/api/attachments', 'GET', [
            'rowsPerPage' => '15',
            'sortBy' => 'created_at',
            'descending' => 'false'
        ]);

        // Имитация вызова из контроллера
        $dto = ListDTO::fromRequest($request);

        // Проверяем, что DTO создан корректно
        $this->assertInstanceOf(ListDTO::class, $dto);

        // Проверяем параметры, которые будут переданы в репозиторий
        $this->assertEquals(15, $dto->params['rowsPerPage']);
        $this->assertEquals('created_at', $dto->params['sortBy']);
        $this->assertFalse($dto->params['descending']);
        $this->assertSame($user, $dto->params['auth_user']);
        $this->assertEquals(1, $dto->params['auth_user_id']);

        // Далее в контроллере было бы:
        // $data = $this->attachmentRepository->getPaginatedList($dto);
        // Эту часть мы не тестируем, так как это уже тесты репозитория
    }

    /**
     * Тест: использование с дополнительными параметрами
     */
    public function test_usage_with_additional_params_in_controller(): void
    {
        $user = new FakeUser();
        $user->id = 1;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $converterMock = Mockery::mock('alias:Converter\DTO\ConverterDTO');
        $converterMock->shouldReceive('getQueryParams')
            ->once()
            ->with(
                Mockery::type(Request::class),
                ['status', 'type', 'showDeleted', 'rowsPerPage', 'sortBy', 'descending', 'search'],
                []
            )
            ->andReturn([
                'status' => 'active',
                'type' => 'image',
                'rowsPerPage' => 20
            ]);

        $request = Request::create('/api/documents', 'GET', [
            'status' => 'active',
            'type' => 'image',
            'rowsPerPage' => '20'
        ]);

        $dto = ListDTO::fromRequest($request, ['status', 'type']);

        $this->assertEquals('active', $dto->params['status']);
        $this->assertEquals('image', $dto->params['type']);
        $this->assertEquals(20, $dto->params['rowsPerPage']);
        $this->assertSame($user, $dto->params['auth_user']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
