<?php

namespace Core\Tests\Fakes;

use Core\Traits\ActionInfo;

use Illuminate\Database\Eloquent\Model;

class FakeActionModel extends Model
{
    use ActionInfo;

    protected $table = 'fake_action_models';
    public $timestamps = false; // для простоты

    protected $fillable = ['created_by', 'updated_by', 'deleted_by'];
}
