<?php

namespace Acacha\UsersEbreEscoolMigration\Traits;

use Acacha\UsersEbreEscoolMigration\Models\UserMigration;

/**
 * Class HasUserMigrations.
 *
 * @package Acacha\Users\Traits
 */
trait HasUserMigrations
{
    /**
     * Scope a query to only include migrated users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMigrated($query)
    {
        //TODO: Custom query to improve performance or Cache!
        return $query->has('migration');
    }

    /**
     * Get the user migration record associated with the user.
     */
    public function migration()
    {
        return $this->hasOne(UserMigration::class);
    }
}