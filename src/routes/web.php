<?php

if ( config('easyroutes.enabled', false) ) {

  Route::group([
    'middleware' => config('easyroutes.middleware', []),
    'namespace'  => '\Ljubadr\EasyRoutes\Controllers',
    'prefix'     => config('easyroutes.route_prefix', 'easyroutes'),
  ], function () {

    Route::get('',             'EasyRoutesController@index');
    Route::get('datatable',    'EasyRoutesController@datatable');
    Route::get('route-exists', 'EasyRoutesController@routeExists');
  });

}
