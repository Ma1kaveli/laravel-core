<?php

namespace Tests\Unit\Resources;

use Core\Resources\BaseResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\TestCase;

/**
 * @covers \Core\Resources\BaseResource
 *
 * Тесты BaseResource:
 * - проверка установки additionalFields
 * - поведение при коллекциях
 * - выполнение Closures
 * - корректная работа с массивами
 */
class BaseResourceTest extends TestCase
{
    /**
     * Проверяем, что additionalFields устанавливаются через конструктор
     */
    public function test_it_sets_additional_fields_from_constructor(): void
    {
        $resource = new BaseResource(['id' => 1], ['foo']);

        $reflection = new \ReflectionClass($resource);
        $prop = $reflection->getProperty('additionalFields');
        $prop->setAccessible(true);

        $this->assertSame(['foo'], $prop->getValue($resource));
    }

    /**
     * toArray() возвращает данные additionalFields
     */
    public function test_to_array_returns_additional_fields(): void
    {
        $resource = new class(['id' => 1], ['foo']) extends BaseResource {
            protected function getAdditionalData()
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'ignored',
                ];
            }
        };

        $array = $resource->toArray(null);

        $this->assertSame(['foo' => 'bar'], $array);
    }

    /**
     * toArray() выполняет Closure, если поле является Closure
     */
    public function test_to_array_executes_closure_fields(): void
    {
        $resource = new class(['id' => 1], ['foo']) extends BaseResource {
            protected function getAdditionalData()
            {
                return [
                    'foo' => fn() => 'executed',
                ];
            }
        };

        $array = $resource->toArray(null);

        $this->assertSame(['foo' => 'executed'], $array);
    }

    /**
     * toArray() корректно мержит массивы из additionalFields
     */
    public function test_to_array_merges_array_fields(): void
    {
        $resource = new class(['id' => 1], ['foo']) extends BaseResource {
            protected function getAdditionalData()
            {
                return [
                    'foo' => ['a' => 1, 'b' => 2],
                ];
            }
        };

        $array = $resource->toArray(null);

        $this->assertSame(['a' => 1, 'b' => 2], $array);
    }

    /**
     * Поля из additionalFields, которых нет в getAdditionalData(), игнорируются
     */
    public function test_to_array_ignores_missing_fields(): void
    {
        $resource = new BaseResource(['id' => 1], ['missing']);

        $array = $resource->toArray(null);

        $this->assertSame([], $array);
    }
}
