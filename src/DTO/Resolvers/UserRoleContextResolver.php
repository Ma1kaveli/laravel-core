<?php

namespace Core\DTO\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;

class UserRoleContextResolver extends AbstractContextResolver
{
    public function resolve(Authenticatable $user, array $payload = []): array
    {
        $role = $user->role;

        return [
            'organization_id' => $this->determineOrganizationId(
                $role?->organization_id,
                $payload
            ),
            'role_id' => $role?->id,
        ];
    }
}

