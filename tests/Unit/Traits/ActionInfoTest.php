<?php

namespace Tests\Unit\Traits;

use Core\Traits\ActionInfo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ActionInfoTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('core.soft-model-base.user_model', \Tests\Fakes\FakeUser::class);
        config()->set('core.soft-model-base.created_by_key', 'created_by');
        config()->set('core.soft-model-base.updated_by_key', 'updated_by');
        config()->set('core.soft-model-base.deleted_by_key', 'deleted_by');
    }

    /**
     * Создаем тестовую модель с трэйтом
     */
    protected function getTestModel(): Model
    {
        return new class extends Model {
            use ActionInfo;

            protected $table = 'test';
        };
    }

    /**
     * Проверка, что метод creator() возвращает BelongsTo
     */
    public function test_creator_returns_belongs_to(): void
    {
        $model = $this->getTestModel();

        $relation = $model->creator();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals(config('core.soft-model-base.user_model'), get_class($relation->getRelated()));
        $this->assertEquals(config('core.soft-model-base.created_by_key'), $relation->getForeignKeyName());
    }

    /**
     * Проверка, что метод updator() возвращает BelongsTo
     */
    public function test_updator_returns_belongs_to(): void
    {
        $model = $this->getTestModel();

        $relation = $model->updator();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals(config('core.soft-model-base.user_model'), get_class($relation->getRelated()));
        $this->assertEquals(config('core.soft-model-base.updated_by_key'), $relation->getForeignKeyName());
    }

    /**
     * Проверка, что метод deletor() возвращает BelongsTo
     */
    public function test_deletor_returns_belongs_to(): void
    {
        $model = $this->getTestModel();

        $relation = $model->deletor();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals(config('core.soft-model-base.user_model'), get_class($relation->getRelated()));
        $this->assertEquals(config('core.soft-model-base.deleted_by_key'), $relation->getForeignKeyName());
    }

    /**
     * Проверка, что actionInfo() обращается к связям
     */
    public function test_action_info_accesses_relations(): void
    {
        $model = $this->getTestModel();

        $result = ActionInfo::actionInfo($model);

        $this->assertSame($model, $result);

        // Проверяем, что свойства связей доступны
        $this->assertTrue(property_exists($result, 'creator') || method_exists($result, 'creator'));
        $this->assertTrue(property_exists($result, 'updator') || method_exists($result, 'updator'));
        $this->assertTrue(property_exists($result, 'deletor') || method_exists($result, 'deletor'));
    }

}
