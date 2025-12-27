<?php

namespace Tests\Fakes;

use Illuminate\Database\Eloquent\Model;

class FakeRole extends Model
{
    protected $fillable = ['organization_id', 'role_id', 'id', 'is_base'];
    public $timestamps = false;

    public $is_base = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (array_key_exists('organization_id', $attributes)) {
            $this->organization_id = $attributes['organization_id'];
        }

        if (array_key_exists('role_id', $attributes)) {
            $this->role_id = $attributes['role_id'];
        }

        if (array_key_exists('id', $attributes)) {
            $this->id = $attributes['id'];
        }

        if (array_key_exists('is_base', $attributes)) {
            $this->is_base = $attributes['is_base'];
        }
    }
}
