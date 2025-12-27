<?php

namespace Core\Repositories;

use Core\DTO\ListDTO;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

abstract class BaseRepository
{
    /**
     * Model class name
     */
    protected string $modelClass;

    public ?Authenticatable $user;

    public function __construct(string $modelClass, ?Authenticatable $user = null)
    {
        $this->modelClass = $modelClass;
        $this->user = $user ?? Auth::user();
    }

    /**
     * @return Builder
     */
    protected function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return new $this->modelClass();
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection
    {
        return $this->query()->all();
    }

    /**
     * @return array|Collection
     */
    public function getAll(): array|Collection
    {
        return $this->query()->get();
    }

    /**
     * @param int $id
     * @param bool $withTrashed
     * @param string $notFoundMessage = 'Не найдено!'
     *
     * @return array|Collection|Model|array<Model>
     */
    public function findByIdOrFail(
        int $id,
        bool $withTrashed = false,
        string $notFoundMessage = 'Не найдено!',
    ): array|Collection|Model {
        try {
            $query = $this->query()->when(
                $withTrashed, fn ($q) => $q->withTrashed()
            )->findOrFail($id);
        } catch(\Exception $e) {
            throw new \Exception($notFoundMessage, 404);
        }

        return $query;
    }

    /**
     * isUnique
     *
     * @param mixed $dto
     * @param array $mapParams - example [
     *      'name' => [
     *           'column' => \DB::raw('LOWER(name)'),
     *           'modifier' => fn($v) => trim(strtolower($v)),
     *           'is_or_where' => false
     *       ],
     *       'organizationId' => 'organization_id',
     *   ]
     * @param bool $exceptIfExist = true
     * @param string $exceptMessage = 'Нельзя дублировать записи'
     * @param string $excludeKey = 'id'
     * @param string $excludeColumn = 'id'
     *
     * @return bool|\Exception
     */
    public function isUnique(
        mixed $dto,
        array $mapParams,
        bool $exceptIfExist = true,
        string $exceptMessage = 'Нельзя дублировать записи',
        string $excludeKey = 'id',
        string $excludeColumn = 'id'
    ): bool|\Exception {
        $query = $this->query();

        if (isset($dto->{$excludeKey}) && $dto->{$excludeKey} !== null && $dto->{$excludeKey} !== '') {
            $query = $query->where($excludeColumn, '!=', $dto->{$excludeKey});
        }

        $query = $query->where(function ($q) use ($mapParams, $dto) {
            foreach ($mapParams as $dtoProperty => $mapping) {
                if (!isset($dto->{$dtoProperty})) {
                    continue;
                }

                $value = $dto->{$dtoProperty};

                $column = is_array($mapping) ? ($mapping['column'] ?? null) : $mapping;
                $modifier = is_array($mapping) ? ($mapping['modifier'] ?? null) : null;
                $isOrWhere = is_array($mapping) ? ($mapping['is_or_where'] ?? false) : false;

                if ($modifier && is_callable($modifier)) {
                    $value = $modifier($value);
                }

                $q->where($column, $value, null, $isOrWhere ? 'or' : 'and');
            }
        });

        $isUnique = !$query->exists();

        if ($exceptIfExist && !$isUnique) {
            throw new \Exception($exceptMessage, 404);
        }

        return $isUnique;
    }

    /**
     * @param string $column
     * @param $value
     *
     * @return Model|object|null
     */
    public function findBy(string $column, $value)
    {
        return $this->query()->where($column, $value)->first();
    }

    /**
     * @param string $column
     * @param $value
     *
     * @return array|Collection
     */
    public function getBy(string $column, $value): array|Collection
    {
        return $this->query()->where($column, $value)->get();
    }

