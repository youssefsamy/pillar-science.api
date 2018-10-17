<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateFirstUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pillar:create-first-user';

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
        if (User::query()->first()) {
            $this->output->error('The first user has already been created. This command can not create any further users');
            return -1;
        }

        $name = $this->ask('Name');
        $email = $this->ask('Email');

        do {
            $password = $this->secret('Password');
            $confirmPassword = $this->secret('Confirm password');

            if ($password !== $confirmPassword) {
                $this->warn('Passwords don\'t match. Try again');
            }
        } while ($password !== $confirmPassword);

        if (!$this->confirm("This will create the system's first user. You will not be able to create further users using this command. Do you wish to continue?", true)) {
            $this->line('Canceled by user');
            return 0;
        }

        $user = User::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->permissions = ['super-admin'];

        $user->save();
        $this->output->success('User created succesfully');
    }
}
