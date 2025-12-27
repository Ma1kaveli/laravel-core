<?php

namespace Core\DTO\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface IContextResolverContract
{
    public function resolve(
        Authenticatable $user,
        array $payload = []
    ): array;
}