    /**
     *
     * @param array $data
     *
     * @return Builder|Model|object|null
     */
    public function findByColumns(array $data)
    {
        $query = $this->query();

        foreach ($data as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * findById
     *
     * @param int $id
     *
     * @return array|Collection|Model|null
     */
    public function findById(mixed $id): array|Collection|Model|null
    {
        return $this->query()->find($id);
    }

    /**
     * @return bool
     */
    public function isAuth(): bool {
        if (!empty($this->user)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        $isSuperadminField = config('core.repository.is_root_field', 'is_superadministrator');
        return $this->isAuth() && !!$this->user->{$isSuperadminField};
    }

    /**
     * @return int|null
     */
    public function getAuthUserId(): int|null {
        $userIdField = config('core.repository.user_id_field', 'id');
        return $this->isAuth() ? $this->user->{$userIdField} : null;
    }

    /**
     * @param bool $withTrashed = false
     *
     * @return Builder
     */
    public function appendWithTrashedToQuery(bool $withTrashed = false): Builder {
        $query = $this->query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * @param bool $withTrashed = false
     *
     * @return int
     */
    public function count(bool $withTrashed = false): int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->count();
    }

    /**
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return int|null
     */
    public function countToday(string $column = 'created_at', bool $withTrashed = false): int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereDate($column, Carbon::today())->count();
    }

    /**
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return int|null
     */
    public function countThisWeek(string $column = 'created_at', bool $withTrashed = false): int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereBetween(
            $column,
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
        )->count();
    }

    /**
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return int|null
     */
    public function countThisMonth(string $column = 'created_at', bool $withTrashed = false): int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereMonth(
            $column, Carbon::now()->month
        )->whereYear($column, Carbon::now()->year)->count();
    }

