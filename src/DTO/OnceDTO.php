<?php

namespace Core\DTO;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class OnceDTO extends FormDTO {
    public function __construct(
        public readonly Authenticatable|null $authUser,
        Authenticatable $user,
        ?int $organizationId,
        ?int $roleId,
        ?int $id = null,
        public readonly array $params = [],
    ) {
        parent::__construct(
            user: $user,
            organizationId: $organizationId,
            roleId: $roleId,
            id: $id
        );
    }

    /**
     * Стандартное формирование DTO
     *
     * @param int $id
     * @param array $params = []
     *
     * @return OnceDTO
     */
    public static function make(int $id, array $params = []): OnceDTO
    {
        $user = Auth::user();

        $resolverClass = config('core.form_dto.context_resolver');
        $resolver = app($resolverClass);

        $context = $resolver->resolve($user, []);

        return new self(
            user: $user,
            authUser: $user,
            id: $id,
            organizationId: $context['organization_id'] ?? null,
            roleId: $context['role_id'] ?? null,
            params: $params,
        );
    }

    /**
     * appendParams
     *
     * @param array $params
     *
     * @return static
     */
    public function appendParams(array $params): static
    {
        return new self(
            user: $this->user,
            authUser: $this->authUser,
            id: $this->id,
            organizationId: $this->organizationId,
            roleId: $this->roleId,
            params: [
                ...$this->params,
                ...$params
            ]
        );
    }
}
