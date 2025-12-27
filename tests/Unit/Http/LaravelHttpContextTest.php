<?php

namespace Tests\Unit\Http;

use Core\Http\LaravelHttpContext;
use Tests\TestCase;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * @covers \Core\Http\LaravelHttpContext
 *
 * Тесты для LaravelHttpContext.
 *
 * Класс является адаптером над Laravel Request
 * и не содержит бизнес-логики — только проксирование.
 *
 * Эти тесты фиксируют контракт:
 * - всегда используется текущий HTTP request
 * - данные и параметры маршрута возвращаются корректно
 */
class LaravelHttpContextTest extends TestCase
{
    /**
     * Метод request() должен вернуть текущий экземпляр
     * Illuminate\Http\Request, связанный с контейнером Laravel.
     */
    public function test_request_returns_current_laravel_request(): void
    {
        $context = new LaravelHttpContext();

        $request = $context->request();

        $this->assertInstanceOf(
            Request::class,
            $request,
            'Метод request() должен возвращать экземпляр Illuminate\Http\Request'
        );
    }

    /**
     * Метод all() должен вернуть все входные данные запроса,
     * переданные через query или body.
     */
    public function test_all_returns_all_request_input(): void
    {
        $this->app['request']->merge([
            'name' => 'John',
            'age'  => 30,
        ]);

        $context = new LaravelHttpContext();

        $this->assertSame(
            [
                'name' => 'John',
                'age'  => 30,
            ],
            $context->all(),
            'Метод all() должен вернуть все данные запроса'
        );
    }

    /**
     * Метод route() должен корректно возвращать параметр маршрута,
     * если он существует.
     */
    public function test_route_returns_route_parameter(): void
    {
        Route::get('/users/{id}', function () {
            return 'ok';
        });

        $this->get('/users/42');

        $context = new LaravelHttpContext();

        $this->assertSame(
            '42',
            $context->route('id'),
            'Метод route() должен вернуть параметр маршрута'
        );
    }

    /**
     * Если параметр маршрута отсутствует,
     * метод route() должен вернуть значение по умолчанию.
     */
    public function test_route_returns_default_value_if_parameter_missing(): void
    {
        Route::get('/posts', function () {
            return 'ok';
        });

        $this->get('/posts');

        $context = new LaravelHttpContext();

        $this->assertSame(
            'default',
            $context->route('id', 'default'),
            'Метод route() должен вернуть default-значение, если параметр отсутствует'
        );
    }
}
