<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->group(['middleware' => ['web']], function(Router $api) {
            $api->get('login/{social}','App\\Api\\V1\\Controllers\\SocialAuthController@redirect')->where('social','twitter|facebook|linkedin|google|github|bitbucket');
            $api->get('login/{social}/callback','App\\Api\\V1\\Controllers\\SocialAuthController@callback')->where('social','twitter|facebook|linkedin|google|github|bitbucket');
        });
       

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('resetpassword', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');
        //$api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
    });

    $api->get('swagger', 'App\\Api\\V1\\Controllers\\SwaggerController@swagger');

    $api->group(['middleware' => ['bindings']], function (Router $api) {
        // Authenticated routes
        $api->group(['middleware' => ['auth:api']], function(Router $api) {
            $api->group(['middleware' => 'admin:' . \App\Models\User::PERMISSION_USER_TEAM_MANAGEMENT], function (Router $api) {
                $api->get('users/permissions', 'App\\Api\\V1\\Controllers\\UserController@permissions');

                $api->resource('users', 'App\Api\V1\Controllers\UserController', [
                    'only' => ['index', 'show', 'update']
                ]);

                $api->resource('users.invitations', 'App\Api\V1\Controllers\User\JoinInvitationController', [
                    'only' => ['store']
                ]);

                $api->get('/teams/search', 'App\Api\V1\Controllers\TeamController@search');
                $api->resource('teams', 'App\Api\V1\Controllers\TeamController', [
                    'only' => ['store', 'update', 'destroy']
                ]);
            });

            $api->group(['middleware' => 'admin:' . \App\Models\User::PERMISSION_DESKTOP_CLIENT_MANAGEMENT], function (Router $api) {
                $api->resource('desktop-clients', 'App\Api\V1\Controllers\DesktopClientController', [
                    'only' => ['index', 'store']
                ]);
            });

            $api->resource('teams', 'App\Api\V1\Controllers\TeamController', [
                'only' => ['index', 'show']
            ]);

            $api->resource('teams.computers', 'App\Api\V1\Controllers\Team\ComputerController', [
                'only' => ['index']
            ]);

            $api->resource('projects', 'App\Api\V1\Controllers\ProjectController', [
                'only' => ['index', 'show', 'update', 'destroy']
            ]);

            $api->resource('teams.projects', 'App\Api\V1\Controllers\Team\ProjectController', [
                'only' => ['store']
            ]);

            $api->resource('teams.remote-directories', 'App\Api\V1\Controllers\Team\RemoteDirectoryController', [
                'only' => ['index', 'store']
            ]);

            $api->resource('remote-directories', 'App\Api\V1\Controllers\RemoteDirectoryController', [
                'only' => ['show', 'update']
            ]);

            $api->resource('projects.dataset', 'App\Api\V1\Controllers\Project\DatasetController', [
                'only' => ['index']
            ]);

            $api->resource('projects.users', 'App\Api\V1\Controllers\Project\UserController', [
                'only' => ['index', 'update', 'destroy']
            ]);

            $api->resource('users.projects', 'App\Api\V1\Controllers\User\ProjectController', [
                'only' => ['show']
            ]);

            $api->get('/projects/{project}/users-available', 'App\Api\V1\Controllers\Project\UserController@availableAutocomplete');

            $api->resource('datasets.protocols', 'App\Api\V1\Controllers\Dataset\ProtocolController', [
                'only' => ['show', 'store', 'update', 'destroy']
            ]);
            $api->get('/datasets/{dataset}/protocols-available', 'App\Api\V1\Controllers\Dataset\ProtocolController@availableAutocomplete');

            $api->resource('protocols', 'App\Api\V1\Controllers\ProtocolController', [
                'only' => ['index', 'show', 'update']
            ]);

            $api->resource('datasets.metadata', 'App\Api\V1\Controllers\Dataset\MetadataController', [
                'only' => ['index', 'store']
            ]);

            $api->resource('metadata', 'App\Api\V1\Controllers\MetadataController', [
                'only' => ['destroy']
            ]);

            $api->resource('datasets.dataset-versions', 'App\Api\V1\Controllers\Dataset\DatasetVersionController', [
                'only' => ['store']
            ]);

            $api->get('/search', 'App\Api\V1\Controllers\SearchController@search');

            $api->post('/datasets/{dataset}/upload', 'App\Api\V1\Controllers\DatasetController@upload');
            $api->post('/datasets/{dataset}/create-directory', 'App\Api\V1\Controllers\DatasetController@createDirectory');
            $api->post('/datasets/{dataset}/update', 'App\Api\V1\Controllers\DatasetController@update');
            $api->get('/datasets/{dataset}/ancestors', 'App\Api\V1\Controllers\DatasetController@ancestors');
            $api->get('/datasets/{dataset}/tree', 'App\Api\V1\Controllers\DatasetController@tree');
            $api->put('/datasets/{dataset}/move-to/{target}', 'App\Api\V1\Controllers\DatasetController@move');
            $api->post('/datasets/{dataset}/map-to/{target}', 'App\Api\V1\Controllers\DatasetController@map');
            $api->resource('datasets', 'App\Api\V1\Controllers\DatasetController', [
                'only' => ['show', 'destroy']
            ]);
        });

        // Public routes
        $api->resource('invitations', 'App\Api\V1\Controllers\JoinInvitationController', [
            'only' => ['show'],
        ]);

        $api->get('desktop-clients/download', 'App\Api\V1\Controllers\DesktopClientController@download');
        $api->get('desktop-clients/latest', 'App\Api\V1\Controllers\DesktopClientController@latest');

        $api->get('datasets/{dataset}/preview', 'App\Api\V1\Controllers\DatasetController@preview');
        $api->get('datasets/{dataset}/dataset-versions/{version}/preview', 'App\Api\V1\Controllers\Dataset\DatasetVersionController@preview');

        $api->post('invitations/{token}/consume', 'App\\Api\\V1\\Controllers\\JoinInvitationController@consume');

        $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
            $api->get('protected', function() {
                return response()->json([
                    'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
                ]);
            });

            $api->get('refresh', [
                'middleware' => 'jwt.refresh',
                function() {
                    return response()->json([
                        'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                    ]);
                }
            ]);
        });
    });
});
