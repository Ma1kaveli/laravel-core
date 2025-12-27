<?php

namespace Tests\Unit\DTO\Resolvers;

use Core\DTO\Resolvers\BaseAwareOrganizationContextResolver;
use Tests\Fakes\FakeRole;
use Tests\Fakes\FakeUser;
use Tests\TestCase;

class BaseAwareOrganizationContextResolverTest extends TestCase
{
    /**
     * Тест: разрешение контекста с базовой ролью и organization_id в payload
     */
    public function test_resolve_with_base_role_and_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => 25, // Переопределено из payload (is_base = true)
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с базовой ролью без organization_id в payload
     */
    public function test_resolve_with_base_role_without_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, []);

        $this->assertEquals([
            'organization_id' => 10, // Используется из роли
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с небазовой ролью и organization_id в payload
     */
    public function test_resolve_with_non_base_role_and_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => false
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => 10, // Остается из роли (is_base = false)
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с небазовой ролью без organization_id в payload
     */
    public function test_resolve_with_non_base_role_without_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => false
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, []);

        $this->assertEquals([
            'organization_id' => 10, // Используется из роли
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с базовой ролью и пустым organization_id в payload
     */
    public function test_resolve_with_base_role_and_empty_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => ''
        ]);

        $this->assertEquals([
            'organization_id' => 10, // Используется из роли, т.к. payload пустой
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с базовой ролью и organization_id = 0 в payload
     */
    public function test_resolve_with_base_role_and_zero_organization_id_in_payload(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 0
        ]);

        // С текущей реализацией (!empty) 0 считается пустым
        $this->assertEquals([
            'organization_id' => 10, // Используется из роли
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста без роли
     */
    public function test_resolve_without_role(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();
        $user->role = null;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => null,
            'role_id' => null,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью без organization_id и с is_base = true
     */
    public function test_resolve_with_role_without_organization_id_and_is_base_true(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => null,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => 25, // Используется из payload (is_base = true)
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью без organization_id и с is_base = false
     */
    public function test_resolve_with_role_without_organization_id_and_is_base_false(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => null,
            'id' => 1,
            'is_base' => false
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => null, // Остается null (is_base = false)
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: строка organization_id в payload конвертируется в int
     */
    public function test_resolve_converts_string_organization_id_to_int(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
            'is_base' => true
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => '99'
        ]);

        $this->assertSame(99, $result['organization_id']);
        $this->assertIsInt($result['organization_id']);
    }

    /**
     * Тест: edge case - is_base как строка 'true' (должно работать только с true)
     */
    public function test_resolve_with_is_base_as_string_true(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        // Строка 'true' - используем stdClass для точного тестирования
        $role = new \stdClass();
        $role->organization_id = 10;
        $role->id = 1;
        $role->is_base = 'true'; // строка, не boolean true

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        // Строка 'true' не равна true, поэтому organization_id из роли
        $this->assertEquals([
            'organization_id' => 10,
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: edge case - is_base как число 1 (должно работать только с true)
     */
    public function test_resolve_with_is_base_as_integer_one(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        // Число 1 - используем stdClass для точного тестирования
        $role = new \stdClass();
        $role->organization_id = 10;
        $role->id = 1;
        $role->is_base = 1; // число, не boolean true

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        // 1 не равно true, поэтому organization_id из роли
        $this->assertEquals([
            'organization_id' => 10,
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: edge case - is_base как boolean true (должно работать)
     */
    public function test_resolve_with_is_base_as_boolean_true(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();
        $user = new FakeUser();

        // Boolean true
        $role = new \stdClass();
        $role->organization_id = 10;
        $role->id = 1;
        $role->is_base = true;

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        // Boolean true позволяет переопределить organization_id
        $this->assertEquals([
            'organization_id' => 25,
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: наследование от AbstractContextResolver
     */
    public function test_inherits_from_abstract_context_resolver(): void
    {
        $resolver = new BaseAwareOrganizationContextResolver();

        $this->assertInstanceOf(
            \Core\DTO\Resolvers\AbstractContextResolver::class,
            $resolver
        );

        $this->assertInstanceOf(
            \Core\DTO\Contracts\IContextResolverContract::class,
            $resolver
        );
    }
}
