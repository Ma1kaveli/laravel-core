<?php

namespace Core\Models;

use Core\Traits\ActionInfo;
use Core\Traits\SoftModel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

abstract class SoftModelBase extends Model
{
    use SoftModel, SoftDeletes, ActionInfo;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($model) {
            $model->deleted_by = Auth::user()->id;
            $model->deleted_at = Carbon::now();
            $model->save();
        });
        static::restored(function ($model) {
            $model->updated_by = Auth::user()->id;
            $model->deleted_by = null;
            $model->deleted_at = null;
            $model->save();
        });
    }
}
