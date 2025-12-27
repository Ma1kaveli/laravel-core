<?php

namespace Tests;

use Core\Providers\CoreServiceProvider;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Подключаем провайдер пакета
     */
    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
        ];
    }

    /**
     * Конфигурация окружения тестов
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $databasePath = __DIR__ . '/temp/database.sqlite';

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }

        config()->set('database.default', 'sqlite');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
        ]);
    }

    /**
     * Глобальный setUp
     * - накатывает миграции
     * - вызывает хуки для наследников
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->runTestMigrations();
        $this->setUpAuth();
    }

    /**
     * Миграции, нужные для тестов
     */
    protected function runTestMigrations(): void
    {
        Schema::dropAllTables();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Хук для авторизации (по умолчанию — ничего)
     * Переопределяется в конкретных тестах
     */
    protected function setUpAuth(): void
    {
        // noop
    }
}
