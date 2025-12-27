<?php

namespace Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

enum Context: string {
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
}

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Вернуть правила для конкретного контекста
     */
    abstract protected function rulesFor(Context $context): array;

    /**
     * Кастомные сообщения для конкретного контекста
     */
    protected function messagesFor(Context $context): array
    { return []; }

    /**
     * Дополнительные манипуляции с валидатором
     */
    protected function withValidatorFor(Context $context, Validator $validator): void { }

    public function rules(): array
    {
        return $this->rulesFor($this->getContext());
    }

    public function messages(): array
    {
        return $this->messagesFor($this->getContext());
    }

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorFor($this->getContext(), $validator);
    }

    /**
     * Определение контекста (по HTTP-методу)
     */
    protected function getContext(): Context
    {
        return match (true) {
            $this->isMethod('POST') => Context::CREATE,
            $this->isMethod('PUT'), $this->isMethod('PATCH') => Context::UPDATE,
            $this->isMethod('DELETE') => Context::DELETE,
            default => throw new \RuntimeException("Unsupported method {$this->method()}"),
        };
    }

    /**
     * Удобный метод для приведения данных к DTO
     */
    public function toDto(string $dtoClass): object
    {
        return new $dtoClass($this->validated());
    }
}
