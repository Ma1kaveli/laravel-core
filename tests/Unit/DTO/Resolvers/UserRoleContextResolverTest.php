<?php

namespace Tests\Unit\DTO\Resolvers;

use Core\DTO\Resolvers\UserRoleContextResolver;
use Tests\Fakes\FakeRole;
use Tests\Fakes\FakeUser;
use Tests\TestCase;

class UserRoleContextResolverTest extends TestCase
{
    /**
     * Тест: разрешение контекста с ролью и organization_id в payload
     */
    public function test_resolve_with_role_and_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => 25, // Используется из payload
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью без organization_id в payload
     */
    public function test_resolve_with_role_without_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, []);

        $this->assertEquals([
            'organization_id' => 10, // Используется из роли
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью и пустым organization_id в payload
     */
    public function test_resolve_with_role_and_empty_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => ''
        ]);

        $this->assertEquals([
            'organization_id' => 10, // Используется из роли
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью и organization_id = 0 в payload
     */
    public function test_resolve_with_role_and_zero_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
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
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();
        $user->role = null;

        $result = $resolver->resolve($user, [
            'organization_id' => 25
        ]);

        $this->assertEquals([
            'organization_id' => 25, // Используется из payload
            'role_id' => null,
        ], $result);
    }

    /**
     * Тест: разрешение контекста без роли и без organization_id в payload
     */
    public function test_resolve_without_role_and_without_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();
        $user->role = null;

        $result = $resolver->resolve($user, []);

        $this->assertEquals([
            'organization_id' => null,
            'role_id' => null,
        ], $result);
    }

    /**
     * Тест: разрешение контекста с ролью без organization_id и с organization_id в payload
     */
    public function test_resolve_with_role_without_organization_id_and_organization_id_in_payload(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => null,
            'id' => 1,
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => 30
        ]);

        $this->assertEquals([
            'organization_id' => 30, // Используется из payload
            'role_id' => 1,
        ], $result);
    }

    /**
     * Тест: строка organization_id в payload конвертируется в int
     */
    public function test_resolve_converts_string_organization_id_to_int(): void
    {
        $resolver = new UserRoleContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 10,
            'id' => 1,
        ]);

        $user->role = $role;

        $result = $resolver->resolve($user, [
            'organization_id' => '99'
        ]);

        $this->assertSame(99, $result['organization_id']);
        $this->assertIsInt($result['organization_id']);
    }

    /**
     * Тест: наследование от AbstractContextResolver
     */
    public function test_inherits_from_abstract_context_resolver(): void
    {
        $resolver = new UserRoleContextResolver();

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
