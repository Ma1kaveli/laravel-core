<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Tests\Fakes\FakeSoftModelBase;
use Tests\TestCase;

/**
 * @covers \Core\Models\SoftModelBase
 *
 * Тесты для SoftModelBase.
 *
 * Контракт:
 * - при soft delete заполняется deleted_by и deleted_at
 * - при restore очищаются deleted_* и заполняется updated_by
 * - логика реализована через model events
 */
class SoftModelBaseTest extends TestCase
{
    /**
     * Подготавливаем авторизованного пользователя
     * для проверки model events
     */
    protected function setUpAuth(): void
    {
        $user = new User();
        $user->id = 100;

        Auth::login($user);
    }

    /**
     * При soft delete:
     * - модель должна быть soft-deleted
     * - deleted_by должен быть равен текущему пользователю
     * - deleted_at должен быть установлен
     */
    public function test_it_sets_deleted_by_and_deleted_at_on_soft_delete(): void
    {
        $model = FakeSoftModelBase::create();

        $model->delete();
        $model->refresh();

        $this->assertSoftDeleted(
            'fake_soft_models',
            ['id' => $model->id]
        );

        $this->assertEquals(
            100,
            $model->deleted_by,
            'deleted_by должен быть равен ID текущего пользователя'
        );

        $this->assertNotNull(
            $model->deleted_at,
            'deleted_at должен быть установлен'
        );

    }

    /**
     * При restore:
     * - deleted_by очищается
     * - deleted_at очищается
     * - updated_by равен текущему пользователю
     */
    public function test_it_resets_deleted_fields_and_sets_updated_by_on_restore(): void
    {
        $model = FakeSoftModelBase::create();
        $model->delete();

        $model->restore();
        $model->refresh();

        $this->assertNull(
            $model->deleted_by,
            'deleted_by должен быть очищен при restore'
        );

        $this->assertNull(
            $model->deleted_at,
            'deleted_at должен быть очищен при restore'
        );

        $this->assertEquals(
            100,
            $model->updated_by,
            'updated_by должен быть равен ID текущего пользователя'
        );
    }

    /**
     * Soft delete и restore должны работать корректно
     * при многократных последовательных вызовах
     */
    public function test_it_can_be_deleted_and_restored_multiple_times(): void
    {
        $model = FakeSoftModelBase::create();

        $model->delete();
        $this->assertNotNull($model->deleted_at);

        $model->restore();
        $this->assertNull($model->deleted_at);

        $model->delete();
        $this->assertNotNull($model->deleted_at);
    }
}
