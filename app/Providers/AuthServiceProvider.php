<?php

namespace App\Providers;

use App\Models\Dataset;
use App\Models\RemoteDirectory;
use App\Models\Metadata;
use App\Models\Project;
use App\Models\Protocol;
use App\Policies\DatasetPolicy;
use App\Policies\RemoteDirectoryPolicy;
use App\Policies\MetadataPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ProtocolPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Dataset::class => DatasetPolicy::class,
        RemoteDirectory::class => RemoteDirectoryPolicy::class,
        Protocol::class => ProtocolPolicy::class,
        Metadata::class => MetadataPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
