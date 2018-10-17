<?php

namespace App\Console\Commands;

use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Console\Command;

class SetupTestDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pillar:setup-test-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the test database schema if not already created';

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
        if (!\App::environment('testing')) {
            $this->output->error('This command will only run in testing environment');
            return -1;
        }

        try {
            $db = new \PDO(sprintf("mysql:host=%s", env('DB_HOST')), env('DB_USERNAME'), env('DB_PASSWORD'));

            $result = $db->exec("CREATE DATABASE IF NOT EXISTS " . env('DB_DATABASE'));

            if ($result === false) {
                print_r($db->errorInfo(), true);
                return;
            }
        } catch (PDOException $exception) {
            die("DB ERROR: ". $exception->getMessage());
        }
    }
}
