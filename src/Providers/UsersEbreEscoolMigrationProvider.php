<?php

namespace Acacha\UsersEbreEscoolMigration\Providers;

use Acacha\UsersEbreEscoolMigration\Facades\AcachaUsersEbreEscoolMigration;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

/**
 * Class UsersEbreEscoolMigrationProvider.
 */
class UsersEbreEscoolMigrationProvider extends EventServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Acacha\UsersEbreEscoolMigration\Events\UserHasBeenMigrated::class => [
            \Acacha\UsersEbreEscoolMigration\Listeners\PersistUserMigrationInDatabase::class,
        ],
    ];

    /**
     * Register.
     */
    public function register()
    {
        if (!defined('ACACHA_EBRE_ESCOOL_MIGRATION')) {
            define('ACACHA_EBRE_ESCOOL_MIGRATION', realpath(__DIR__.'/../../'));
        }

        $this->app->bind('AcachaUsersEbreEscoolMigration', function () {
            return new \Acacha\UsersEbreEscoolMigration\AcachaUsersEbreEscoolMigration();
        });

        $this->registerEloquentFactoriesFrom(ACACHA_EBRE_ESCOOL_MIGRATION . '/database/factories');

    }

    /**
     * Boot.
     */
    public function boot()
    {
        //Parent will be responsible of registering events using $listen property
        parent::boot();

        $this->defineRoutes();
        $this->loadViews();

        $this->loadMigrations();
        $this->publishFactories();

    }

    /**
     * Define routes.
     */
    private function defineRoutes()
    {
        $this->defineWebRoutes();
        $this->defineApiRoutes();
    }

    /**
     * Define web routes
     */
    protected function defineWebRoutes()     {
        require ACACHA_EBRE_ESCOOL_MIGRATION . '/routes/web.php';
    }

    /**
     * Define api routes.
     */
    protected function defineApiRoutes()     {
        require ACACHA_EBRE_ESCOOL_MIGRATION . '/routes/api.php';
    }

    /**
     * Load views.
     */
    private function loadViews()
    {
        $this->loadViewsFrom(ACACHA_EBRE_ESCOOL_MIGRATION.'/resources/views', 'ebre-escool-migration');
    }

    /**
     * Load package migrations.
     */
    public function loadMigrations()
    {
        $this->loadMigrationsFrom(ACACHA_EBRE_ESCOOL_MIGRATION .'/database/migrations');
    }

    /**
     * Publish factories.
     */
    private function publishFactories() {
        $this->publishes(AcachaUsersEbreEscoolMigration::factories(), 'acacha_users_ebre_escool_migration_factories');
    }

    /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path)
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }

}