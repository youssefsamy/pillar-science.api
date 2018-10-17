<?php

namespace App;

trait DatabaseMigrations
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations {
        runDatabaseMigrations as baseRunDatabaseMigrations;
    }

    public function runDatabaseMigrations()
    {
        $this->baseRunDatabaseMigrations();

        $this->artisan('pillar:clear-storage');
    }
}