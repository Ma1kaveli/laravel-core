<?php

namespace Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class FakeUser extends Model implements Authenticatable
{
    protected $fillable = ['id', 'name'];
    public $timestamps = false;

    // Загруженные отношения — без type hint!
    protected $relations = [];

    public function setRelation($key, $value)
    {
        $this->relations[$key] = $value;
        return $this;
    }

    public function getRelation($key)
    {
        return $this->relations[$key] ?? null;
    }

    public function relationLoaded($key)
    {
        return array_key_exists($key, $this->relations);
    }

    public function unsetRelation($key)
    {
        unset($this->relations[$key]);
        return $this;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return null;
    }
}
