# BaseService Layer

Слой **BaseService** предназначен исключительно для операций записи в базу данных (create, update, delete и т.д.). Он не должен использоваться для чтения данных — для этого предназначен BaseRepository. BaseService предоставляет базовые методы для манипуляции моделями, которые можно расширять в наследуемых сервисах.

Основные компоненты:
- `BaseService` — абстрактный класс, от которого наследуются все сервисы для записи.
- Методы фокусируются на создании, обновлении, удалении и других операциях записи.

## 1. Использование BaseService

BaseService принимает модель в конструкторе и предоставляет методы для работы с ней.

Пример наследования:
```php
class UserService extends BaseService {
    public function __construct(User $model) {
        parent::__construct($model);
    }

    public function createUser(array $data): Model {
        return $this->create($data);
    }
}
```

### Методы BaseService

1. **create(array $data): Model**  
   Создаёт новый экземпляр модели на основе переданных данных. Использует ::create() модели.  
   Аргументы: $data - ассоциативный массив атрибутов модели.  
   Возврат: Созданная модель.  
   Пример:  
   ```php
   $this->create(['name' => 'John', 'email' => 'john@example.com']);
   ```

2. **update(Model $model, array $data): Model**  
   Обновляет существующий экземпляр модели новыми данными. Использует tap() для цепочки и update().  
   Аргументы: $model - экземпляр модели; $data - массив обновлений.  
   Возврат: Обновлённая модель.  
   Пример:  
   ```php
   $this->update($user, ['name' => 'Jane']);
   ```

3. **destroy(Model $data, ExecutionOptionsDTO $config, string $alreadyDeleteMessage, string $successMessage, string $errorMessage): array**  
   Выполняет мягкое удаление модели с использованием SoftDeletes. Возвращает массив с результатом операции (успех/ошибка).  
   Аргументы: $data - модель; $config - опции (транзакции, логи); сообщения для случаев.  
   Возврат: Массив с сообщением и статусом.  
   Пример:  
   ```php
   $this->destroy($model, $config, 'Уже удалено', 'Успех', 'Ошибка');
   ```

4. **restore(Model $data, ExecutionOptionsDTO $config, string $notDeleteMessage, string $successMessage, string $errorMessage): array**  
   Восстанавливает мягко удалённую модель. Возвращает массив с результатом.  
   Аргументы: Аналогично destroy, но для восстановления.  
   Возврат: Массив с сообщением и статусом.  
   Пример:  
   ```php
   $this->restore($model, $config, 'Не удалено', 'Успех', 'Ошибка');
   ```

5. **softDelete(Model $data): void**  
   Выполняет мягкое удаление модели без возврата результата.  
   Аргументы: $data - модель.  
   Возврат: Нет.  
   Пример:  
   ```php
   $this->softDelete($model);
   ```

6. **softRestore(Model $data): void**  
   Восстанавливает мягко удалённую модель без возврата результата.  
   Аргументы: $data - модель.  
   Возврат: Нет.  
   Пример:  
   ```php
   $this->softRestore($model);
   ```

7. **forceDelete(Model $data): void**  
   Выполняет полное (force) удаление модели, без возможности восстановления.  
   Аргументы: $data - модель.  
   Возврат: Нет.  
   Пример:  
   ```php
   $this->forceDelete($model);
   ```

8. **insert(array $data): bool**  
   Массово вставляет несколько записей в таблицу.  
   Аргументы: $data - массив ассоциативных массивов.  
   Возврат: True при успехе.  
   Пример:  
   ```php
   $this->insert([['name' => 'A'], ['name' => 'B']]);
   ```

9. **bulkDelete(array $ids, bool $soft = true): int**  
   Массово удаляет записи по ID (мягко или полностью). Возвращает количество удалённых.  
   Аргументы: $ids - массив ID; $soft - флаг мягкого удаления.  
   Возврат: Количество удалённых.  
   Пример:  
   ```php
   $this->bulkDelete([1, 2]);
   ```

10. **deleteBy(array $conditions, bool $soft = true): int**  
    Удаляет записи по условиям (мягко или полностью). Возвращает количество удалённых.  
    Аргументы: $conditions - массив условий; $soft - флаг мягкого удаления.  
    Возврат: Количество удалённых.  
    Пример:  
    ```php
    $this->deleteBy(['status' => 'inactive']);
    ```

11. **bulkUpdate(array $updates, array $conditions): int**  
    Массово обновляет записи по условиям. Возвращает количество обновлённых.  
    Аргументы: $updates - массив обновлений; $conditions - условия.  
    Возврат: Количество обновлённых.  
    Пример:  
    ```php
    $this->bulkUpdate(['status' => 'active'], ['status' => 'pending']);
    ```

