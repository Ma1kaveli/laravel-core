<?php

namespace Core\DTO;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class DynamicDTO extends FormDTO {
    /**
     * Summary of keys
     *
     * @var array<string>
     */
    public readonly array $keys;

    public function __construct(
        array $data = [],
        Authenticatable $user,
        ?int $organizationId,
        ?int $roleId,
        ?int $id = null,
    ) {
        $keys = [];
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
            $keys[] = $key;
        }

        $this->keys = $keys;

        parent::__construct(
            $user,
            $organizationId,
            $roleId,
            $id
        );
    }

    /**
     * Summary of make
     *
     * @param array $data
     *
     * @return static
     */
    public static function make(array $data = [], ?int $id = null): static {
        $user = Auth::user();

        $resolverClass = config('core.form_dto.context_resolver');
        $resolver = app($resolverClass);

        $context = $resolver->resolve($user, []);

        return new static(
            data: $data,
            user: $user,
            organizationId: $context['organization_id'] ?? null,
            roleId: $context['role_id'] ?? null,
            id: $id
        );
    }
}
