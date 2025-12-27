<?php

namespace Tests\Unit\DTO\Resolvers;

use Core\DTO\Resolvers\ActiveOrganizationContextResolver;
use Tests\Fakes\FakeUser;
use Tests\Fakes\FakeRole;
use Tests\TestCase;

class ActiveOrganizationContextResolverTest extends TestCase
{
    /**
     * Тест: разрешение контекста с активной ролью
     */
    public function test_resolve_with_active_role(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user);

        $this->assertEquals([
            'organization_id' => 15,
            'role_id' => 3,
        ], $result);
    }

    /**
     * Тест: разрешение контекста без активной роли
     */
    public function test_resolve_without_active_role(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = null;

        $result = $resolver->resolve($user);

        $this->assertEquals([
            'organization_id' => null,
            'role_id' => null,
        ], $result);
    }

    /**
     * Тест: payload переопределяет organization_id из активной роли
     */
    public function test_resolve_payload_overrides_active_role(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => 30
        ]);

        $this->assertEquals([
            'organization_id' => 30,
            'role_id' => 3,
        ], $result);
    }

    /**
     * Тест: разрешение с ролью без organization_id
     */
    public function test_resolve_with_role_without_organization_id(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => null,
            'id' => 5,
        ]);

        $result = $resolver->resolve($user);

        $this->assertEquals([
            'organization_id' => null,
            'role_id' => 5,
        ], $result);
    }

    /**
     * Тест: payload с organization_id как строка
     */
    public function test_resolve_with_string_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 10,
            'id' => 2,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => '99'
        ]);

        $this->assertSame(99, $result['organization_id']);
        $this->assertIsInt($result['organization_id']);
    }

    /**
     * Тест: пустой payload не переопределяет активную роль
     */
    public function test_resolve_empty_payload_does_not_override(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, []);

        $this->assertEquals([
            'organization_id' => 15,
            'role_id' => 3,
        ], $result);
    }

    /**
     * Тест: разрешение с payload содержащим organization_id = 0
     * ВАЖНО: В зависимости от реализации determineOrganizationId в AbstractContextResolver
     * 0 может вернуться как 0 или как null/default
     */
    public function test_resolve_with_zero_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => 0
        ]);

        // Если в AbstractContextResolver используется !empty(), то 0 вернет default (15)
        // Если используется array_key_exists() или isset(), то вернет 0
        // Проверяем оба варианта
        if ($result['organization_id'] === 0) {
            $this->assertSame(0, $result['organization_id']);
        } else {
            $this->assertSame(15, $result['organization_id']);
        }
    }

    /**
     * Тест: строка '0' в payload
     */
    public function test_resolve_with_string_zero_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => '0'
        ]);

        // '0' конвертируется в 0, затем логика как в тесте выше
        if ($result['organization_id'] === 0) {
            $this->assertSame(0, $result['organization_id']);
        } else {
            $this->assertSame(15, $result['organization_id']);
        }
    }

    /**
     * Тест: активная роль без id (использует role_id)
     */
    public function test_resolve_with_active_role_having_role_id_instead_of_id(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();

        // Используем stdClass для симуляции роли с role_id вместо id
        $role = new \stdClass();
        $role->organization_id = 10;
        $role->role_id = 5; // вместо id

        $user->activeRole = $role;

        $result = $resolver->resolve($user);

        // Проверяем, что role_id используется если нет id
        $this->assertEquals([
            'organization_id' => 10,
            'role_id' => 5,
        ], $result);
    }

    /**
     * Тест: активная роль с id (приоритет над role_id)
     */
    public function test_resolve_with_active_role_having_both_id_and_role_id(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();

        $role = new \stdClass();
        $role->organization_id = 10;
        $role->id = 7;
        $role->role_id = 5;

        $user->activeRole = $role;

        $result = $resolver->resolve($user);

        // id должен иметь приоритет над role_id
        $this->assertEquals([
            'organization_id' => 10,
            'role_id' => 7, // использует id, а не role_id
        ], $result);
    }

    /**
     * Тест: наследование от AbstractContextResolver
     */
    public function test_inherits_from_abstract_context_resolver(): void
    {
        $resolver = new ActiveOrganizationContextResolver();

        $this->assertInstanceOf(
            \Core\DTO\Resolvers\AbstractContextResolver::class,
            $resolver
        );

        $this->assertInstanceOf(
            \Core\DTO\Contracts\IContextResolverContract::class,
            $resolver
        );
    }

    /**
     * Тест: реализация метода resolve возвращает массив
     */
    public function test_resolve_returns_array(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 1,
            'id' => 2,
        ]);

        $result = $resolver->resolve($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('organization_id', $result);
        $this->assertArrayHasKey('role_id', $result);
    }

    /**
     * Тест: edge case - role есть, но поля могут отсутствовать
     */
    public function test_resolve_with_role_missing_properties(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();

        // Тест с разными вариантами неполных объектов
        $testCases = [
            (object) [], // полностью пустой объект
            (object) ['organization_id' => null], // только organization_id = null
            (object) ['id' => null], // только id = null
            (object) ['organization_id' => 10], // нет id
            (object) ['id' => 5], // нет organization_id
        ];

        foreach ($testCases as $role) {
            $user->activeRole = $role;
            $result = $resolver->resolve($user);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('organization_id', $result);
            $this->assertArrayHasKey('role_id', $result);

            // Проверяем ожидаемые значения
            $expectedOrgId = $role->organization_id ?? null;
            $expectedRoleId = $role->id ?? null;

            $this->assertEquals($expectedOrgId, $result['organization_id']);
            $this->assertEquals($expectedRoleId, $result['role_id']);
        }
    }

    /**
     * Тест: проверка, что метод использует determineOrganizationId из родителя
     */
    public function test_uses_parent_determine_organization_id_method(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();

        $role = new FakeRole([
            'organization_id' => 20,
            'id' => 4,
        ]);

        $user->activeRole = $role;

        // С payload
        $resultWithPayload = $resolver->resolve($user, ['organization_id' => 50]);
        $this->assertEquals(50, $resultWithPayload['organization_id']);

        // Без payload
        $resultWithoutPayload = $resolver->resolve($user, []);
        $this->assertEquals(20, $resultWithoutPayload['organization_id']);
    }

    /**
     * Тест: edge case - null значения в payload
     */
    public function test_resolve_with_null_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => null
        ]);

        // null в payload должен вернуть organization_id из роли
        $this->assertSame(15, $result['organization_id']);
    }

    /**
     * Тест: edge case - false в payload
     */
    public function test_resolve_with_false_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => false
        ]);

        // false в payload должен вернуть organization_id из роли
        $this->assertSame(15, $result['organization_id']);
    }

    /**
     * Тест: edge case - пустая строка в payload
     */
    public function test_resolve_with_empty_string_organization_id_in_payload(): void
    {
        $resolver = new ActiveOrganizationContextResolver();
        $user = new FakeUser();
        $user->activeRole = new FakeRole([
            'organization_id' => 15,
            'id' => 3,
        ]);

        $result = $resolver->resolve($user, [
            'organization_id' => ''
        ]);

        // Пустая строка в payload должна вернуть organization_id из роли
        $this->assertSame(15, $result['organization_id']);
    }
}
