<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pillar:clear-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all stored files and directories in the app storage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (\App::environment('production')) {
            $this->output->error('This command cannot be run in production environment');
            return -1;
        }

        \Storage::deleteDirectory(config('pillar.storage.datasets.upload_dir'), config('pillar.storage.datasets.disk'));
        \File::delete(\File::glob(config('scout.tntsearch.storage') . '/*'));
    }
}
