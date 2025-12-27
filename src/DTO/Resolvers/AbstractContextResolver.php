<?php

namespace Core\DTO\Resolvers;

use Core\DTO\Contracts\IContextResolverContract;

abstract class AbstractContextResolver implements IContextResolverContract
{
    protected function determineOrganizationId(
        ?int $defaultOrganizationId,
        array $payload = []
    ): ?int {
        return !empty($payload['organization_id'])
            ? (int) $payload['organization_id']
            : $defaultOrganizationId;
    }
}
