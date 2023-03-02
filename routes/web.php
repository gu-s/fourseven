<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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


$router->get('users', 'UserController@list');
$router->get('search_session', 'UserController@searchSession');
$router->get('list_active_users', 'UserController@listActiveUsers');
$router->get('common_sessions', 'UserController@getMostCommonSessionDurations');
$router->get('users_logged_consecutively', 'UserController@getUsersLoggedConsecutively');
