<?php

namespace App\Functional\Models;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DatasetVersionTest extends TestCase
{
    use DatabaseMigrations;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;

    protected function setUp()
    {
        parent::setUp();

        $user = factory(User::class)->create([
            'name' => 'Basic User',
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $this->team = factory(Team::class)->create([
            'name' => 'Laboratoire de tests fonctionnels sur les projets'
        ]);

        $this->team->admins()->save($user);

        $this->project = factory(Project::class)->create([
            'name' => 'Dataset testing project',
            'created_by' => $user->id
        ]);

        $this->team->projects()->save($this->project);
    }

    /**
     * @group RV-8
     * @expectedException \App\Exceptions\ImmutableModelException
     */
    public function testUpdateNotAllowed()
    {
        $version = $this->project->directory->currentVersion;

        $version->update([
            'name' => 'not allowed'
        ]);
    }

    /**
     * @group RV-8
     * @expectedException \App\Exceptions\ImmutableModelException
     */
    public function testDeleteNotAllowed()
    {
        $version = $this->project->directory->currentVersion;

        $version->delete();
    }
}
