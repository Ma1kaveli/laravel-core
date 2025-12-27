<?php

namespace Tests\Unit\Services;

use Core\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;
use Carbon\Carbon;

class BaseServiceTest extends TestCase
{
    protected $modelMock;
    protected $service;

    public function setUp(): void
    {
        parent::setUp();

        // Мокаем модель
        $this->modelMock = Mockery::mock(Model::class)->makePartial();
        $this->modelMock->id = 1;
        $this->modelMock->password = null;
        $this->modelMock->is_verified = false;
        $this->modelMock->is_published = false;
        $this->modelMock->is_approved = false;
        $this->modelMock->archived_at = null;
        $this->modelMock->is_owner = null;

        // Создаем сервис с моком модели
        $this->service = new class(get_class($this->modelMock)) extends BaseService {};
    }

    /** @covers BaseService::setPassword */
    public function test_set_password_hashes_value(): void
    {
        $password = 'secret123';
        $this->modelMock->shouldReceive('update')->once()->with(Mockery::on(function($arg) use ($password) {
            return isset($arg['password']) && Hash::check($password, $arg['password']);
        }))->andReturn($this->modelMock);

        $result = $this->service->setPassword($this->modelMock, $password);
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::verified */
    public function test_verified_sets_flag(): void
    {
        $this->modelMock->shouldReceive('update')->once()->with(['is_verified' => true])->andReturn($this->modelMock);
        $result = $this->service->verified($this->modelMock);
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::archive */
    public function test_archive_sets_timestamp(): void
    {
        Carbon::setTestNow('2025-12-27 12:00:00');
        $this->modelMock->shouldReceive('update')->once()->with(['archived_at' => Carbon::now()])->andReturn($this->modelMock);
        $result = $this->service->archive($this->modelMock);
        $this->assertSame($this->modelMock, $result);
        Carbon::setTestNow(); // Сброс
    }

    /** @covers BaseService::unarchive */
    public function test_unarchive_nulls_timestamp(): void
    {
        $this->modelMock->shouldReceive('update')->once()->with(['archived_at' => null])->andReturn($this->modelMock);
        $result = $this->service->unarchive($this->modelMock);
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::changeIsPublished */
    public function test_change_is_published_toggles_flag(): void
    {
        $this->modelMock->is_published = false;
        $this->modelMock->shouldReceive('update')->once()->with(['is_published' => true])->andReturn($this->modelMock);
        $result = $this->service->changeIsPublished($this->modelMock);
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::toggleBoolean */
    public function test_toggle_boolean(): void
    {
        $this->modelMock->some_flag = false;
        $this->modelMock->shouldReceive('update')->once()->with(['some_flag' => true])->andReturn($this->modelMock);
        $result = $this->service->toggleBoolean($this->modelMock, 'some_flag');
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::setRandomValue */
    public function test_set_random_value(): void
    {
        $this->modelMock->shouldReceive('update')->once()->with(Mockery::on(function($arg) {
            return isset($arg['token']) && is_string($arg['token']) && strlen($arg['token']) === 32;
        }))->andReturn($this->modelMock);

        $result = $this->service->setRandomValue($this->modelMock, 'token', 32);
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::encryptField */
    public function test_encrypt_field(): void
    {
        $this->modelMock->secret = 'value';
        $this->modelMock->shouldReceive('update')->once()->with(Mockery::on(function($arg) {
            return isset($arg['secret']) && is_string($arg['secret']);
        }))->andReturn($this->modelMock);

        $result = $this->service->encryptField($this->modelMock, 'secret');
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::descryptField */
    public function test_descrypt_field(): void
    {
        $this->modelMock->secret = Crypt::encryptString('value');
        $this->modelMock->shouldReceive('update')->once()->with(['secret' => 'value'])->andReturn($this->modelMock);

        $result = $this->service->descryptField($this->modelMock, 'secret');
        $this->assertSame($this->modelMock, $result);
    }

    /** @covers BaseService::setIfChanged */
    public function test_set_if_changed_only_updates_when_needed(): void
    {
        $this->modelMock->field = 'same';
        // Не должен вызывать update, т.к. значение совпадает
        $result = $this->service->setIfChanged($this->modelMock, 'field', 'same');
        $this->assertSame($this->modelMock, $result);

        // Должен вызвать update если отличается
        $this->modelMock->shouldReceive('update')->once()->with(['field' => 'new'])->andReturn($this->modelMock);
        $result = $this->service->setIfChanged($this->modelMock, 'field', 'new');
        $this->assertSame($this->modelMock, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
