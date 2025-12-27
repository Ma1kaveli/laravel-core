# Документация к библиотеке "makaveli/laravel-core"

## Введение

Библиотека **makaveli/laravel-core** представляет собой базовый набор инструментов для разработки приложений на фреймворке Laravel. Она предназначена для упрощения работы с данными, моделями, запросами, валидацией и другими аспектами backend-разработки. Библиотека включает в себя классы для работы с DTO (Data Transfer Objects), репозиториями, сервисами, хелперами, трейтами и валидаторами, что позволяет стандартизировать код и повысить его поддерживаемость.

Основные цели библиотеки:
- Обеспечение единого подхода к обработке форм, списков и данных.
- Поддержка мягкого удаления (soft deletes) и логирования действий (создатель, обновитель, удалитель).
- Интеграция с Laravel (версии 10-12) и PHP 8.2+.
- Зависимости: Laravel Framework, makaveli/laravel-converter (для конвертации данных).

Библиотека лицензирована под MIT и разработана Michael Udovenko.

## Установка

1. Установите библиотеку через Composer:
   ```
   composer require makaveli/laravel-core
   ```

2. Опубликуйте конфигурационный файл:
   ```
   php artisan vendor:publish --tag=core-config
   ```
   Это создаст файл `config/core.php` в вашем проекте.

3. Зарегистрируйте провайдер в `config/app.php` (если не зарегистрирован автоматически):
   ```php
   'providers' => [
       // ...
       \Core\Providers\CoreServiceProvider::class,
   ],
   ```

4. Убедитесь, что зависимость `makaveli/laravel-converter` установлена (версия 1.0.3).

## Конфигурация

Конфигурационный файл `config/core.php` содержит настройки:
- `'log' => \Illuminate\Support\Facades\Log::class`: Класс для логирования ошибок.
- `'form_dto'`: Настройки для DTO форм.
  - `'common_request_fields' => ['organizationId']`: Общие поля запроса (например, ID организации).
  - `'context_resolver' => \Core\DTO\Resolvers\BaseAwareOrganizationContextResolver::class`: Резолвер контекста (по умолчанию — базовый с учетом организации).
- `'repository'`: Настройки репозиториев.
  - `'is_root_field' => 'is_superadministrator'`: Поле для проверки root-прав.
  - `'user_id_field' => 'id'`: Поле ID пользователя.
- `'soft-model-base'`: Настройки для моделей с мягким удалением.
  - `'user_model' => \Illuminate\Database\Eloquent\Model::class`: Модель пользователя.
  - Ключи для логирования действий: `'created_by_key'`, `'updated_by_key'`, `'deleted_by_key'`.

Вы можете переопределить эти значения в вашем `config/core.php`.

## Основные компоненты

Библиотека разделена на модули (неймспейс `Core`). Ниже описаны ключевые части.

### DTO (Data Transfer Objects)

DTO используются для передачи данных между слоями приложения. Они интегрируются с аутентификацией и резолверами контекста.

- **DynamicDTO**: Динамический DTO для произвольных данных. Конструктор принимает массив данных, пользователя, ID организации/роли и ID сущности.
- **ExecutionOptionsDTO**: Опции выполнения (транзакции, валидация, логирование ошибок). Методы: `appendGetFunc()`, `withoutTransaction()`, `withoutValidation()`, `withoutErrorLog()`.
- **FormDTO**: Базовый DTO для форм. Обрабатывает базовые данные из запроса (использует ConverterDTO из зависимости).
- **ListDTO**: DTO для списков. Поддерживает параметры запроса (search, showDeleted и т.д.).
- **OnceDTO**: DTO для разовых операций (с параметрами).

Резолверы контекста (в `Core\DTO\Resolvers`):
- **AbstractContextResolver**: Базовый абстрактный класс.
- **ActiveOrganizationContextResolver**: Резолвит на основе активной роли.
- **BaseAwareOrganizationContextResolver**: Учитывает базовую роль и payload.
- **UserRoleContextResolver**: На основе роли пользователя.

### Helpers

Вспомогательные классы в `Core\Helpers`:
- **Filters**: Трансформация булевых значений (`transformBoolean()`).
- **Paginations**: Генерация пустой пагинации (`generateEmpty()`).
- **Phone**: Очистка номеров телефона (`stringNumberWithoutSymbols()`).
- **Relations**: Работа с отношениями (перемещение первого элемента коллекции в одиночное отношение, `moveFirstRelationItem()`, `moveFirstRelationItemInPaginator()`).

