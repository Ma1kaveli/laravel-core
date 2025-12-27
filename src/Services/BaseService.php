<?php

namespace Core\Services;

use Core\DTO\ExecutionOptionsDTO;
use Core\DTO\OnceDTO;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class BaseService
{
    /**
     * Model class name
     */
    protected readonly string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return Builder
     */
    protected function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * Creates a new model instance.
     *
     * @param array $data The data to fill the model with.
     *
     * @return Model The created model instance.
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * Updates an existing model instance.
     *
     * @param Model $model The model instance to update
     * @param array $data The data to update the model with.
     *
     * @return Model The updated model instance.
     */
    public function update(Model $model, array $data): Model
    {
        return tap($model)->update($data);
    }

    /**
     * Soft deletes a model instance. who has SoftModel trait
     *
     * @param Model $data The model instance to soft delete.
     *
     * @return array
     */
    public function destroy(
        Model $data,
        ExecutionOptionsDTO $config,
        string $alreadyDeleteMessage,
        string $successMessage,
        string $errorMessage,
    ): array {
        return $data->destroyModel(
            $data,
            $alreadyDeleteMessage,
            $successMessage,
            $errorMessage,
            $config->withTransaction,
            $config->writeErrorLog
        );
    }

    /**
     * Restores a soft-deleted model instance who has SoftModel trait
     *
     * @param Model $data The model instance to restore.
     *
     * @return array
     */
    public function restore(
        Model $data,
        ExecutionOptionsDTO $config,
        string $notDeleteMessage,
        string $successMessage,
        string $errorMessage,
    ): array {
        return $data->restoreModel(
            $data,
            $notDeleteMessage,
            $successMessage,
            $errorMessage,
            $config->withTransaction,
            $config->writeErrorLog
        );
    }

    /**
     * Soft deletes a model instance.
     *
     * @param Model $data The model instance to soft delete.
     *
     * @return void
     */
    public function softDelete(Model $data): void
    {
        $data->delete();
    }

    /**
     * Restores a soft-deleted model instance.
     *
     * @param Model $data The model instance to restore.
     * @return void
     */
    public function softRestore(Model $data): void
    {
        $data->restore();
    }

    /**
     * Force deletes a model instance (permanent deletion).
     *
     * @param Model $data The model instance to force delete.
     *
     * @return void
     */
    public function forceDelete(Model $data): void
    {
        $data->forceDelete();
    }

    /**
     * Inserts multiple records into the database.
     *
     * @param array $data An array of associative arrays for bulk insert.
     *
     * @return bool True if the insert was successful.
     */
    public function insert(array $data): bool
    {
        return $this->modelClass::insert($data);
    }

    /**
     * Deletes multiple models by their IDs.
     *
     * @param array $ids The array of IDs to delete.
     * @param bool $soft Whether to soft delete or force delete.
     *
     * @return int The number of deleted records.
     */
    public function bulkDelete(array $ids, bool $soft = true): int
    {
        $query = $this->query()->whereIn('id', $ids);

        return $soft ? $query->delete() : $query->forceDelete();
    }

    /**
     * Deletes multiple models by conditions
     *
     * @param array $conditions The where conditions (e.g., ['status' => 'active']).
     * @param bool $soft $soft Whether to soft delete or force delete.
     *
     * @return int The number of deleted records.
     */
    public function deleteBy(array $conditions, bool $soft = true): int
    {
        $query = $this->query()->where($conditions);

        return $soft ? $query->delete() : $query->forceDelete();
    }

    /**
     * Updates multiple records based on a condition.
     *
     * @param array $updates The data to update.
     * @param array $conditions The where conditions (e.g., ['status' => 'active']).
     *
     * @return int The number of updated records.
     */
    public function bulkUpdate(array $updates, array $conditions): int
    {
        return $this->query()->where($conditions)->update($updates);
    }

    /**
     * Inserts data in batches to avoid memory issues.
     *
     * @param array $data An array of associative arrays for insert.
     * @param int $batchSize The size of each batch.
     *
     * @return void
     */
    public function batchedInsert(array $data, int $batchSize = 500): void
    {
        collect($data)->chunk($batchSize)->each(function (Collection $chunk) {
            $this->query()->insert($chunk->toArray());
        });
    }

    /**
     * Increments a field value on a model.
     *
     * @param Model $model The model instance.
     * @param string $field The field to increment.
     * @param int $amount The amount to increment by.
     *
     * @return Model The updated model.
     */
    public function incrementField(Model $model, string $field, int $amount = 1): Model
    {
        $model->increment($field, $amount);

        return $model;
    }

    /**
     * Decrements a field value on a model.
     *
     * @param Model $model The model instance.
     * @param string $field The field to decrement.
     * @param int $amount The amount to decrement by.
     *
     * @return Model The updated model.
     */
    public function decrementField(Model $model, string $field, int $amount = 1): Model
    {
        $model->decrement($field, $amount);

        return $model;
    }

    /**
     * Attaches related models in a many-to-many relationship.
     *
     * @param Model $model The parent model.
     * @param string $relation The relationship name.
     * @param array $ids The IDs to attach.
     * @param array $attributes Additional pivot attributes.
     *
     * @return void
     */
    public function attachRelations(Model $model, string $relation, array $ids, array $attributes = []): void
    {
        $model->$relation()->attach($ids, $attributes);
    }

    /**
     * Detaches related models in a many-to-many relationship.
     *
     * @param Model $model The parent model.
     * @param string $relation The relationship name.
     * @param array $ids The IDs to detach.
     * @return void
     */
    public function detachRelations(Model $model, string $relation, array $ids = []): void
    {
        $model->$relation()->detach($ids);
    }

    /**
     * Syncs related models in a many-to-many relationship.
     *
     * @param Model $model The parent model.
     * @param string $relation The relationship name.
     * @param array $ids The IDs to sync.
     * @return void
     */
    public function syncRelations(Model $model, string $relation, array $ids): void
    {
        $model->$relation()->sync($ids);
    }

    /**
     * Toggles a boolean field on a model.
     *
     * @param Model $model The model instance.
     * @param string $field The boolean field to toggle.
     *
     * @return Model The updated model.
     */
    public function toggleBoolean(Model $model, string $field): Model
    {
        return tap($model)->update([
            $field => !$model->$field
        ]);
    }

    /**
     * Sets a timestamp field on a model.
     *
     * @param Model $model The model instance.
     * @param string $field The timestamp field (e.g., 'archived_at').
     * @param mixed $value The value to set (default now()).
     *
     * @return Model The updated model.
     */
    public function setTimestamp(Model $model, string $field, $value = null): Model
    {
        if (is_null($value)) {
            $value = Carbon::now();
        }

        return tap($model)->update([$field => $value]);
    }

    /**
     * Archives a model by setting an 'archived_at' timestamp.
     *
     * @param Model $model The model to archive.
     * @return Model The archived model.
     */
    public function archive(Model $model): Model
    {
        return $this->setTimestamp($model, 'archived_at');
    }

    /**
     * Unarchives a model by nulling 'archived_at'.
     *
     * @param Model $model The model to unarchive.
     *
     * @return Model The unarchived model.
     */
    public function unarchive(Model $model): Model
    {
        return tap($model)->update(['archived_at' => null]);
    }

    /**
     * Truncates a table (deletes all records).
     *
     * @return void
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Upserts data (insert or update on conflict).
     *
     * @param array $data The data for upsert.
     * @param array $uniqueBy The unique columns for conflict.
     * @param array $updateColumns The columns to update on conflict.
     *
     * @return void
     */
    public function upsert(array $data, array $uniqueBy, array $updateColumns = []): void
    {
        $this->query()->upsert($data, $uniqueBy, $updateColumns);
    }

    /**
     * Locks a model for update (pessimistic locking).
     *
     * @param Model $model The model to lock.
     *
     * @return Model The locked model.
     */
    public function lockForUpdate(Model $model): Model
    {
        return $model->newQuery()->where(
            'id',
            $model->id
        )->lockForUpdate()->firstOrFail();
    }

    /**
     * Adds a slug to a model based on a field.
     *
     * @param Model $model The model instance.
     * @param string $field The field to slugify (e.g., 'name').
     *
     * @return Model The updated model with slug.
     */
    public function addSlug(Model $model, string $field): Model
    {
        return tap($model)->update([
            'slug' => Str::slug($model->$field)
        ]);
    }

    /**
     * Sets a default value for a field if null.
     *
     * @param Model $model The model instance.
     * @param string $field The field to check.
     * @param mixed $default The default value.
     *
     * @return Model The updated model if changed.
     */
    public function setDefaultIfNull(Model $model, string $field, mixed $default): Model
    {
        if (is_null($model->$field)) {
            return tap($model)->update([$field => $default]);
        }

        return $model;
    }

    /**
     * Merges array field values (e.g., JSON column).
     *
     * @param Model $model The model instance.
     * @param string $field The array/JSON field.
     * @param array $newValues The values to merge.
     *
     * @return Model The updated model.
     */
    public function mergeArrayField(Model $model, string $field, array $newValues): Model
    {
        $current = $model->$field ?? [];
        $merged = array_merge($current, $newValues);

        return tap($model)->update([$field => $merged]);
    }

    /**
     * Removes values from array field.
     *
     * @param Model $model The model instance.
     * @param string $field The array/JSON field.
     * @param array $valuesToRemove The values to remove.
     *
     * @return Model The updated model.
     */
    public function removeFromArrayField(Model $model, string $field, array $valuesToRemove): Model
    {
        $current = $model->$field ?? [];
        $updated = array_diff($current, $valuesToRemove);

        return tap($model)->update([$field => array_values($updated)]);
    }

    /**
     * Appends a value to an array field.
     *
     * @param Model $model The model instance.
     * @param string $field The array/JSON field.
     * @param mixed $value The value to append.
     *
     * @return Model The updated model.
     */
    public function appendToArrayField(Model $model, string $field, mixed $value): Model
    {
        $current = $model->$field ?? [];
        $current[] = $value;

        return tap($model)->update([$field => $current]);
    }

    /**
     * Prepends a value to an array field.
     *
     * @param Model $model The model instance.
     * @param string $field The array/JSON field.
     * @param mixed $value The value to prepend.
     *
     * @return Model The updated model.
     */
    public function prependToArrayField(Model $model, string $field, mixed $value): Model
    {
        $current = $model->$field ?? [];
        array_unshift($current, $value);

        return tap($model)->update([$field => $current]);
    }

    /**
     * Sets a JSON field value.
     *
     * @param Model $model The model instance.
     * @param string $field The JSON field.
     * @param string $key The JSON key to set.
     * @param mixed $value The value to set.
     *
     * @return Model The updated model.
     */
    public function setJsonField(Model $model, string $field, string $key, mixed $value): Model
    {
        $json = $model->$field ?? [];
        $json[$key] = $value;

        return tap($model)->update([$field => $json]);
    }

    /**
     * Unsets a JSON field key.
     *
     * @param Model $model The model instance.
     * @param string $field The JSON field.
     * @param string $key The JSON key to unset.
     *
     * @return Model The updated model.
     */
    public function unsetJsonField(Model $model, string $field, string $key): Model
    {
        $json = $model->$field ?? [];
        unset($json[$key]);

        return tap($model)->update([$field => $json]);
    }

    /**
     * Merges data into a JSON field.
     *
     * @param Model $model The model instance.
     * @param string $field The JSON field.
     * @param array $newData The data to merge.
     *
     * @return Model The updated model.
     */
    public function mergeJsonField(Model $model, string $field, array $newData): Model
    {
        $json = $model->$field ?? [];
        $merged = array_merge($json, $newData);

        return tap($model)->update([$field => $merged]);
    }

    /**
     * Clears a field (sets to null or empty).
     *
     * @param Model $model The model instance.
     * @param string $field The field to clear.
     *
     * @return Model The updated model.
     */
    public function clearField(Model $model, string $field): Model
    {
        return tap($model)->update([$field => null]);
    }

    /**
     * Duplicates a model (creates a copy).
     *
     * @param Model $model The model to duplicate.
     * @param array $overrides Data to override in the duplicate.
     *
     * @return Model The duplicated model.
     */
    public function duplicate(Model $model, array $overrides = []): Model
    {
        $duplicate = $model->replicate();
        $duplicate->fill($overrides);
        $duplicate->save();

        return $duplicate;
    }

    /**
     * Sets a random value for a field (e.g., token).
     *
     * @param Model $model The model instance.
     * @param string $field The field to set.
     * @param int $length The length of the random string.
     *
     * @return Model The updated model.
     */
    public function setRandomValue(Model $model, string $field, int $length = 32): Model
    {
        return tap($model)->update([$field => Str::random($length)]);
    }

    /**
     * Hashes a field value.
     *
     * @param Model $model The model instance.
     * @param string $field The field to hash.
     *
     * @return Model The updated model.
     */
    public function hashField(Model $model, string $field): Model
    {
        $value = $model->$field;

        if ($value) {
            return tap($model)->update([
                $field => Hash::make($value)
            ]);
        }

        return $model;
    }

    /**
     * Hashes a field
     *
     * @param Model $model The model instance.
     * @param string $field The field to hash.
     * @param string $value The value to need add
     *
     * @return Model
     */
    public function hasMake(Model $model, string $field, string $value): Model
    {
        if ($value) {
            return tap($model)->update([
                $field => Hash::make($value)
            ]);
        }

        return $model;
    }

    /**
     * Decrypts a field value.
     *
     * @param Model $model The model instance.
     * @param string $field The field to encrypt.
     *
     * @return Model The updated model.
     */
    public function descryptField(Model $model, string $field): Model
    {
        $value = $model->$field;
        if ($value) {
            return tap($model)->update([
                $field => Crypt::decryptString($value)
            ]);
        }

        return $model;
    }

    /**
     * Encrypts a field value.
     *
     * @param Model $model The model instance.
     * @param string $field The field to encrypt.
     *
     * @return Model The updated model.
     */
    public function encryptField(Model $model, string $field): Model
    {
        $value = $model->$field;
        if ($value) {
            return tap($model)->update([
                $field => Crypt::encryptString($value)
            ]);
        }

        return $model;
    }

    /**
     * Sets a UUID for a field.
     *
     * @param Model $model The model instance.
     * @param string $field The field to set UUID.
     *
     * @return Model The updated model.
     */
    public function setUuid(Model $model, string $field): Model
    {
        return tap($model)->update([$field => Str::uuid()]);
    }

    /**
     * Touches a model's timestamps without changes.
     *
     * @param Model $model The model instance.
     *
     * @return Model The touched model.
     */
    public function touch(Model $model): Model
    {
        return tap($model)->touch();
    }

    /**
     * Pushes a value to a JSON array field.
     *
     * @param Model $model The model instance.
     * @param string $field The JSON array field.
     * @param mixed $value The value to push.
     *
     * @return Model The updated model.
     */
    public function pushToJsonArray(Model $model, string $field, mixed $value): Model
    {
        DB::table($model->getTable())->where('id', $model->id)->update([
            $field => DB::raw("JSON_ARRAY_APPEND($field, '$', " . json_encode($value) . ")")
        ]);

        $model->refresh();

        return $model;
    }

    /**
     * Pops a value from a JSON array field.
     *
     * @param Model $model The model instance.
     * @param string $field The JSON array field.
     * @param int $index The index to pop (default last).
     *
     * @return Model The updated model.
     */
    public function popFromJsonArray(Model $model, string $field, int $index = -1): Model
    {
        DB::table($model->getTable())->where('id', $model->id)->update([
            $field => DB::raw("JSON_REMOVE($field, '$[$index]')")
        ]);

        $model->refresh();

        return $model;
    }

    /**
     * Change approve field
     *
     * @param Model $data
     * @param OnceDTO $dto

     * @return Model
     */
    public function changeIsApprove(Model $data, OnceDTO $dto): Model
    {
        return tap($data)->update([
            'updated_by'   => $dto->user->id,
            'is_approved'  => !$data->is_approved,
        ]);
    }

    /**
     * Insert by chunk
     *
     * @param array $chunk
     * @param int $length = 1000
     *
     * @return void
     */
    public function insertChunk(array $insertData, int $length = 1000) {
        foreach (array_chunk($insertData, $length) as $chunk) {
            $this->query()->insert($chunk);
        }
    }

    /**
     * Change is_owner field
     *
     * @param Model $data
     * @param boolean $isOwner
     *
     * @return Model
     */
    public function changeIsOwner(Model $data, $isOwner = false): Model {
        return tap($data)->update([
            'is_owner' => $isOwner,
        ]);
    }

    /**
     * Generic setter: update model field only if value differs (avoid unnecessary touches).
     *
     * @param Model $model
     * @param string $field
     * @param mixed $value
     * @return Model
     */
    public function setIfChanged(Model $model, string $field, mixed $value): Model
    {
        if ($model->getAttribute($field) === $value) {
            return $model;
        }

        return tap($model)->update([$field => $value]);
    }

    /**
     * Generic: set a flag to true (idempotent).
     *
     * @param Model $model
     * @param string $field
     *
     * @return Model
     */
    public function setFlagTrue(Model $model, string $field): Model
    {
        // Only update if needed to avoid touching timestamps unnecessarily
        if (! (bool) data_get($model, $field)) {
            return tap($model)->update([$field => true]);
        }

        return $model;
    }

    /**
     * Change flag is_viewed to true
     *
     * @param Model $data
     *
     * @return Model
     */
    public function setIsViewed(Model $data): Model
    {
        return tap($data)->update([
            'is_viewed' => true,
        ]);
    }

    /**
     * Change flag is_published
     *
     * @param Model $data
     *
     * @return Model
     */
    public function changeIsPublished(Model $data): Model
    {
        return tap($data)->update([
            'is_published' => !$data->is_published,
        ]);
    }

    /**
     * Set read_at to now timestamp
     *
     * @param Model $dta
     *
     * @return Model
     */
    public function read(Model $data): Model
    {
        return tap($data)->update([
            'read_at' => Carbon::now()
        ]);
    }

    /**
     * Bulk insert rows for a single field.
     *
     * @param array $values      Values to insert into $fieldName
     * @param string $fieldName  DB column name
     * @param array $extraData   Additional fields for each row
     * @param bool $timestamps   Whether to add created_at / updated_at
     *
     * @return void
     */
    public function insertManyByField(
        array $values,
        string $fieldName,
        array $extraData = [],
        bool $timestamps = true
    ) {
        $now = Carbon::now();

        $ts = $timestamps
            ? ['created_at' => $now, 'updated_at' => $now]
            : [];

        $rows = [];

        foreach ($values as $value) {
            $rows[] = [
                $fieldName => $value,
            ] + $extraData + $ts;
        }

        $this->query()->insert($rows);
    }

    /**
     * Set password to model
     *
     * @param Model $data
     * @param string $password
     *
     * @return Model
     */
    public function setPassword(Model $data, string $password): Model {
        return tap($data)->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Verefied model
     *
     * @param Model $data
     *
     * @return Model
     */
    public function verified(Model $data): Model {
        return tap($data)->update([
            'is_verified' => true,
        ]);
    }
}
