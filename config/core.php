<?php

return [
    'log' => \Illuminate\Support\Facades\Log::class,

    'form_dto' => [
        'common_request_fields' => ['organizationId'],

        'context_resolver' => \Core\DTO\Resolvers\BaseAwareOrganizationContextResolver::class,
    ],

    'repository' => [
        'is_root_field' => 'is_superadministrator',
        
        'user_id_field' => 'id'
    ],

    'soft-model-base' => [
        /*
        |--------------------------------------------------------------------------
        | User model path
        |--------------------------------------------------------------------------
        */
        'user_model' => \Illuminate\Database\Eloquent\Model::class,

        /*
        |--------------------------------------------------------------------------
        | Model's created by key
        |--------------------------------------------------------------------------
        */
        'created_by_key' => 'created_by',

        /*
        |--------------------------------------------------------------------------
        | Model's updated by key
        |--------------------------------------------------------------------------
        */
        'updated_by_key' => 'updated_by',

        /*
        |--------------------------------------------------------------------------
        | Model's deleted by key
        |--------------------------------------------------------------------------
        */
        'deleted_by_key' => 'deleted_by',
    ]
];
