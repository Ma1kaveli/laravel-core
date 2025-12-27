<?php

namespace Tests\Fakes;

use Core\Requests\BaseFormRequest;
use Core\Requests\Context;

use Illuminate\Validation\Validator;

class FakeFormRequest extends BaseFormRequest
{
    public array $validatorContextCalls = [];

    protected function rulesFor(Context $context): array
    {
        return match ($context) {
            Context::CREATE => ['name' => ['required']],
            Context::UPDATE => ['name' => ['sometimes']],
            Context::DELETE => [],
        };
    }

    protected function messagesFor(Context $context): array
    {
        return match ($context) {
            Context::CREATE => ['name.required' => 'Name is required'],
            default => [],
        };
    }

    protected function withValidatorFor(Context $context, Validator $validator): void
    {
        $this->validatorContextCalls[] = $context;
    }

    public function authorize(): bool
    {
        return true;
    }
}
