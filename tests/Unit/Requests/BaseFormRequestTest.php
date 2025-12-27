<?php

namespace Tests\Unit\Requests;

use Core\Requests\Context;

use Illuminate\Support\Facades\Validator;

use Tests\Fakes\FakeFormRequest;
use Tests\TestCase;

/**
 * @covers \Core\Requests\BaseFormRequest
 *
 * Тесты базового FormRequest с поддержкой контекста.
 *
 * Контракт:
 * - контекст определяется по HTTP-методу
 * - rules(), messages(), withValidator() делегируют в контекст
 * - toDto() возвращает DTO с validated-данными
 */
class BaseFormRequestTest extends TestCase
{
    /**
     * CREATE-контекст должен использовать правила из rulesFor(Context::CREATE)
     */
    public function test_it_uses_create_rules_for_post_method(): void
    {
        $request = FakeFormRequest::create('/', 'POST', []);

        $this->assertSame(
            ['name' => ['required']],
            $request->rules()
        );
    }

    /**
     * UPDATE-контекст должен использовать правила из rulesFor(Context::UPDATE)
     */
    public function test_it_uses_update_rules_for_put_method(): void
    {
        $request = FakeFormRequest::create('/', 'PUT', []);

        $this->assertSame(
            ['name' => ['sometimes']],
            $request->rules()
        );
    }

    /**
     * DELETE-контекст должен использовать правила из rulesFor(Context::DELETE)
     */
    public function test_it_uses_delete_rules_for_delete_method(): void
    {
        $request = FakeFormRequest::create('/', 'DELETE', []);

        $this->assertSame([], $request->rules());
    }

    /**
     * messages() должны возвращаться в зависимости от контекста
     */
    public function test_it_returns_contextual_messages(): void
    {
        $request = FakeFormRequest::create('/', 'POST', []);

        $this->assertSame(
            ['name.required' => 'Name is required'],
            $request->messages()
        );
    }

    /**
     * withValidator() должен проксировать вызов в withValidatorFor()
     * с корректным контекстом
     */
    public function test_it_calls_with_validator_for_context(): void
    {
        $request = FakeFormRequest::create('/', 'POST', []);

        $validator = Validator::make([], []);
        $request->withValidator($validator);

        $this->assertSame(
            [Context::CREATE],
            $request->validatorContextCalls
        );
    }

    /**
     * Неподдерживаемый HTTP-метод должен выбрасывать исключение
     */
    public function test_it_throws_exception_for_unsupported_method(): void
    {
        $this->expectException(\RuntimeException::class);

        FakeFormRequest::create('/', 'OPTIONS')->rules();
    }
}
