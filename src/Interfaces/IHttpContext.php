<?php

namespace Core\Interfaces;

use Illuminate\Http\Request;

interface IHttpContext
{
    public function request(): Request;
    public function route(string $key, mixed $default = null): mixed;

    public function all(): array;
}