12. **batchedInsert(array $data, int $batchSize = 500): void**  
    Вставляет данные батчами для избежания проблем с памятью при больших объёмах.  
    Аргументы: $data - массив данных; $batchSize - размер батча.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->batchedInsert($largeData);
    ```

13. **incrementField(Model $model, string $field, int $amount = 1): Model**  
    Инкрементирует значение числового поля на указанное количество.  
    Аргументы: $model - модель; $field - поле; $amount - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->incrementField($model, 'views');
    ```

14. **decrementField(Model $model, string $field, int $amount = 1): Model**  
    Декрементирует значение числового поля на указанное количество.  
    Аргументы: $model - модель; $field - поле; $amount - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->decrementField($model, 'stock');
    ```

15. **attachRelations(Model $model, string $relation, array $ids, array $attributes = []): void**  
    Прикрепляет связанные модели в many-to-many реляции, с опциональными pivot-атрибутами.  
    Аргументы: $model - родитель; $relation - имя реляции; $ids - ID; $attributes - pivot.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->attachRelations($post, 'tags', [1, 2]);
    ```

16. **detachRelations(Model $model, string $relation, array $ids = []): void**  
    Открепляет связанные модели в many-to-many реляции. Если $ids пуст — все.  
    Аргументы: $model - родитель; $relation - имя реляции; $ids - ID.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->detachRelations($post, 'tags', [1]);
    ```

17. **syncRelations(Model $model, string $relation, array $ids): void**  
    Синхронизирует связанные модели в many-to-many (удаляет лишние, добавляет новые).  
    Аргументы: $model - родитель; $relation - имя реляции; $ids - новые ID.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->syncRelations($post, 'tags', [1, 3]);
    ```

18. **toggleBoolean(Model $model, string $field): Model**  
    Переключает значение boolean-поля (true <-> false).  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->toggleBoolean($model, 'active');
    ```

19. **setTimestamp(Model $model, string $field, $value = null): Model**  
    Устанавливает значение timestamp-поля (по умолчанию Carbon::now()).  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setTimestamp($model, 'approved_at');
    ```

20. **archive(Model $model): Model**  
    Архтивирует модель, устанавливая 'archived_at' в текущее время.  
    Аргументы: $model - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->archive($model);
    ```

21. **unarchive(Model $model): Model**  
    Разархивирует модель, устанавливая 'archived_at' в null.  
    Аргументы: $model - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->unarchive($model);
    ```

22. **truncate(): void**  
    Полностью очищает таблицу (truncate), удаляя все записи без возможности восстановления.  
    Аргументы: Нет.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->truncate();
    ```

23. **upsert(array $data, array $uniqueBy, array $updateColumns = []): void**  
    Выполняет upsert (вставка или обновление при конфликте по uniqueBy).  
    Аргументы: $data - данные; $uniqueBy - уникальные колонки; $updateColumns - обновляемые.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->upsert($data, ['email'], ['name']);
    ```

24. **lockForUpdate(Model $model): Model**  
    Блокирует модель для обновления (pessimistic lock).  
    Аргументы: $model - модель.  
    Возврат: Заблокированная модель.  
    Пример:  
    ```php
    $this->lockForUpdate($model);
    ```

25. **addSlug(Model $model, string $field): Model**  
    Генерирует и устанавливает slug на основе указанного поля (использует Str::slug).  
    Аргументы: $model - модель; $field - поле для slug.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->addSlug($model, 'title');
    ```

26. **setDefaultIfNull(Model $model, string $field, mixed $default): Model**  
    Устанавливает default-значение поля, если оно null.  
    Аргументы: $model - модель; $field - поле; $default - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setDefaultIfNull($model, 'status', 'pending');
    ```

27. **mergeArrayField(Model $model, string $field, array $newValues): Model**  
    Сливает новые значения в array/JSON-поле.  
    Аргументы: $model - модель; $field - поле; $newValues - новые значения.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->mergeArrayField($model, 'tags', ['new']);
    ```

28. **removeFromArrayField(Model $model, string $field, array $valuesToRemove): Model**  
    Удаляет указанные значения из array/JSON-поля.  
    Аргументы: $model - модель; $field - поле; $valuesToRemove - удаляемые.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->removeFromArrayField($model, 'tags', ['old']);
    ```

29. **appendToArrayField(Model $model, string $field, mixed $value): Model**  
    Добавляет значение в конец array/JSON-поля.  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->appendToArrayField($model, 'list', 'item');
    ```

30. **prependToArrayField(Model $model, string $field, mixed $value): Model**  
    Добавляет значение в начало array/JSON-поля.  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->prependToArrayField($model, 'list', 'item');
    ```

31. **setJsonField(Model $model, string $field, string $key, mixed $value): Model**  
    Устанавливает значение по ключу в JSON-поле.  
    Аргументы: $model - модель; $field - поле; $key - ключ; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setJsonField($model, 'meta', 'key', 'val');
    ```

32. **unsetJsonField(Model $model, string $field, string $key): Model**  
    Удаляет ключ из JSON-поля.  
    Аргументы: $model - модель; $field - поле; $key - ключ.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->unsetJsonField($model, 'meta', 'key');
    ```

