<?php

namespace Tests\Unit\DTO\Resolvers;

use Core\DTO\Resolvers\AbstractContextResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\TestCase;

class AbstractContextResolverTest extends TestCase
{
    /**
     * Создаем тестовый ресолвер, наследующий абстрактный класс
     */
    private function getTestResolver(): mixed
    {
        return new class extends AbstractContextResolver {
            public function resolve(Authenticatable $user, array $payload = []): array
            {
                return [];
            }

            // Делаем защищенный метод публичным для тестирования
            public function testDetermineOrganizationId(?int $defaultOrganizationId, array $payload = []): ?int
            {
                return $this->determineOrganizationId($defaultOrganizationId, $payload);
            }
        };
    }

    /**
     * Тест: определение organization_id из payload
     */
    public function test_determine_organization_id_from_payload(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(1, [
            'organization_id' => 5
        ]);

        $this->assertEquals(5, $result);
    }

    /**
     * Тест: использование default organization_id, когда payload пустой
     */
    public function test_determine_organization_id_uses_default_when_payload_empty(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(10, []);

        $this->assertEquals(10, $result);
    }

    /**
     * Тест: organization_id в payload как строка конвертируется в int
     */
    public function test_determine_organization_id_converts_string_to_int(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(1, [
            'organization_id' => '25'
        ]);

        $this->assertSame(25, $result);
        $this->assertIsInt($result);
    }

    /**
     * Тест: null в payload возвращает null
     */
    public function test_determine_organization_id_returns_null_when_payload_null(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(null, []);

        $this->assertNull($result);
    }

    /**
     * Тест: пустое значение в payload возвращает default
     */
    public function test_determine_organization_id_ignores_empty_payload_values(): void
    {
        $resolver = $this->getTestResolver();

        // Пустая строка возвращает default (т.к. !empty('') == false)
        $result1 = $resolver->testDetermineOrganizationId(10, ['organization_id' => '']);
        $this->assertEquals(10, $result1);

        // 0 возвращает default (т.к. !empty(0) == false)
        $result2 = $resolver->testDetermineOrganizationId(10, ['organization_id' => 0]);
        $this->assertEquals(10, $result2);

        // null в payload возвращает default
        $result3 = $resolver->testDetermineOrganizationId(10, ['organization_id' => null]);
        $this->assertEquals(10, $result3);

        // false в payload возвращает default
        $result4 = $resolver->testDetermineOrganizationId(10, ['organization_id' => false]);
        $this->assertEquals(10, $result4);

        // пустой массив в payload возвращает default
        $result5 = $resolver->testDetermineOrganizationId(10, ['organization_id' => []]);
        $this->assertEquals(10, $result5);
    }

    /**
     * Тест: payload с organization_id = 0 возвращает default, а не 0
     * (потому что !empty(0) == false)
     */
    public function test_determine_organization_id_with_zero_value_returns_default(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(10, [
            'organization_id' => 0
        ]);

        // С текущей реализацией (с использованием !empty) это вернет 10, а не 0
        $this->assertSame(10, $result);
    }

    /**
     * Тест: приоритет payload над default значением
     */
    public function test_determine_organization_id_priority_payload_over_default(): void
    {
        $resolver = $this->getTestResolver();

        // Payload должен переопределить default
        $result = $resolver->testDetermineOrganizationId(100, [
            'organization_id' => 200
        ]);

        $this->assertSame(200, $result);
    }

    /**
     * Тест: строка '0' также считается пустой
     */
    public function test_determine_organization_id_with_string_zero(): void
    {
        $resolver = $this->getTestResolver();

        $result = $resolver->testDetermineOrganizationId(15, [
            'organization_id' => '0'
        ]);

        // !empty('0') == false, поэтому вернет default
        $this->assertSame(15, $result);
    }
}
