<?php

namespace Tests\Fakes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FakeRepositoryModel extends Model
{
    use SoftDeletes;

    protected $table = 'fake_repository_models';

    protected $guarded = [];

    public $timestamps = true;
}
