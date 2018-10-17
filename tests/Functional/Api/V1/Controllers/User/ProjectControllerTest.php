<?php

namespace Functional\Api\V1\Controllers\User;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'Basic User',
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $this->team = factory(Team::class)->create([
            'name' => 'Laboratoire de tests fonctionnels sur les remote directories'
        ]);

        $this->team->admins()->save($this->user);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Dataset testing project',
        ]);

        $this->team->projects()->save($this->project);
    }

    /**
     * @group RV-42
     */
    public function testShow()
    {
        $this->login();

        $this->get(sprintf('api/users/%s/projects/%s', $this->user->id, $this->project->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'created_at',
                'updated_at',
                'description',
                'pivot' => [
                    'role',
                    'created_at',
                    'updated_at'
                ],
                'team' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * @group RV-42
     */
    public function testShowNotAllowed()
    {
        $this->login();

        /** @var Project $unrelatedProject */
        $unrelatedProject = factory(Project::class)->create();

        $this->get(sprintf('api/users/%s/projects/%s', $this->user->id, $unrelatedProject->id))
            ->assertStatus(403);
    }
}