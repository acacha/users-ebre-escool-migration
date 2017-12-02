<?php

namespace Acacha\UsersEbreEscoolMigration;

/**
 * Class AcachaUsersEbreEscoolMigration
 */
class AcachaUsersEbreEscoolMigration
{
    /**
     * Factories copy path.
     *
     * @return array
     */
    public function factories()
    {
        return [
            ACACHA_EBRE_ESCOOL_MIGRATION.'/database/factories' =>
                database_path('factories')
        ];
    }

    /**
     * Config auth copy path.
     *
     * @return array
     */
    public function config()
    {
        return [
            ACACHA_USERS_PATH.'/config/users-ebre-escool-migration.php' =>
                config_path('users-ebre-escool-migration.php')
        ];
    }
}