<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use Illuminate\Console\Command;

class FixDatasetErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pillar:fix-datasets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $hasErrors = Dataset::isBroken();

        if (!$hasErrors) {
            $this->output->success('Dataset tree is fine');

            return;
        }

        $errors = Dataset::countErrors();

        $this->info(sprintf('Oddness:        %s nodes that have wrong set of lft and rgt values', $errors['oddness']));
        $this->info(sprintf('Duplicates:     %s nodes that have same lft or rgt values', $errors['duplicates']));
        $this->info(sprintf('Wrong Parent:   %s nodes that have invalid parent_id value that doesn\'t correspond to lft and rgt', $errors['wrong_parent']));
        $this->info(sprintf('Missing Parent: %s nodes that have parent_id pointing to node that doesn\'t exists', $errors['missing_parent']));

        $input = $this->ask('Dataset tree is broken. Do you want to fix it?', 'Y');

        if (strtolower($input) === 'y') {
            Dataset::fixTree();
            $this->info('Dataset tree fixed');
        }

        $this->info('No action taken');
    }
}
