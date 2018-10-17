<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedBrowserTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pillar:seed-browser-tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds the database for browser tests';

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

        $this->call('migrate:fresh');
        $this->line("Seeding test database for browser tests");
        $this->call('db:seed', ['--class' => 'BrowserTestSeeder']);
        $this->output->success('Database ready for browser testing');
    }
}
