<?php

namespace Acacha\UsersEbreEscoolMigration\Services;

use Acacha\UsersEbreEscoolMigration\Models\ProgressBatch;

/**
 * Class MigrationBatch.
 *
 * @package Acacha\UsersEbreEscoolMigration\Services
 */
class MigrationBatch
{
    /**
     * Init/create Migration batch.
     *
     * @return int
     */
    public function init()
    {
        return ProgressBatch::create([]);
    }
}