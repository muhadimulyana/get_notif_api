<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Info Device
$router->group(['prefix' => 'deviceToken'], function () use ($router) {
    $router->get('/', 'DeviceTokenController@show');
    $router->post('/insert', 'DeviceTokenController@store');
    $router->post('/send', 'DeviceTokenController@sendMessage');
    $router->post('/tes', 'DeviceTokenController@test');
});

// Info Aplikasi
$router->group(['prefix' => 'appInfo'], function () use ($router) {
    $router->get('/getAppInfo/{packageName}', 'AppInfoController@show');
    $router->post('/insert', 'AppInfoController@store');
    $router->post('/checkUpdate', 'AppInfoController@checkUpdate');
});
