<?php

namespace Tests\Feature;

use Acacha\Users\Jobs\MigrateUsers;
use Acacha\Users\Models\ProgressBatch;
use Acacha\Users\Models\UserMigration;
use Acacha\Users\Services\MigrationBatch;
use Acacha\Users\Services\UserMigrations;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Queue;
use Scool\EbreEscoolModel\User;
use Tests\Traits\AssertResponseValidations;
use Tests\Traits\ChecksAuthorizations;
use Tests\Traits\InteractsWithUsers;
use Tests\TestCase;
use Tests\Traits\MigrationDatabaseStatistics;
use App\User as UserOnDestination;

/**
 * Class UsersMigrationTest.
 *
 * @package Tests\Feature
 */
class UsersMigrationTest extends TestCase
{
    use DatabaseMigrations,
        InteractsWithUsers,
        MigrationDatabaseStatistics,
        ChecksAuthorizations,
        AssertResponseValidations;

    /**
     * Users table exists on migration database
     *
     * @test
     * @return void
     */
    public function users_table_exists_on_migration_database()
    {
        $totalUsers = $this->totalNumberOfUsers();

        $this->assertTrue($totalUsers > 1000);
    }

    // TODO Define and array of urls to check authorization an refactor using PHPunit datasets

    /**
     * Check authorization for users migration.
     *
     * @test
     */
    public function check_authorization_for_users_migration() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/management/users-migration',
            [$this, 'signInAsUserManager']);
    }

    /**
     * Check authorization for totalNumberOfUsers.
     *
     * @test
     */
    public function check_authorization_for_totalNumbersOfUsers_url() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/totalNumberOfUsers',
            [$this, 'signInAsUserManager']);
    }

    /**
     * Api returns totalNumberOfUsers on source database.
     *
     * @test
     */
    public function api_returns_total_number_of_users_on_source_database() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');
        $response = $this->json('GET', '/api/v1/management/users-migration/totalNumberOfUsers');
        $response->assertStatus(200)
                    ->assertJson([
                        "data" => User::all()->count()
                    ]);
    }

    /**
     * Check authorization for totalNumberOfMigratedUsers.
     *
     * @group prova
     * @test
     */
    public function check_authorization_for_totalNumberOfMigratedUsers_url() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/totalNumberOfMigratedUsers',
            [$this, 'signInAsUserManager']);
    }

    /**
     * Api returns totalNumberOfUsers on source database.
     *
     * @group working
     * @test
     */
    public function api_returns_total_number_of_migrated_users_on_source_database() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');
        $response = $this->json('GET', '/api/v1/management/users-migration/totalNumberOfMigratedUsers');
        $response->assertStatus(200)
            ->assertJson([
                "data" => 0
            ]);

        $this->migrateUsers(3);
        $response = $this->json('GET', '/api/v1/management/users-migration/totalNumberOfMigratedUsers');
        $response->assertStatus(200)
            ->assertJson([
                "data" => 3
            ]);
    }

    /**
     * Migrate some random users.
     *
     * @param $number
     */
    private function migrateUsers($number)
    {
        $service = new UserMigrations();
        $batchService = new MigrationBatch();
        $usersToMigrate = $this->obtainRandomUsersToMigrate($number);
        $batch = $batchService->init();
        $service->migrateUsers($usersToMigrate, $batch);
    }

    /**
     * Obtain users to migrate.
     *
     * @param $number
     * @return static
     */
    private function obtainRandomUsersToMigrate($number) {
        return User::all()->shuffle()->take($number);
    }

    /**
     * Check authorization for totalNumberOfUsers on destination.
     *
     * @test
     */
    public function check_authorization_for_totalNumbersOfUsers_onDestination_url() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/totalNumberOfUsers/destination',
            [$this, 'signInAsUserManager']);
    }

    /**
     * Api returns totalNumberOfUsers on destination database.
     *
     *
     * @test
     */
    public function api_returns_total_number_of_users_on_destination_database() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');
        $response = $this->json('GET', '/api/v1/management/users-migration/totalNumberOfUsers/destination');
        $response->assertStatus(200)
            ->assertJson([
                "data" => UserOnDestination::all()->count()
            ]);
    }

    /**
     * Check authorization for totalNumberOfMigratedUsers on destination.
     *
     * @test
     */
    public function check_authorization_for_totalNumbersOfMigratedUsers_onDestination_url() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/totalNumbersOfMigratedUsers/destination',
            [$this, 'signInAsUserManager']);
    }

    /**
     * Api returns totalNumberOfMigratedUsers on destination database.
     *
     * @test
     */
    public function api_returns_total_number_of_migrated_users_on_destination_database() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');
        $response = $this->json('GET',
            '/api/v1/management/users-migration/totalNumbersOfMigratedUsers/destination');
        $response->assertStatus(200)
            ->assertJson([
                "data" => UserOnDestination::migrated()->count()
            ]);
    }

    /**
     * Check authorization for massive migration url.
     *
     * @group todo
     * @test
     */
    public function check_authorization_for_massive_migration_url() {
        initialize_users_management_permissions();
        //TODO -> execute migration only for one user or create dry option that does not execute any migration?
//        $this->checkAuthorization(
//                '/api/v1/management/users-migration/migrate',
//                [$this, 'signInAsUserManager'],
//                'POST');
    }

    /**
     * Api starts massive migration ok.
     *
     * @test
     */
    public function api_starts_massive_migration_ok() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');

        Queue::fake();

        $response = $this->json('POST', '/api/v1/management/users-migration/migrate');

        $batch = json_decode($response->content());
        $response
            ->assertStatus(200)
            ->assertJson([
                'state' => 'pending'
            ]);
        $this->assertDatabaseHas('progress_batches', [
                'id' => $batch->id,
                'state' => 'pending',
                'created_at' => $batch->created_at,
                'updated_at' => $batch->updated_at,
            ]);

        Queue::assertPushed(MigrateUsers::class, function ($job) use ($batch) {
            return $job->batch->id === $batch->id;
        });

    }

    /**
     * Api massive user migration works ok.
     *
     * @group todo
     * TODO add @  test
     */
    public function api_massive_user_migration_works_ok_by_default() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');

        $response = $this->json('POST', '/api/v1/management/users-migration/migrate');

        $response->dump();
        $response
            ->assertStatus(200)
            ->assertJson([
                'done' => true,
            ]);

