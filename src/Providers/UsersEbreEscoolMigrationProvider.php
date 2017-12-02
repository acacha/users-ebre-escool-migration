<?php

namespace Acacha\UsersEbreEscoolMigration\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class UsersEbreEscoolMigrationProvider.
 */
class UsersEbreEscoolMigrationProvider extends ServiceProvider
{

    /**
     * Register.
     */
    public function register()
    {
        if (!defined('ACACHA_EBRE_ESCOOL_MIGRATION')) {
            define('ACACHA_EBRE_ESCOOL_MIGRATION', realpath(__DIR__.'/../../'));
        }

    }

    /**
     * Boot.
     */
    public function boot()
    {
        $this->defineRoutes();
        $this->loadViews();

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

}