# BaseRepository Layer

Слой **BaseRepository** предназначен исключительно для чтения данных из БД. Наследуемые репозитории используют эти методы для выборок.

## 1. Использование BaseRepository

Пример:
```php
class UserRepository extends BaseRepository {
    public function __construct() {
        parent::__construct(User::class);
    }
}
```

### Методы BaseRepository

1. **query(): Builder**  
   Возвращает новый builder запроса для модели.  
   Аргументы: Нет.  
   Возврат: Builder.  
   Пример:  
   ```php
   $this->query()->where('id', 1)->get();
   ```

2. **getModel(): Model**  
   Возвращает новый экземпляр модели.  
   Аргументы: Нет.  
   Возврат: Model.  
   Пример:  
   ```php
   $this->getModel();
   ```

3. **findAll(): Collection**  
   Возвращает все записи с использованием all() (кэширует в памяти).  
   Аргументы: Нет.  
   Возврат: Collection.  
   Пример:  
   ```php
   $this->findAll();
   ```

4. **getAll(): array|Collection**  
   Возвращает все записи с использованием get().  
   Аргументы: Нет.  
   Возврат: Collection.  
   Пример:  
   ```php
   $this->getAll();
   ```

5. **findByIdOrFail(int $id, bool $withTrashed = false, string $notFoundMessage = 'Не найдено!'): array|Collection|Model**  
   Находит запись по ID или бросает exception. Поддерживает withTrashed.  
   Аргументы: $id - ID; $withTrashed - включить удалённые; $notFoundMessage - сообщение ошибки.  
   Возврат: Model.  
   Пример:  
   ```php
   $this->findByIdOrFail(1);
   ```

6. **isUnique(mixed $dto, array $mapParams, bool $exceptIfExist = true, ...)**  
   Проверяет уникальность по маппингу параметров из DTO. Бросает exception, если не уникально.  
   Аргументы: $dto - DTO; $mapParams - маппинг; $exceptIfExist - бросать ли exception; etc.  
   Возврат: bool или exception.  
   Пример:  
   ```php
   $this->isUnique($dto, ['name' => 'name']);
   ```

7. **findBy(string $column, $value)**  
   Находит первую запись по колонке и значению.  
   Аргументы: $column - колонка; $value - значение.  
   Возврат: Model|null.  
   Пример:  
   ```php
   $this->findBy('email', 'test@ex');
   ```

8. **getBy(string $column, $value): array|Collection**  
   Находит все записи по колонке и значению.  
   Аргументы: $column - колонка; $value - значение.  
   Возврат: Collection.  
   Пример:  
   ```php
   $this->getBy('status', 'active');
   ```

9. **findByColumns(array $data)**  
   Находит первую запись по нескольким колонкам и значениям.  
   Аргументы: $data - массив [колонка => значение].  
   Возврат: Model|null.  
   Пример:  
   ```php
   $this->findByColumns(['id' => 1, 'status' => 'active']);
   ```

10. **findById(mixed $id): array|Collection|Model|null**  
    Находит запись по ID (без fail).  
    Аргументы: $id - ID.  
    Возврат: Model|null.  
    Пример:  
    ```php
    $this->findById(1);
    ```

11. **isAuth(): bool**  
    Проверяет, аутентифицирован ли пользователь.  
    Аргументы: Нет.  
    Возврат: bool.  
    Пример:  
    ```php
    $this->isAuth();
    ```

12. **isRoot(): bool**  
    Проверяет, является ли пользователь root (по конфигу).  
    Аргументы: Нет.  
    Возврат: bool.  
    Пример:  
    ```php
    $this->isRoot();
    ```

13. **getAuthUserId(): int|null**  
    Возвращает ID аутентифицированного пользователя (по конфигу).  
    Аргументы: Нет.  
    Возврат: int|null.  
    Пример:  
    ```php
    $this->getAuthUserId();
    ```

14. **appendWithTrashedToQuery(bool $withTrashed = false): Builder**  
    Создаёт builder с опциональным withTrashed.  
    Аргументы: $withTrashed - включить удалённые.  
    Возврат: Builder.  
    Пример:  
    ```php
    $this->appendWithTrashedToQuery(true);
    ```

