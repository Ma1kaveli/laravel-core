<?php

namespace Tests\Fakes;

use Core\DTO\FormDTO;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class FakeFormDTO extends FormDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?int $customField = null,

        Authenticatable $user,
        ?int $organizationId = null,
        ?int $roleId = null,
        ?int $id = null,
    ) {
        parent::__construct($user, $organizationId, $roleId, $id);
    }

    /**
     * Пример использования processBaseData как в реальном коде
     */
    public static function fromRequest(Request $request, ?int $id = null): self
    {
        $baseData = self::processBaseData(
            $request,
            $id,
            ['name', 'email', 'customField'] // кастомные поля
        );

        return new self(
            name: $baseData['converted_data']['name'] ?? null,
            email: $baseData['converted_data']['email'] ?? null,
            customField: $baseData['converted_data']['custom_field'] ?? null,

            user: $baseData['user'],
            organizationId: $baseData['organization_id'],
            roleId: $baseData['role_id'],
            id: $baseData['id'],
        );
    }

    /**
     * Альтернативный фабричный метод как в примере
     */
    public static function createDirect(
        Authenticatable $user,
        ?string $name = null,
        ?int $organizationId = null,
        ?int $roleId = null
    ): self {
        return new self(
            name: $name,
            email: null,
            customField: null,

            user: $user,
            organizationId: $organizationId,
            roleId: $roleId,
            id: null,
        );
    }
}
