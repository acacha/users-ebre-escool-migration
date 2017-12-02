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
}