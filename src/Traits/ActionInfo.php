<?php

namespace Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ActionInfo
{
    /**
     * Who create, who add last update, who delete
     *
     * @param  mixed $class
     * @return mixed
     */
    public static function actionInfo($class)
    {
        $class->creator;
        $class->updator;
        $class->deletor;

        return $class;
    }

    /**
     * creator
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            config('core.soft-model-base.user_model'),
            config('core.soft-model-base.created_by_key'),
            'id'
        );
    }

    /**
     * updator
     *
     * @return BelongsTo
     */
    public function updator(): BelongsTo
    {
        return $this->belongsTo(
            config('core.soft-model-base.user_model'),
            config('core.soft-model-base.updated_by_key'),
            'id'
        );
    }

    /**
     * deletor
     *
     * @return BelongsTo
     */
    public function deletor(): BelongsTo
    {
        return $this->belongsTo(
            config('core.soft-model-base.user_model'),
            config('core.soft-model-base.deleted_by_key'),
            'id'
        );
    }
}