    /**
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return int|null
     */
    public function countThisYear(string $column = 'created_at', bool $withTrashed = false): int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereYear($column, Carbon::now()->year)->count();
    }

    /**
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function sum(string $column, bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->sum($column);
    }

    /**
     * @param string $column
     * @param string $dateColumn = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function sumToday(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereDate($dateColumn, Carbon::today())->sum($column);
    }

    /**
     * @param string $column
     * @param string $dateColumn = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function sumThisWeek(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereBetween(
            $dateColumn,
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
        )->sum($column);
    }

    /**
     * @param string $column
     * @param string $dateColumn = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function sumThisMonth(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereMonth(
            $dateColumn, Carbon::now()->month
        )->whereYear($dateColumn, Carbon::now()->year)->sum($column);
    }

    /**
     * @param string $column
     * @param string $dateColumn = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function sumThisYear(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereYear($dateColumn, Carbon::now()->year)->sum($column);
    }

    /**
     * @param string $column = 'created_at'
     * @param int $count = 5
     * @param bool $withTrashed = false
     *
     * @return mixed
     */
    public function getLatest(string $column = 'id', int $count = 5, bool $withTrashed = false): mixed {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->orderBy($column, 'desc')->take($count)->get();
    }

    /**
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return float|int|null
     */
    public function avg(string $column, bool $withTrashed = false): float|int|null {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->avg($column);
    }

    /**
     * getPaginatedList
     *
     * @param ListDTO|mixed $dto
     *
     * @return LengthAwarePaginator
     */
    public function getPaginatedList(mixed $dto): LengthAwarePaginator
    {
        return $this->query()->filter($dto->params)->list();
    }

    /**
     * Get max value of a column.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return mixed
     */
    public function max(string $column, bool $withTrashed = false): mixed
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->max($column);
    }

    /**
     * Get min value of a column.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return mixed
     */
    public function min(string $column, bool $withTrashed = false): mixed
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->min($column);
    }

    /**
     * Get distinct values for a column.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function distinct(string $column, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->distinct($column)->pluck($column);
    }

    /**
     * @param int $id
     * @param array $with = []
     * @param bool $withTrashed
     * @param string $notFoundMessage = 'Не найдено!'
     *
     * @return array|Collection|Model|array<Model>
     */
    public function showByIdOrFailWith(
        int $id,
        array $with = [],
        bool $withTrashed = false,
        string $notFoundMessage = 'Не найдено!',
    ): array|Collection|Model {
        try {
            $query = $this->appendWithTrashedToQuery($withTrashed)
                ->with($with)
                ->findOrFail($id);
        } catch(\Exception $e) {
            throw new \Exception($notFoundMessage, 404);
        }

        return $query;
    }

    /**
     * Get models with eager loading.
     *
     * @param array $relations
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function getWithRelations(array $relations, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->with($relations)->get();
    }

    /**
     * Get first model matching conditions.
     *
     * @param array $conditions
     * @param bool $withTrashed = false
     *
     * @return Model|null
     */
    public function firstWhere(array $conditions, bool $withTrashed = false): ?Model
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($conditions)->first();
    }

    /**
     * Get models where in array.
     *
     * @param string $column
     * @param array $values
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereIn(string $column, array $values, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereIn($column, $values)->get();
    }

    /**
     * Get models where not in array.
     *
     * @param string $column
     * @param array $values
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereNotIn(string $column, array $values, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereNotIn($column, $values)->get();
    }

    /**
     * Get models between two values.
     *
     * @param string $column
     * @param array $range [min, max]
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereBetween(string $column, array $range, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereBetween($column, $range)->get();
    }

    /**
     * Get models by date range.
     *
     * @param string $column
     * @param Carbon $start
     * @param Carbon $end
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function byDateRange(string $column, Carbon $start, Carbon $end, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereDate($column, '>=', $start)
            ->whereDate($column, '<=', $end)->get();
    }

    /**
     * Get models created after a date.
     *
     * @param string $column = 'created_at'
     * @param Carbon $date
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function createdAfter(string $column = 'created_at', Carbon $date, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($column, '>', $date)->get();
    }

    /**
     * Get models created before a date.
     *
     * @param string $column = 'created_at'
     * @param Carbon $date
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function createdBefore(string $column = 'created_at', Carbon $date, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($column, '<', $date)->get();
    }

    /**
     * Get models ordered by column.
     *
     * @param string $column
     * @param string $direction = 'asc'
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function orderedBy(string $column, string $direction = 'asc', bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->orderBy($column, $direction)->get();
    }

    /**
     * Get random models.
     *
     * @param int $count
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function random(int $count, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->inRandomOrder()->limit($count)->get();
    }

    /**
     * Get models with specific columns.
     *
     * @param array $columns
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function select(array $columns, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->select($columns)->get();
    }

    /**
     * Pluck a column.
     *
     * @param string $column
     * @param string $key = null
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function pluck(string $column, ?string $key = null, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->pluck($column, $key);
    }

    /**
     * Get models grouped by column.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function groupBy(string $column, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->groupBy($column)->get();
    }

    /**
     * Get models with trashed only.
     *
     * @return Collection
     */
    public function onlyTrashed(): Collection
    {
        return $this->query()->onlyTrashed()->get();
    }

    /**
     * Get count of trashed models.
     *
     * @return int
     */
    public function trashedCount(): int
    {
        return $this->query()->onlyTrashed()->count();
    }

    /**
     * Get models using cache.
     *
     * @param string $cacheKey
     * @param int $ttl Minutes to cache
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function cachedGet(string $cacheKey, int $ttl = 60, bool $withTrashed = false): Collection
    {
        return Cache::remember($cacheKey, $ttl * 60, function () use ($withTrashed) {
            return $this->appendWithTrashedToQuery($withTrashed)->get();
        });
    }

    /**
     * Get paginated cached list.
     *
     * @param ListDTO|mixed $dto
     * @param string $cacheKey
     * @param int $ttl Minutes to cache
     *
     * @return LengthAwarePaginator
     */
    public function cachedPaginatedList(mixed $dto, string $cacheKey, int $ttl = 60): LengthAwarePaginator
    {
        return Cache::remember($cacheKey, $ttl * 60, function () use ($dto) {
            return $this->getPaginatedList($dto);
        });
    }

    /**
     * Get models with a like search.
     *
     * @param string $column
     * @param string $searchTerm
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function searchLike(string $column, string $searchTerm, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($column, 'like', "%$searchTerm%")->get();
    }

    /**
     * Get models by multiple columns.
     *
     * @param array $columnsValues [column => value]
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereMultiple(array $columnsValues, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        foreach ($columnsValues as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get();
    }

    /**
     * Get models using orWhere.
     *
     * @param array $conditions [[column, operator, value]]
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function orWhere(array $conditions, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        $query->where(function ($q) use ($conditions) {
            foreach ($conditions as $condition) {
                $q->orWhere(...$condition);
            }
        });

        return $query->get();
    }

    /**
     * Get models with count of relation.
     *
     * @param string $relation
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function withCount(string $relation, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->withCount($relation)->get();
    }

    /**
     * Get aggregated sum by group.
     *
     * @param string $groupColumn
     * @param string $sumColumn
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function sumByGroup(string $groupColumn, string $sumColumn, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->groupBy($groupColumn)->select($groupColumn, DB::raw("SUM($sumColumn) as total"))->get();
    }

    /**
     * Get models joined with another table.
     *
     * @param string $table
     * @param string $first
     * @param string $operator = '='
     * @param string $second
     * @param string $type = 'inner'
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function join(string $table, string $first, string $operator = '=', string $second, string $type = 'inner', bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->join($table, $first, $operator, $second, $type)->get();
    }

    /**
     * Get latest model.
     *
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return Model|null
     */
    public function latest(string $column = 'created_at', bool $withTrashed = false): ?Model
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->latest($column)->first();
    }

    /**
     * Get oldest model.
     *
     * @param string $column = 'created_at'
     * @param bool $withTrashed = false
     *
     * @return Model|null
     */
    public function oldest(string $column = 'created_at', bool $withTrashed = false): ?Model
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->oldest($column)->first();
    }

    /**
     * Get models by user ID (assuming 'user_id' column).
     *
     * @param int $userId
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function byUserId(int $userId, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where('user_id', $userId)->get();
    }

    /**
     * Get models excluding IDs.
     *
     * @param array $excludeIds
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function excludeIds(array $excludeIds, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereNotIn('id', $excludeIds)->get();
    }

    /**
     * Get models with null field.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereNull(string $column, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereNull($column)->get();
    }

    /**
     * Get models with not null field.
     *
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereNotNull(string $column, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereNotNull($column)->get();
    }

    /**
     * Get models by boolean flag.
     *
     * @param string $column
     * @param bool $value
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereBoolean(string $column, bool $value, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($column, $value)->get();
    }

    /**
     * Get models by enum value.
     *
     * @param string $column
     * @param string $enumValue
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereEnum(string $column, string $enumValue, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($column, $enumValue)->get();
    }

    /**
     * Get models with JSON contains.
     *
     * @param string $column
     * @param mixed $value
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereJsonContains(string $column, mixed $value, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereJsonContains($column, $value)->get();
    }

    /**
     * Get models ordered by multiple columns.
     *
     * @param array $orders [[column, direction]]
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function orderByMultiple(array $orders, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        foreach ($orders as $order) {
            $query->orderBy($order[0], $order[1] ?? 'asc');
        }

        return $query->get();
    }

    /**
     * Get models with having clause (for aggregates).
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function having(string $column, string $operator, mixed $value, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->having($column, $operator, $value)->get();
    }

    /**
     * Get models using raw query.
     *
     * @param string $rawWhere
     * @param array $bindings
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function rawWhere(string $rawWhere, array $bindings = [], bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->whereRaw($rawWhere, $bindings)->get();
    }

    /**
     * Get count by conditions.
     *
     * @param array $conditions
     * @param bool $withTrashed = false
     *
     * @return int
     */
    public function countBy(array $conditions, bool $withTrashed = false): int
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($conditions)->count();
    }

    /**
     * Get exists by conditions.
     *
     * @param array $conditions
     * @param bool $withTrashed = false
     *
     * @return bool
     */
    public function exists(array $conditions, bool $withTrashed = false): bool
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where($conditions)->exists();
    }

    /**
     * Get first or create (read-only version, checks existence).
     *
     * @param array $attributes
     * @param bool $withTrashed = false
     *
     * @return Model|null
     */
    public function firstOrNull(array $attributes, bool $withTrashed = false): ?Model
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->firstWhere($attributes);
    }

    /**
     * Get models by subquery.
     *
     * @param Closure $subquery
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function whereSubquery(Closure $subquery, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        $query->where($subquery);

        return $query->get();
    }

    /**
     * Get models with global scope applied.
     *
     * @param string $scope
     * @param bool $withTrashed = false
     *
     * @return Collection
     */
    public function withScope(string $scope, bool $withTrashed = false): Collection
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->$scope()->get();
    }

    /**
     * Get value of a single column for a model.
     *
     * @param int $id
     * @param string $column
     * @param bool $withTrashed = false
     *
     * @return mixed
     */
    public function getColumnValue(int $id, string $column, bool $withTrashed = false): mixed
    {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        return $query->where('id', $id)->value($column);
    }

    /**
     * Оставляем только те идентификаторы, которых нету в БД
     *
     * @param array $fieldIds
     * @param string $field
     * @param array $conditions
     * @param bool $withTrashed = false
     *
     * @return array
     */
    public function uncreatedIds(
        array $fieldIds,
        string $field,
        array $conditions = [],
        bool $withTrashed = false
    ): array {
        $query = $this->appendWithTrashedToQuery($withTrashed);

        $existUserIds = $query->where($conditions)
            ->whereIn($field, $fieldIds)
            ->pluck('id')->toArray();

        return array_diff($fieldIds, $existUserIds);
    }
}
