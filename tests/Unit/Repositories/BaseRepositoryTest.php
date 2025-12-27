<?php

namespace Tests\Unit\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\Fakes\FakeRepository;
use Tests\Fakes\FakeRepositoryModel;

/**
 * @covers \Core\Repositories\BaseRepository
 *
 * Тесты контракта BaseRepository.
 *
 * Проверяем:
 * - базовую инициализацию
 * - auth-логику
 * - бизнес-методы
 * - работу с soft deletes
 * - кеширование
 */
class BaseRepositoryTest extends TestCase
{
    protected FakeRepository $repository;

    protected function setUpAuth(): void
    {
        $user = new User();
        $user->id = 10;
        $user->is_superadministrator = true;

        Auth::login($user);

        $this->repository = new FakeRepository($user);
    }

    /**
     * Репозиторий должен корректно возвращать модель.
     */
    public function test_get_model_returns_model_instance(): void
    {
        $model = $this->repository->getModel();

        $this->assertInstanceOf(
            FakeRepositoryModel::class,
            $model
        );
    }

    /**
     * Репозиторий должен определять авторизацию пользователя.
     */
    public function test_is_auth_and_is_root(): void
    {
        $this->assertTrue($this->repository->isAuth());
        $this->assertTrue($this->repository->isRoot());
        $this->assertEquals(10, $this->repository->getAuthUserId());
    }

    /**
     * findByIdOrFail должен возвращать модель,
     * если запись существует.
     */
    public function test_find_by_id_or_fail_returns_model(): void
    {
        $model = FakeRepositoryModel::create(['name' => 'test']);

        $found = $this->repository->findByIdOrFail($model->id);

        $this->assertEquals($model->id, $found->id);
    }

    /**
     * findByIdOrFail должен выбрасывать исключение,
     * если запись не найдена.
     */
    public function test_find_by_id_or_fail_throws_exception(): void
    {
        $this->expectException(\Exception::class);

        $this->repository->findByIdOrFail(999);
    }

    /**
     * isUnique должен вернуть false,
     * если запись с такими параметрами уже существует.
     */
    public function test_is_unique_returns_false_when_duplicate_exists(): void
    {
        FakeRepositoryModel::create(['name' => 'John']);

        $dto = (object) ['name' => 'John'];

        $result = $this->repository->isUnique(
            $dto,
            ['name' => 'name'],
            false
        );

        $this->assertFalse($result);
    }

    /**
     * uncreatedIds должен вернуть только отсутствующие идентификаторы.
     */
    public function test_uncreated_ids_returns_missing_ids(): void
    {
        FakeRepositoryModel::create(['id' => 1]);
        FakeRepositoryModel::create(['id' => 2]);

        $ids = $this->repository->uncreatedIds(
            [1, 2, 3, 4],
            'id'
        );

        $this->assertEquals([3, 4], array_values($ids));
    }

    /**
     * onlyTrashed должен возвращать только soft-deleted записи.
     */
    public function test_only_trashed_returns_soft_deleted_models(): void
    {
        $model = FakeRepositoryModel::create();
        $model->delete();

        $trashed = $this->repository->onlyTrashed();

        $this->assertCount(1, $trashed);
        $this->assertTrue($trashed->first()->trashed());
    }

    /**
     * cachedGet должен возвращать данные из кеша
     * при повторном вызове.
     */
    public function test_cached_get_uses_cache(): void
    {
        Cache::flush();

        FakeRepositoryModel::create(['name' => 'cached']);

        $first = $this->repository->cachedGet('test_cache');
        FakeRepositoryModel::truncate();
        $second = $this->repository->cachedGet('test_cache');

        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
    }
}
