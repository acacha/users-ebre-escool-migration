<?php

Route::group(['namespace' => "Acacha\UsersEbreEscoolMigration\Http\Controllers",
    'prefix' => 'api/v1', 'middleware' => ['api','throttle','bindings']], function () {

    //HERE API PUBLIC ROUTES

    Route::group(['middleware' => 'auth:api'], function() {
        //HERE API PRIVATE ROUTES
//        Route::get('/events',                     'APIEventsController@index');

    });
});