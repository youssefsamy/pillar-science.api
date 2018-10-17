<?php

namespace App\Functional\Api\V1\Controllers\Project;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetControllerTest extends TestCase
{
    use RefreshDatabase;

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

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $user, [
            'name' => 'Dataset testing project',
        ]);

        $this->team->projects()->save($this->project);
    }

    /**
     * @group RV-8
     */
    public function testIndex()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s/dataset', $this->project->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'size',
                'type',
                'created_at',
                'updated_at',
                'name'
            ])
            ->assertJsonFragment([
                'name' => 'Dataset testing project'
            ]);
    }
}
