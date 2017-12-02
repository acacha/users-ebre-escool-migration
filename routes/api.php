<?php

Route::group(['namespace' => "Acacha\UsersEbreEscoolMigration\Http\Controllers",
    'prefix' => 'api/v1', 'middleware' => ['api','throttle','bindings']], function () {

    //HERE API PUBLIC ROUTES

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('/management/users-migration/totalNumberOfUsers',
            'UsersMigrationController@totalNumberOfUsers');
        Route::get('/management/users-migration/totalNumberOfMigratedUsers',
            'UsersMigrationController@totalNumberOfMigratedUsers');
        Route::get('/management/users-migration/totalNumberOfUsers/destination',
            'UsersMigrationController@totalNumberOfUsersOnDestination');
        Route::get('/management/users-migration/totalNumberOfMigratedUsers/destination',
            'UsersMigrationController@totalNumberOfMigratedUsersOnDestination');

        Route::get('/management/users-migration/checkConnection', 'UsersMigrationController@checkConnection');

        Route::post('/management/users-migration/migrate', 'UsersMigrationController@migrate');

        Route::post('/management/users-migration/migrate-stop', 'UsersMigrationController@stopMigration');
        Route::get('/management/users-migration/migrate-resume', 'UsersMigrationController@resumeMigration');

        Route::get('/management/users-migration/history', 'UsersMigrationController@history');
        Route::get('/management/users-migration/batch_history', 'UsersMigrationController@batchHistory');
    });
});