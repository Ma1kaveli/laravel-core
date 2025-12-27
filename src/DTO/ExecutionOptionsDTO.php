<?php

namespace Core\DTO;

use Illuminate\Support\Facades\Auth;

class ExecutionOptionsDTO extends FormDTO {
    public function __construct(
        public readonly bool $getFunc = false,
        public readonly bool $withTransaction = true,
        public readonly bool $withValidation = true,
        public readonly bool $writeErrorLog = true
    ) {
        $user = Auth::user();

        $resolverClass = config('core.form_dto.context_resolver');
        $resolver = app($resolverClass);

        $context = $resolver->resolve($user, []);

        parent::__construct(
            user: $user,
            organizationId: $context['organization_id'] ?? null,
            roleId: $context['role_id'] ?? null,
            id: null
        );
    }

    public static function make(): static
    {
        return new static();
    }

    /**
     * Summary of appendGetFunc
     *
     * @return static
     */
    public function appendGetFunc(): static
    {
        return new self(
            withTransaction: $this->withTransaction,
            withValidation: $this->withValidation,
            writeErrorLog: $this->writeErrorLog,
            getFunc: true,
        );
    }

    /**
     * Summary of withoutTransaction
     *
     * @return static
     */
    public function withoutTransaction(): static
    {
        return new self(
            getFunc: $this->getFunc,
            withValidation: $this->withValidation,
            writeErrorLog: $this->writeErrorLog,
            withTransaction: false,
        );
    }

    /**
     * Summary of withoutValidation
     *
     * @return static
     */
    public function withoutValidation(): static
    {
        return new self(
            getFunc: $this->getFunc,
            withTransaction: $this->withTransaction,
            writeErrorLog: $this->writeErrorLog,
            withValidation: false,
        );
    }

    /**
     * Summary of withoutErrorLog
     *
     * @return static
     */
    public function withoutErrorLog(): static
    {
        return new self(
            getFunc: $this->getFunc,
            withTransaction: $this->withTransaction,
            withValidation: $this->withValidation,
            writeErrorLog: false,
        );
    }
}

