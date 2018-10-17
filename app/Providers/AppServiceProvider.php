<?php

namespace App\Providers;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\RenderableException;
use App\Exceptions\StorageSyncException;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use App\Models\RemoteDirectory;
use App\Models\Metadata;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Observers\DatasetObserver;
use App\Observers\DatasetVersionObserver;
use App\Observers\RemoteDirectoryObserver;
use App\Observers\MetadataObserver;
use App\Observers\ProjectObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use App\Services\MimeTypes\CsvMimeTypeGuesser;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Scout\ModelObserver;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use TeamTNT\TNTSearch\TNTSearch;
use Maknz\Slack\Client as Slack;

class AppServiceProvider extends ServiceProvider
{
    /** @var RenderableException */
    private $renderableExceptions = [
        StorageSyncException::class => 500,
        InvalidParameterException::class => 422
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Team::observe(TeamObserver::class);
        User::observe(UserObserver::class);
        Project::observe(ProjectObserver::class);
        Dataset::observe(DatasetObserver::class);
        DatasetVersion::observe(DatasetVersionObserver::class);
        RemoteDirectory::observe(RemoteDirectoryObserver::class);
        Metadata::observe(MetadataObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*if ($this->app->environment() !== 'production') {
            $this->app-$this->register(L5SwaggerServiceProvider::class);
        }*/

        $this->app->bind(Slack::class, function () {
            return new Slack(config('slack.webhook'), config('slack.settings'));
        });

        \HTMLPurifier_URISchemeRegistry::instance()
            ->register('data', new \HTMLPurifier_URIScheme_data());

        // We will manually handle the searchable sync because
        // datasets depend on dataset versions
        ModelObserver::disableSyncingFor(Dataset::class);

        MimeTypeGuesser::getInstance()
            ->register(new CsvMimeTypeGuesser());

        // Copied from TNTSearchScoutServiceProvider
        $this->app->bind(TNTSearch::class, function () {
            $tnt = new TNTSearch();

            $driver = config('database.default');
            $config = config('scout.tntsearch') + config("database.connections.{$driver}");

            $tnt->loadConfig($config);
            $tnt->setDatabaseHandle(app('db')->connection()->getPdo());

            return $tnt;
        });

        $handler = app('Dingo\Api\Exception\Handler');

        $handler->register(function (ValidationException $exception) {
            throw new ResourceException('An error occurred', $exception->validator->errors());
        });

        foreach ($this->renderableExceptions as $exception => $errorCode) {
            $handler->register(function (RenderableException $exception) use ($errorCode) {
                return \Response::make($exception->render(), $errorCode);
            });
        }

        $handler->register(function (ModelNotFoundException $exception) {
            $model = last(explode('\\', $exception->getModel()));

            return \Response::make([
                'error' => [
                    'message' => sprintf('No query results for model [%s]', $model),
                    'status_code' => 404
                ]
            ], 404);
        });

        $handler->register(function (AuthenticationException $exception) {
            return \Response::make([
                'error' => [
                    'message' => $exception->getMessage(),
                    'status_code' => 401
                ]
            ], 401);
        });

        $handler->register(function (AuthorizationException $exception) {
            return \Response::make([
                'error' => [
                    'message' => $exception->getMessage(),
                    'status_code' => 403
                ]
            ], 403);
        });
    }
}