15. **count(bool $withTrashed = false): int|null**  
    Возвращает количество записей.  
    Аргументы: $withTrashed - включить удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->count();
    ```

16. **countToday(string $column = 'created_at', bool $withTrashed = false): int|null**  
    Количество записей за сегодня по дате в колонке.  
    Аргументы: $column - колонка даты; $withTrashed - удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->countToday();
    ```

17. **countThisWeek(string $column = 'created_at', bool $withTrashed = false): int|null**  
    Количество за неделю.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->countThisWeek();
    ```

18. **countThisMonth(string $column = 'created_at', bool $withTrashed = false): int|null**  
    Количество за месяц.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->countThisMonth();
    ```

19. **countThisYear(string $column = 'created_at', bool $withTrashed = false): int|null**  
    Количество за год.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->countThisYear();
    ```

20. **sum(string $column, bool $withTrashed = false): float|int|null**  
    Сумма по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->sum('price');
    ```

21. **sumToday(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null**  
    Сумма за сегодня.  
    Аргументы: $column - колонка; $dateColumn - дата; $withTrashed - удалённые.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->sumToday('price');
    ```

22. **sumThisWeek(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null**  
    Сумма за неделю.  
    Аргументы: Аналогично sumToday.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->sumThisWeek('price');
    ```

23. **sumThisMonth(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null**  
    Сумма за месяц.  
    Аргументы: Аналогично.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->sumThisMonth('price');
    ```

24. **sumThisYear(string $column, string $dateColumn = 'created_at', bool $withTrashed = false): float|int|null**  
    Сумма за год.  
    Аргументы: Аналогично.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->sumThisYear('price');
    ```

25. **getLatest(string $column = 'id', int $count = 5, bool $withTrashed = false): mixed**  
    Возвращает последние записи по сортировке (take $count).  
    Аргументы: $column - колонка; $count - количество; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->getLatest();
    ```

26. **avg(string $column, bool $withTrashed = false): float|int|null**  
    Среднее значение по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: float|int|null.  
    Пример:  
    ```php
    $this->avg('price');
    ```

27. **getPaginatedList(mixed $dto): LengthAwarePaginator**  
    Пагинированный список с фильтрами из DTO.  
    Аргументы: $dto - DTO с params.  
    Возврат: LengthAwarePaginator.  
    Пример:  
    ```php
    $this->getPaginatedList($dto);
    ```

28. **max(string $column, bool $withTrashed = false): mixed**  
    Максимальное значение по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: mixed.  
    Пример:  
    ```php
    $this->max('price');
    ```

29. **min(string $column, bool $withTrashed = false): mixed**  
    Минимальное значение по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: mixed.  
    Пример:  
    ```php
    $this->min('price');
    ```

30. **distinct(string $column, bool $withTrashed = false): Collection**  
    Уникальные значения по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->distinct('status');
    ```

31. **showByIdOrFailWith(int $id, array $with = [], bool $withTrashed = false, string $notFoundMessage = 'Не найдено!'): array|Collection|Model**  
    По ID or fail с with-реляциями.  
    Аргументы: $id - ID; $with - реляции; $withTrashed - удалённые; $notFoundMessage - сообщение.  
    Возврат: Model.  
    Пример:  
    ```php
    $this->showByIdOrFailWith(1, ['user']);
    ```

32. **getWithRelations(array $relations, bool $withTrashed = false): Collection**  
    Все записи с реляциями.  
    Аргументы: $relations - массив реляций; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->getWithRelations(['user']);
    ```

33. **firstWhere(array $conditions, bool $withTrashed = false): ?Model**  
    Первая запись по условиям.  
    Аргументы: $conditions - массив условий; $withTrashed - удалённые.  
    Возврат: Model|null.  
    Пример:  
    ```php
    $this->firstWhere(['status' => 'active']);
    ```

34. **whereIn(string $column, array $values, bool $withTrashed = false): Collection**  
    Записи where in.  
    Аргументы: $column - колонка; $values - значения; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereIn('id', [1, 2]);
    ```

35. **whereNotIn(string $column, array $values, bool $withTrashed = false): Collection**  
    Записи where not in.  
    Аргументы: $column - колонка; $values - значения; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereNotIn('id', [3, 4]);
    ```

36. **whereBetween(string $column, array $range, bool $withTrashed = false): Collection**  
    Записи between.  
    Аргументы: $column - колонка; $range - [min, max]; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereBetween('price', [10, 100]);
    ```

37. **byDateRange(string $column, Carbon $start, Carbon $end, bool $withTrashed = false): Collection**  
    Записи по диапазону дат.  
    Аргументы: $column - колонка; $start/end - даты; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->byDateRange('created_at', $start, $end);
    ```

38. **createdAfter(string $column = 'created_at', Carbon $date, bool $withTrashed = false): Collection**  
    Созданные после даты.  
    Аргументы: $column - колонка; $date - дата; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->createdAfter('created_at', $date);
    ```

39. **createdBefore(string $column = 'created_at', Carbon $date, bool $withTrashed = false): Collection**  
    Созданные до даты.  
    Аргументы: $column - колонка; $date - дата; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->createdBefore('created_at', $date);
    ```

40. **orderedBy(string $column, string $direction = 'asc', bool $withTrashed = false): Collection**  
    Записи с сортировкой.  
    Аргументы: $column - колонка; $direction - asc/desc; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->orderedBy('name');
    ```

41. **random(int $count, bool $withTrashed = false): Collection**  
    Рандомные записи (limit $count).  
    Аргументы: $count - количество; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->random(5);
    ```

42. **select(array $columns, bool $withTrashed = false): Collection**  
    Записи с выбором колонок.  
    Аргументы: $columns - массив колонок; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->select(['id', 'name']);
    ```

43. **pluck(string $column, ?string $key = null, bool $withTrashed = false): Collection**  
    Pluck колонки (опционально с ключом).  
    Аргументы: $column - колонка; $key - ключ; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->pluck('name', 'id');
    ```

44. **groupBy(string $column, bool $withTrashed = false): Collection**  
    Группировка по колонке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->groupBy('status');
    ```

45. **onlyTrashed(): Collection**  
    Только мягко удалённые записи.  
    Аргументы: Нет.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->onlyTrashed();
    ```

46. **trashedCount(): int**  
    Количество мягко удалённых записей.  
    Аргументы: Нет.  
    Возврат: int.  
    Пример:  
    ```php
    $this->trashedCount();
    ```

47. **cachedGet(string $cacheKey, int $ttl = 60, bool $withTrashed = false): Collection**  
    Кэшированные все записи.  
    Аргументы: $cacheKey - ключ; $ttl - время; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->cachedGet('all_users');
    ```

48. **cachedPaginatedList(mixed $dto, string $cacheKey, int $ttl = 60): LengthAwarePaginator**  
    Кэшированный пагинированный список.  
    Аргументы: $dto - DTO; $cacheKey - ключ; $ttl - время.  
    Возврат: LengthAwarePaginator.  
    Пример:  
    ```php
    $this->cachedPaginatedList($dto, 'paginated');
    ```

49. **searchLike(string $column, string $searchTerm, bool $withTrashed = false): Collection**  
    Поиск по like в колонке.  
    Аргументы: $column - колонка; $searchTerm - термин; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->searchLike('name', 'test');
    ```

50. **whereMultiple(array $columnsValues, bool $withTrashed = false): Collection**  
    Записи по нескольким колонкам (AND).  
    Аргументы: $columnsValues - [col => val]; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereMultiple(['status' => 'active']);
    ```

51. **orWhere(array $conditions, bool $withTrashed = false): Collection**  
    Записи с orWhere.  
    Аргументы: $conditions - [[col, op, val]]; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->orWhere([['id', '=', 1], ['id', '=', 2]]);
    ```

52. **withCount(string $relation, bool $withTrashed = false): Collection**  
    Записи с count реляции.  
    Аргументы: $relation - реляция; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->withCount('comments');
    ```

53. **sumByGroup(string $groupColumn, string $sumColumn, bool $withTrashed = false): Collection**  
    Сумма по группам.  
    Аргументы: $groupColumn - группировка; $sumColumn - сумма; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->sumByGroup('status', 'price');
    ```

54. **join(string $table, string $first, string $operator = '=', string $second, string $type = 'inner', bool $withTrashed = false): Collection**  
    Записи с join.  
    Аргументы: $table - таблица; $first/second/operator/type - условия; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->join('users', 'posts.user_id', '=', 'users.id');
    ```

55. **latest(string $column = 'created_at', bool $withTrashed = false): ?Model**  
    Последняя запись по сортировке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Model|null.  
    Пример:  
    ```php
    $this->latest();
    ```

56. **oldest(string $column = 'created_at', bool $withTrashed = false): ?Model**  
    Старейшая запись по сортировке.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Model|null.  
    Пример:  
    ```php
    $this->oldest();
    ```

57. **byUserId(int $userId, bool $withTrashed = false): Collection**  
    Записи по user_id.  
    Аргументы: $userId - ID; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->byUserId(1);
    ```

58. **excludeIds(array $excludeIds, bool $withTrashed = false): Collection**  
    Записи, исключая указанные ID.  
    Аргументы: $excludeIds - массив ID; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->excludeIds([1, 2]);
    ```

59. **whereNull(string $column, bool $withTrashed = false): Collection**  
    Записи, где колонка null.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereNull('deleted_at');
    ```

60. **whereNotNull(string $column, bool $withTrashed = false): Collection**  
    Записи, где колонка not null.  
    Аргументы: $column - колонка; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereNotNull('updated_at');
    ```

61. **whereBoolean(string $column, bool $value, bool $withTrashed = false): Collection**  
    Записи по boolean-значению.  
    Аргументы: $column - колонка; $value - true/false; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereBoolean('active', true);
    ```

62. **whereEnum(string $column, string $enumValue, bool $withTrashed = false): Collection**  
    Записи по enum-значению.  
    Аргументы: $column - колонка; $enumValue - значение; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereEnum('status', 'active');
    ```

63. **whereJsonContains(string $column, mixed $value, bool $withTrashed = false): Collection**  
    Записи, где JSON содержит значение.  
    Аргументы: $column - колонка; $value - значение; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereJsonContains('meta', 'key');
    ```

64. **orderByMultiple(array $orders, bool $withTrashed = false): Collection**  
    Записи с сортировкой по нескольким колонкам.  
    Аргументы: $orders - [[col, dir]]; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->orderByMultiple([['name', 'asc'], ['id', 'desc']]);
    ```

65. **rawWhere(string $rawWhere, array $bindings = [], bool $withTrashed = false): Collection**  
    Записи с raw where.  
    Аргументы: $rawWhere - raw; $bindings - биндинги; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->rawWhere('age > ?', [18]);
    ```

66. **countBy(array $conditions, bool $withTrashed = false): int**  
    Количество по условиям.  
    Аргументы: $conditions - условия; $withTrashed - удалённые.  
    Возврат: int.  
    Пример:  
    ```php
    $this->countBy(['status' => 'active']);
    ```

67. **exists(array $conditions, bool $withTrashed = false): bool**  
    Проверка существования по условиям.  
    Аргументы: $conditions - условия; $withTrashed - удалённые.  
    Возврат: bool.  
    Пример:  
    ```php
    $this->exists(['id' => 1]);
    ```

68. **firstOrNull(array $attributes, bool $withTrashed = false): ?Model**  
    Первая запись или null по атрибутам.  
    Аргументы: $attributes - атрибуты; $withTrashed - удалённые.  
    Возврат: Model|null.  
    Пример:  
    ```php
    $this->firstOrNull(['name' => 'John']);
    ```

69. **whereSubquery(Closure $subquery, bool $withTrashed = false): Collection**  
    Записи с subquery in where.  
    Аргументы: $subquery - closure; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->whereSubquery(fn($q) => $q->where('id', 1));
    ```

70. **withScope(string $scope, bool $withTrashed = false): Collection**  
    Записи с применением scope.  
    Аргументы: $scope - имя scope; $withTrashed - удалённые.  
    Возврат: Collection.  
    Пример:  
    ```php
    $this->withScope('active');
    ```

71. **getColumnValue(int $id, string $column, bool $withTrashed = false): mixed**  
    Значение колонки по ID.  
    Аргументы: $id - ID; $column - колонка; $withTrashed - удалённые.  
    Возврат: mixed.  
    Пример:  
    ```php
    $this->getColumnValue(1, 'name');
    ```

72. **uncreatedIds(array $fieldIds, string $field, array $conditions = [], bool $withTrashed = false): array**  
    Возвращает ID, которых нет в БД по полю и условиям.  
    Аргументы: $fieldIds - ID; $field - поле; $conditions - условия; $withTrashed - удалённые.  
    Возврат: array.  
    Пример:  
    ```php
    $this->uncreatedIds([1, 2], 'user_id');
    ```
