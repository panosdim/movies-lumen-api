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
use Illuminate\Http\Request;

// Version info
$router->get('/', function () use ($router) {
    return response()->json(['version' => '1.0']);
});

// Login
$router->post('login', ['uses' => 'AuthController@authenticate']);

// Popular Movies
$router->get('/popular', 'MoviesController@popular');

$router->group(
    ['middleware' => 'jwt.auth'],
    function () use ($router) {
        // User Info
        $router->get('user', function (Request $request) {
            return response()->json($request->auth);
        });

        // Update Release Dates
        $router->get('/update', 'MoviesController@update');

        // Search for Movies
        $router->post('/search', 'MoviesController@search');

        // Auto Complete support
        $router->post('/autocomplete', 'MoviesController@autocomplete');


        // Watch List API
        $router->group([
            'prefix' => '/movies',
        ], function () use ($router) {
            $router->get('/', 'MoviesController@index');
            $router->post('/', 'MoviesController@store');
            $router->delete('/{id:[\d]+}', 'MoviesController@destroy');
        });
    }
);
