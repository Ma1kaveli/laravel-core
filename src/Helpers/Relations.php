<?php

namespace Core\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Relations
{
    /**
     * Переместить первый элемент отношения-коллекции в новое одиночное отношение.
     *
     * @param Model  $model
     * @param string $fromRelation  Например: 'participants'
     * @param string|null $toRelation Если не указан, будет singular от fromRelation: 'participant'
     *
     * @return Model
     */
    public static function moveFirstRelationItem(
        Model $model,
        string $fromRelation,
        ?string $toRelation = null
    ): Model {
        if ($fromRelation === '') {
            return $model;
        }

        if ($model->relationLoaded($fromRelation)) {
            $loaded = $model->getRelation($fromRelation);

            $first = is_iterable($loaded) ? collect($loaded)->first() : null;

            $toRelation = $toRelation ?? Str::singular($fromRelation);

            $model->setRelation($toRelation, $first);

            $model->unsetRelation($fromRelation);
        }

        return $model;
    }

    /**
     * По каждому элементу пагинации переместить первый элемент отношения-коллекции в новое одиночное отношение.
     *
     * @param LengthAwarePaginator  $model
     * @param string $fromRelation  Например: 'participants'
     * @param string|null $toRelation Если не указан, будет singular от fromRelation: 'participant'
     *
     * @return LengthAwarePaginator
     */
    public static function moveFirstRelationItemInPaginator(
        LengthAwarePaginator $data,
        string $fromRelation,
        ?string $toRelation = null
    ): LengthAwarePaginator {
        $data->setCollection(
            $data->getCollection()->transform(
                function ($item) use ($fromRelation, $toRelation) {
                    if (!$item instanceof Model) {
                        return $item;
                    }

                    return self::moveFirstRelationItem(
                        $item,
                        $fromRelation,
                        $toRelation
                    );
                }
            )
        );

        return $data;
    }
}