33. **mergeJsonField(Model $model, string $field, array $newData): Model**  
    Сливает новые данные в JSON-поле.  
    Аргументы: $model - модель; $field - поле; $newData - новые данные.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->mergeJsonField($model, 'meta', ['new' => 'val']);
    ```

34. **clearField(Model $model, string $field): Model**  
    Очищает поле, устанавливая в null.  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->clearField($model, 'temp');
    ```

35. **duplicate(Model $model, array $overrides = []): Model**  
    Создаёт копию модели с опциональными перезаписями.  
    Аргументы: $model - модель; $overrides - перезаписи.  
    Возврат: Новая модель.  
    Пример:  
    ```php
    $this->duplicate($model, ['name' => 'Copy']);
    ```

36. **setRandomValue(Model $model, string $field, int $length = 32): Model**  
    Устанавливает случайную строку в поле.  
    Аргументы: $model - модель; $field - поле; $length - длина.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setRandomValue($model, 'token');
    ```

37. **hashField(Model $model, string $field): Model**  
    Хэширует текущее значение поля (bcrypt).  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->hashField($model, 'password');
    ```

38. **hasMake(Model $model, string $field, string $value): Model**  
    Хэширует и устанавливает новое значение в поле.  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->hasMake($model, 'password', 'newpass');
    ```

39. **descryptField(Model $model, string $field): Model**  
    Декриптует значение поля.  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->descryptField($model, 'encrypted');
    ```

40. **encryptField(Model $model, string $field): Model**  
    Криптует значение поля.  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->encryptField($model, 'data');
    ```

41. **setUuid(Model $model, string $field): Model**  
    Устанавливает UUID в поле.  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setUuid($model, 'uuid');
    ```

42. **touch(Model $model): Model**  
    Обновляет timestamps модели без изменений атрибутов.  
    Аргументы: $model - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->touch($model);
    ```

43. **pushToJsonArray(Model $model, string $field, mixed $value): Model**  
    Добавляет значение в JSON-array поле с использованием raw SQL.  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->pushToJsonArray($model, 'array_field', 'new');
    ```

44. **popFromJsonArray(Model $model, string $field, int $index = -1): Model**  
    Удаляет значение из JSON-array поля по индексу (default последний).  
    Аргументы: $model - модель; $field - поле; $index - индекс.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->popFromJsonArray($model, 'array_field');
    ```

45. **changeIsApprove(Model $model, OnceDTO $dto): Model**  
    Переключает is_approved и устанавливает updated_by.  
    Аргументы: $model - модель; $dto - DTO с user.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->changeIsApprove($model, $dto);
    ```

46. **insertChunk(array $insertData, int $length = 1000)**  
    Вставляет данные чанками.  
    Аргументы: $insertData - данные; $length - размер чанка.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->insertChunk($data);
    ```

47. **changeIsOwner(Model $data, $isOwner = false): Model**  
    Устанавливает is_owner в указанное значение (default false).  
    Аргументы: $data - модель; $isOwner - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->changeIsOwner($model);
    ```

48. **setIfChanged(Model $model, string $field, mixed $value): Model**  
    Устанавливает значение, если оно отличается от текущего.  
    Аргументы: $model - модель; $field - поле; $value - значение.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setIfChanged($model, 'name', 'new');
    ```

49. **setFlagTrue(Model $model, string $field): Model**  
    Устанавливает флаг в true, если был false.  
    Аргументы: $model - модель; $field - поле.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setFlagTrue($model, 'active');
    ```

50. **setIsViewed(Model $data): Model**  
    Устанавливает is_viewed в true.  
    Аргументы: $data - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setIsViewed($model);
    ```

51. **changeIsPublished(Model $data): Model**  
    Переключает is_published.  
    Аргументы: $data - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->changeIsPublished($model);
    ```

52. **read(Model $data): Model**  
    Устанавливает read_at в текущее время.  
    Аргументы: $data - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->read($model);
    ```

53. **insertManyByField(array $values, string $fieldName, array $extraData = [], bool $timestamps = true)**  
    Массово вставляет записи по полю с доп. данными.  
    Аргументы: $values - значения; $fieldName - поле; $extraData - доп; $timestamps - таймстампы.  
    Возврат: Нет.  
    Пример:  
    ```php
    $this->insertManyByField($ids, 'user_id');
    ```

54. **setPassword(Model $data, string $password): Model**  
    Устанавливает хэшированный пароль.  
    Аргументы: $data - модель; $password - пароль.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->setPassword($model, 'pass');
    ```

55. **verified(Model $data): Model**  
    Устанавливает is_verified в true.  
    Аргументы: $data - модель.  
    Возврат: Обновлённая модель.  
    Пример:  
    ```php
    $this->verified($model);
    ```
