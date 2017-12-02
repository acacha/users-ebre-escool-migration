<?php

Route::group(['namespace' => "Acacha\UsersEbreEscoolMigration\Http\Controllers", 'middleware' => 'web'], function () {

      Route::group( ['middleware' => 'auth'] , function () {
          // Events Web without API only Laravel PHP
//          Route::get('/events_php',                 'EventsController@index');

    });

});
