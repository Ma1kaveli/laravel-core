<?php

namespace Core\DTO\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;

class BaseAwareOrganizationContextResolver extends AbstractContextResolver
{
    public function resolve(Authenticatable $user, array $payload = []): array
    {
        $role = $user->role;

        $organizationId = $role?->organization_id;

        if (
            $role?->is_base === true
            && !empty($payload['organization_id'])
        ) {
            $organizationId = (int) $payload['organization_id'];
        }

        return [
            'organization_id' => $organizationId,
            'role_id' => $role?->id,
        ];
    }
}

