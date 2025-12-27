<?php

namespace Tests\Integration\DTO;

use Core\DTO\OnceDTO;
use Core\DTO\Resolvers\BaseAwareOrganizationContextResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Fakes\FakeUser;
use Tests\TestCase;
use Mockery;

class OnceDTOIntegrationTest extends TestCase
{
    /**
     * Тест: реальное использование OnceDTO в сервисе
     */
    public function test_real_usage_in_service(): void
    {
        // Сценарий: использование OnceDTO для операции с конкретной сущностью
        // Например, получение или обновление записи по ID

        $user = new FakeUser();
        $user->id = 1;
        $user->name = 'Test User';

        Auth::shouldReceive('user')->once()->andReturn($user);

        $resolverMock = Mockery::mock(BaseAwareOrganizationContextResolver::class);
        $resolverMock->shouldReceive('resolve')
            ->with($user, [])
            ->andReturn([
                'organization_id' => 15,
                'role_id' => 3
            ]);

        Config::set('core.form_dto.context_resolver', BaseAwareOrganizationContextResolver::class);
        $this->app->instance(BaseAwareOrganizationContextResolver::class, $resolverMock);

        // Имитация использования в сервисе:
        // $dto = OnceDTO::make($id, $additionalParams);
        // $result = $repository->getById($dto);

        $entityId = 500;
        $additionalParams = [
            'withRelations' => true,
            'onlyActive' => true
        ];

        $dto = OnceDTO::make($entityId, $additionalParams);

        // Проверяем, что DTO содержит все необходимые данные для репозитория
        $this->assertSame($user, $dto->user);
        $this->assertSame($entityId, $dto->id);
        $this->assertSame(15, $dto->organizationId);
        $this->assertSame(3, $dto->roleId);
        $this->assertTrue($dto->params['withRelations']);
        $this->assertTrue($dto->params['onlyActive']);

        // Имитация добавления дополнительных параметров в процессе работы
        $enrichedDto = $dto->appendParams([
            'locale' => 'ru',
            'timezone' => 'Europe/Moscow'
        ]);

        $this->assertTrue($enrichedDto->params['withRelations']);
        $this->assertTrue($enrichedDto->params['onlyActive']);
        $this->assertEquals('ru', $enrichedDto->params['locale']);
        $this->assertEquals('Europe/Moscow', $enrichedDto->params['timezone']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