### Http и Interfaces

- **LaravelHttpContext**: Реализация интерфейса `IHttpContext` для работы с запросами Laravel.
- Интерфейсы: `IHttpContext` (доступ к запросу), `IListDTO` (для списков DTO).

### Models

- **SoftModelBase**: Базовая модель с мягким удалением, трейтами `SoftModel` и `ActionInfo`. Автоматически логирует действия (created_by, updated_by, deleted_by).

### Providers

- **CoreServiceProvider**: Регистрирует конфиг и singleton для `IHttpContext`.

### Repositories

- **BaseRepository**: Базовый репозиторий для работы с моделями. Поддерживает аутентификацию, подсчеты, пагинацию, уникальность и многое другое.

  **Подробная документация методов**: См. файл с документацией **[Docs](./src/Repositories/README.md)** — там описаны все методы класса (findAll, getAll, findByIdOrFail, isUnique и т.д., всего более 50 методов для запросов, подсчетов, пагинации и кэширования).

### Requests

- **BaseFormRequest**: Базовый класс для форм-запросов. Поддерживает контексты (CREATE, UPDATE, DELETE) с правилами валидации, сообщениями и хуками для валидатора.

### Resources

- **BaseResource**: Базовый JSON-ресурс. Поддерживает дополнительные поля и коллекции.

### Rules

Валидационные правила в `Core\Rules`:
- **ValidFlatNumberOrRange**: Валидация номеров квартир (одиночное число или диапазон >0).
- **ValidPassword**: Пароль минимум 8 символов с заглавными/строчными буквами и цифрами.
- **ValidRuPhone**: Валидация российских номеров телефона по regex.

### Services

- **BaseService**: Базовый сервис для CRUD-операций. Поддерживает транзакции, валидацию, логирование.

  **Подробная документация методов**: См. файл. с документацией **[Docs](./src/Services/README.md)** — там описаны все методы класса (create, update, destroy, restore, bulkDelete, incrementField и т.д., всего более 50 методов для манипуляций с моделями, отношениями, JSON-полями и т.д.).

### Traits

- **ActionInfo**: Трейт для логирования действий (creator, updator, deletor). Добавляет отношения к модели пользователя.
- **SoftModel**: Трейт для мягкого удаления/восстановления с транзакциями и логированием ошибок.

### Validators

- **DateRangeValidator**: Валидация диапазонов дат (с временем или без). Методы: `validate()`, `validateOnlyDate()`.

## Примеры использования

### Создание DTO для формы
```php
use Core\DTO\DynamicDTO;

$dto = OnceDTO::make($id);

class ModelFormDTO extends FormDTO
{
    public function __construct(
        public readonly string $name,
        Authenticatable $user,
        ?int $organizationId,
        ?int $roleId,
        ?int $id = null,
    ) {
        parent::__construct($user, $organizationId, $roleId, $id);
    }

    public static function fromRequest(ModelRequest $request, ?int $id = null): static
    {
        $baseData = self::processBaseData(
            $request,
            $id,
            ['name']
        );

        return new static(
            name: $baseData['converted_data']['name'],
            user: $baseData['user'],
            organizationId: $baseData['organization_id'],
            id: $baseData['id'],
        );
    }
}
```

### Работа с репозиторием
```php
use Core\Repositories\BaseRepository;

class ModelRepository extends BaseRepository;
{
    public function __construct()
    {
        parent::__construct(Model::class);
    }
}

$repo = new ModelRepository();

$repo->findByIdOrFail(1);
```

### Сервис для обновления
```php
use Core\Services\BaseService;

class ModelService extends BaseService;
{
    public function __construct()
    {
        parent::__construct(Model::class);
    }
}

$service = new ModelService(Model::class);
$service->update($model, $data);
```

## Рекомендации

- Используйте резолверы контекста для обработки organization_id и role_id в DTO.
- Для моделей наследуйте от `SoftModelBase` для поддержки мягкого удаления.
- В репозиториях и сервисах проверяйте аутентификацию с помощью `isAuth()`, `isRoot()`.
- Для валидации дат используйте `DateRangeValidator` в кастомных валидаторах.

Если нужны дополнения или уточнения, обратитесь к исходному коду. Библиотека открыта для вклада на GitHub: https://github.com/Ma1kaveli/laravel-core.

## License
MIT License. See [LICENSE](LICENSE) for details.
