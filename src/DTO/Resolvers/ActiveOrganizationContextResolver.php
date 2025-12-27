<?php

namespace Core\DTO\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;

class ActiveOrganizationContextResolver extends AbstractContextResolver
{
    public function resolve(Authenticatable $user, array $payload = []): array
    {
        $role = $user->activeRole;

        return [
            'organization_id' => $this->determineOrganizationId(
                $role?->organization_id ?? null,
                $payload
            ),
            'role_id' => $role?->role_id ?? null,
        ];
    }
}