//        foreach ($usersToMigrate as $user) {
//            $this->assertDatabaseHas('users', [
//                'email' => $user['email'],
//                'name' => $user['name']
//            ]);
//        }

    }

    /**
     * Api massive user migration works ok.
     *
     * @group todo
     * TODO add @  test
     */
    public function api_massive_user_migration_works_ok() {
        $this->signInAsUserManager();

        //TODO
        $usersToMigrate = $this->fakeUsersToMigrate();

        $response = $this->json('POST', '/api/v1/management/users-migration/migrate', $usersToMigrate);

        $response
            ->assertStatus(200)
            ->assertJson([
                'done' => true,
            ]);

        foreach ($usersToMigrate as $user) {
            $this->assertDatabaseHas('users', [
                'email' => $user['email'],
                'name' => $user['name']
            ]);
        }

    }

    /**
     * Api check stop migration validation.
     *
     * @test
     */
    public function check_stop_migration_validations() {
        initialize_users_management_permissions();
        $this->signInAsUserManager('api');

        $response = $this->json(
            'POST',
            '/api/v1/management/users-migration/migrate-stop'
        );

        $response->assertStatus(422);
        $this->assertResponseValidation($response,422,'batch_id','The batch id field is required.');

    }

    /**
     * Api check stop migration validation.
     *
     * @test
     */
    public function check_stop_migration() {
        initialize_users_management_permissions();

        $this->signInAsUserManager('api');

        $batch = ProgressBatch::create([]);
        $response = $this->json(
            'POST',
            '/api/v1/management/users-migration/migrate-stop',
            ['batch_id' => $batch->id]
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'stopped' => true,
            ]);
    }

    /**
     * Check authorization for stop migration.
     *
     * @test
     */
    public function check_authorization_for_stop_migration() {
        initialize_users_management_permissions();
        $batch = ProgressBatch::create([]);

        $this->checkAuthorization(
            '/api/v1/management/users-migration/migrate-stop',
            [$this, 'signInAsUserManager'],
            'POST',
            ['batch_id' => $batch->id]);
    }

    /**
     * Check authorization for stop migration.
     *
     * @test
     */
    public function check_authorization_for_resume_migration() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/migrate-resume',
            [$this, 'signInAsUserManager'],
            'GET');
    }

    /**
     * Check authorization for check source database connection.
     *
     * @test
     */
    public function check_authorization_for_check_source_connection() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/checkConnection',
            [$this, 'signInAsUserManager'],
            'GET');
    }

    /**
     * Check authorization for getting user migration history.
     *
     * @test
     */
    public function check_authorization_for_getting_user_migration_history() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/history',
            [$this, 'signInAsUserManager'],
            'GET');
    }

    /**
     * Api gets user migration history ok.
     *
     * @test
     */
    public function api_gets_user_migration_history_ok() {
        initialize_users_management_permissions();

        //create user migrations
        $userMigration = $this->createUserMigrations();
        $this->signInAsUserManager('api')
            ->json('GET', '/api/v1/management/users-migration/history')
            ->assertStatus(200)
            ->assertJson([
                'current_page' => 1,
                'data' => [
                    ['id'=> $userMigration->id,
                        'source_user_id' =>  $userMigration->source_user_id,
                        'source_user' => $userMigration->source_user,
                        'created_at' => $userMigration->created_at->toDateTimeString(),
                        'updated_at' => $userMigration->updated_at->toDateTimeString()
                    ]
                ],
                'from' => 1,
                'last_page' => 1,
                'next_page_url' => null,
                'per_page' => 15,
                'prev_page_url' => null,
                'to' => 1,
                'total' => 1
            ]);
    }

    /**
     * Check authorization for getting user migration batch history.
     *
     * @test
     */
    public function check_authorization_for_getting_user_migration_batch_history() {
        initialize_users_management_permissions();
        $this->checkAuthorization(
            '/api/v1/management/users-migration/batch_history',
            [$this, 'signInAsUserManager'],
            'GET');
    }

    /**
     * Api gets user migration batch history ok.
     *
     * @test
     */
    public function api_gets_user_migration_batch_history_ok() {
        initialize_users_management_permissions();

        //create batches user migrations
        $batches = $this->createBatchUserMigrations(3);

        $this->signInAsUserManager('api')
            ->json('GET', '/api/v1/management/users-migration/batch_history')
            ->assertStatus(200)
            ->assertJson([
                    ['id'=> $batches[0]->id,
                    'accomplished' => $batches[0]->accomplished,
                    'incidences' => $batches[0]->incidences,
                    'state' => $batches[0]->state,
                    'type' => 'Acacha\\Users\\Models\\UserMigration',
                    'created_at' => $batches[0]->created_at->toDateTimeString(),
                    'updated_at' => $batches[0]->updated_at->toDateTimeString()
                    ],
                    ['id'=> $batches[1]->id,
                    'accomplished' => $batches[1]->accomplished,
                    'incidences' => $batches[1]->incidences,
                    'state' => $batches[1]->state,
                    'type' => 'Acacha\\Users\\Models\\UserMigration',
                    'created_at' => $batches[1]->created_at->toDateTimeString(),
                    'updated_at' => $batches[1]->updated_at->toDateTimeString()
                    ],
                    ['id'=> $batches[2]->id,
                    'accomplished' => $batches[2]->accomplished,
                    'incidences' => $batches[2]->incidences,
                    'state' => $batches[2]->state,
                    'type' => 'Acacha\\Users\\Models\\UserMigration',
                    'created_at' => $batches[2]->created_at->toDateTimeString(),
                    'updated_at' => $batches[2]->updated_at->toDateTimeString()
                    ],
                ]);
    }

    /**
     * Create batch user migrations.
     *
     * @param null $num
     * @return mixed
     */
    protected function createBatchUserMigrations($num = null)
    {
        return factory(ProgressBatch::class,$num)->create([
            'type' => UserMigration::class
        ]);

    }

    /**
     * Create user migrations.
     *
     * @param null $num
     * @return mixed
     */
    private function createUserMigrations($num = null) {
        return factory(UserMigration::class,$num)->create();
    }

}
