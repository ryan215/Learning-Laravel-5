<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api'], function () {


    // Even though this is just a demo task, versioning APIs is very important
    Route::group(['prefix' => 'v1'], function () {

        // Using explicit routing instead of Route::resource() as per
        // https://philsturgeon.uk/php/2013/07/23/beware-the-route-to-evil/
        Route::get('resources', 'ResourcesController@search');
        Route::get('resources/{searchString}', 'ResourcesController@search');

        // Most methods are only available to authenticated in users.
        // If we are worried about DDOS or general abuse, even the methods above this group could be moved within it
        Route::group(['middleware' => 'auth.api'], function () {

            Route::post('resources/{searchString}/lease', 'ResourcesController@lease');

            Route::get('leases', 'LeasesController@index');
            Route::get('leases/{leaseId}', 'LeasesController@show');
            Route::delete('leases/{leaseId}/terminate', 'LeasesController@terminate');

        });

        // Ideas for additional routes/options:
        // - leases/?expired=1
        // - leases/{id}/extend
    });
});

