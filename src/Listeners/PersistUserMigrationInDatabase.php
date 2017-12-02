<?php

namespace Acacha\UsersEbreEscoolMigration\Listeners;

use Acacha\UsersEbreEscoolMigration\Events\UserHasBeenMigrated;
use Acacha\UsersEbreEscoolMigration\Events\UserMigrationHasBeenPersisted;
use Acacha\UsersEbreEscoolMigration\Models\UserMigration;

/**
 * Class PersistUserMigrationInDatabase.
 *
 */
class PersistUserMigrationInDatabase
{
    /**
     * PersistUserMigrationInDatabase constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserHasBeenMigrated  $event
     * @return void
     */
    public function handle(UserHasBeenMigrated $event)
    {
        try {
            $newUserMigration = UserMigration::create([
                'source_user_id' => $event->sourceUserId,
                'source_user' => $event->sourceUser,
                'user_id' => $event->newUser ? $event->newUser->id : null,
                'user_migration_batch_id' => $event->user_migration_batch_id
            ]);
            event(new UserMigrationHasBeenPersisted($newUserMigration));
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

    }

}