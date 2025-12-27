<?php

namespace Core\DTO;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

use Converter\DTO\ConverterDTO;

abstract class FormDTO {
    public function __construct(
        public readonly Authenticatable $user,
        public ?int $organizationId,
        public ?int $roleId,
        public ?int $id = null,
    ) {}

    /**
     * processBaseData
     *
     * @param Request $request
     * @param ?int $entityId
     * @param array $customFields = []
     *
     * @return array
     */
    protected static function processBaseData(
        Request $request,
        ?int $entityId,
        array $customFields = [],
    ): array {
        $user = Auth::user();

        $requestFields = array_merge(
            static::getCommonRequestFields(),
            $customFields
        );

        $convertedData = (new ConverterDTO())->getRequestData(
            $request->only($requestFields)
        );

        $resolverClass = config('core.form_dto.context_resolver');
        $resolver = app($resolverClass);

        $context = $resolver->resolve($user, $convertedData);

        return [
            'user' => $user,
            'organization_id' => $context['organization_id'] ?? null,
            'role_id' => $context['role_id'] ?? null,
            'id' => $entityId,
            'converted_data' => $convertedData,
        ];
    }

    /**
     * getCommonRequestFields
     *
     * @return array
     */
    protected static function getCommonRequestFields(): array
    {
        return config('core.form_dto.common_request_fields', []);
    }
}
