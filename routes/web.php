<?php

Route::group(['namespace' => "Acacha\UsersEbreEscoolMigration\Http\Controllers", 'middleware' => 'web'], function () {

      Route::group( ['middleware' => 'auth'] , function () {
          //Users migration
          Route::get('management/users-migration', 'UsersMigrationController@index')->name('users-migration');

    });

});
