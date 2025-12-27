<?php

namespace Tests\Unit\Helpers;

use Core\Helpers\Relations;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\Fakes\FakeUser;
use Tests\TestCase;

/**
 * @covers \Core\Helpers\Relations
 *
 * Тесты для helper'а Relations.
 *
 * Контракт:
 * - helper работает ТОЛЬКО с загруженными отношениями
 * - не лезет в БД
 * - не падает на некорректных данных
 * - безопасно трансформирует структуру отношений
 */
class RelationsTest extends TestCase
{
    /**
     * Если отношение НЕ загружено —
     * helper ничего не делает и не создаёт новых отношений.
     */
    public function test_it_does_nothing_if_relation_not_loaded(): void
    {
        $model = new FakeUser();

        Relations::moveFirstRelationItem($model, 'participants');

        $this->assertFalse(
            $model->relationLoaded('participants'),
            'Исходное отношение не должно быть загружено'
        );

        $this->assertFalse(
            $model->relationLoaded('participant'),
            'Новое одиночное отношение не должно появиться'
        );
    }

    /**
     * Из коллекции отношений берётся первый элемент,
     * сохраняется как одиночное отношение,
     * а исходное коллекционное отношение удаляется.
     */
    public function test_it_moves_first_item_from_relation_collection(): void
    {
        $model = new FakeUser();

        $first = new FakeUser(['id' => 1]);
        $second = new FakeUser(['id' => 2]);

        $model->setRelation('participants', collect([$first, $second]));

        Relations::moveFirstRelationItem($model, 'participants');

        $this->assertTrue(
            $model->relationLoaded('participant'),
            'Одиночное отношение должно быть создано'
        );

        $this->assertSame(
            $first,
            $model->participant,
            'В одиночное отношение должен попасть первый элемент коллекции'
        );

        $this->assertFalse(
            $model->relationLoaded('participants'),
            'Исходное коллекционное отношение должно быть удалено'
        );
    }

    /**
     * Если коллекция отношений загружена, но пуста —
     * одиночное отношение создаётся со значением null.
     */
    public function test_it_sets_null_if_relation_collection_is_empty(): void
    {
        $model = new FakeUser();

        $model->setRelation('participants', collect());

        Relations::moveFirstRelationItem($model, 'participants');

        $this->assertTrue(
            $model->relationLoaded('participant'),
            'Одиночное отношение должно быть создано'
        );

        $this->assertNull(
            $model->participant,
            'Значение одиночного отношения должно быть null'
        );
    }

    /**
     * Если отношение загружено, но имеет значение null —
     * helper должен безопасно обработать этот случай
     * и создать одиночное отношение со значением null.
     */
    public function test_it_handles_null_relation_value(): void
    {
        $model = new FakeUser();

        $model->setRelation('participants', null);

        Relations::moveFirstRelationItem($model, 'participants');

        $this->assertTrue(
            $model->relationLoaded('participant'),
            'Одиночное отношение должно быть создано'
        );

        $this->assertNull(
            $model->participant,
            'Значение одиночного отношения должно быть null'
        );
    }

    /**
     * Helper должен использовать кастомное имя одиночного отношения,
     * если оно передано явно.
     */
    public function test_it_uses_custom_to_relation_name(): void
    {
        $model = new FakeUser();

        $item = new FakeUser(['id' => 10]);

        $model->setRelation('items', collect([$item]));

        Relations::moveFirstRelationItem($model, 'items', 'mainItem');

        $this->assertTrue(
            $model->relationLoaded('mainItem'),
            'Кастомное одиночное отношение должно быть создано'
        );

        $this->assertSame(
            $item,
            $model->mainItem,
            'В кастомное одиночное отношение должен попасть первый элемент'
        );

        $this->assertFalse(
            $model->relationLoaded('items'),
            'Исходное отношение должно быть удалено'
        );
    }

    /**
     * Helper должен корректно работать с LengthAwarePaginator:
     * трансформировать каждый элемент коллекции пагинации,
     * не ломая сам объект пагинатора.
     */
    public function test_it_safely_handles_paginator(): void
    {
        $model = new FakeUser();
        $model->setRelation('participants', collect([
            new FakeUser(['id' => 5]),
        ]));

        $paginator = new LengthAwarePaginator(
            new Collection([$model]),
            total: 1,
            perPage: 15
        );

        Relations::moveFirstRelationItemInPaginator(
            $paginator,
            'participants'
        );

        $this->assertEquals(
            5,
            $paginator->items()[0]->participant->id,
            'В пагинаторе должен быть преобразованный элемент'
        );
    }

    /**
     * Если в пагинаторе встречаются элементы,
     * не являющиеся Eloquent Model —
     * helper должен их игнорировать и не падать.
     */
    public function test_it_ignores_non_model_items_in_paginator(): void
    {
        $paginator = new LengthAwarePaginator(
            new Collection(['string', 123]),
            total: 2,
            perPage: 15
        );

        Relations::moveFirstRelationItemInPaginator(
            $paginator,
            'participants'
        );

        $this->assertSame(
            'string',
            $paginator->items()[0],
            'Строковые элементы не должны изменяться'
        );

        $this->assertSame(
            123,
            $paginator->items()[1],
            'Числовые элементы не должны изменяться'
        );
    }
}
