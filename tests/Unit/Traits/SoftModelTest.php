<?php

namespace Tests\Unit\Traits;

use Tests\Fakes\FakeSoftModel;

use Tests\TestCase;

class SoftModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function test_destroyModel_success()
    {
        $model = FakeSoftModel::create(['name' => 'Test']);

        $response = FakeSoftModel::destroyModel(
            $model,
            'Already deleted',
            'Deleted successfully',
            'Delete error'
        );

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('Deleted successfully', $response['message']);
    }

    /** @test */
    public function test_destroyModel_already_deleted()
    {
        $model = FakeSoftModel::create(['name' => 'Test']);
        $model->deleted_at = now();
        $model->save();

        $response = FakeSoftModel::destroyModel(
            $model,
            'Already deleted',
            'Deleted successfully',
            'Delete error'
        );

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('Already deleted', $response['message']);
    }

    /** @test */
    public function test_restoreModel_success()
    {
        $model = FakeSoftModel::create(['name' => 'Test']);
        $model->deleted_at = now();
        $model->save();

        $response = FakeSoftModel::restoreModel(
            $model,
            'Not deleted',
            'Restored successfully',
            'Restore error'
        );

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('Restored successfully', $response['message']);
    }

    /** @test */
    public function test_restoreModel_not_deleted()
    {
        $model = FakeSoftModel::create(['name' => 'Test']);

        $response = FakeSoftModel::restoreModel(
            $model,
            'Not deleted',
            'Restored successfully',
            'Restore error'
        );

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('Not deleted', $response['message']);
    }
}
