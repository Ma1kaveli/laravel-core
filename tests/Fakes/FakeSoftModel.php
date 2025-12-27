<?php

namespace Tests\Fakes;

use Core\Traits\SoftModel;
use Illuminate\Database\Eloquent\Model;

class FakeSoftModel extends Model
{
    use SoftModel;

    protected $table = 'fake_soft_models';
    public $timestamps = false;
    protected $guarded = [];
}
