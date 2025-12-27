<?php

namespace Core\Http;

use Core\Interfaces\IHttpContext;

use Illuminate\Http\Request;

class LaravelHttpContext implements IHttpContext
{
    public function request(): Request
    {
        return request();
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return request()->route($key, $default);
    }

    public function all(): array
    {
        return request()->all();
    }
}
