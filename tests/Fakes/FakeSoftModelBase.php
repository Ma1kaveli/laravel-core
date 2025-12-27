<?php

namespace Tests\Fakes;

use Core\Models\SoftModelBase;

class FakeSoftModelBase extends SoftModelBase
{
    protected $table = 'fake_soft_models';

    protected $guarded = [];

    public $timestamps = false;
}
