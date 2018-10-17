<?php

namespace App\Functional\Api\V1\Controllers;

use App\DatabaseMigrations;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use App\TestCase;

class ProjectControllerTest extends TestCase
{
    use DatabaseMigrations;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /** @var Team */
    private $team;
    /** @var User */
    private $user;
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
            'name' => 'Laboratoire de tests fonctionnels sur les projets'
        ]);

        $this->team->admins()->save($this->user);
    }

    /**
     * @group RV-23
     */
    public function testIndex()
    {
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'My test project',
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get('api/projects')
            ->assertJsonFragment([
                'name' => 'My test project'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-23
     */
    public function testShow()
    {
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'My test project',
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s', $this->project->id))
            ->assertJson([
                'name' => 'My test project'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-23
     */
    public function testShowNotFound()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s', 999))
            ->assertStatus(404);
    }

    /**
     * @group RV-23
     */
    public function testUpdate()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'My test project',
        ]);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'name' => 'Update project laboratory'
        ])->assertStatus(200);

        $this->assertDatabaseHas('projects', [
            'name' => 'Update project laboratory'
        ]);
    }

    /**
     * @group RV-23
     */
    public function testUpdateNotAllowed()
    {
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, factory(User::class)->create(), [
            'name' => 'My test project',
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'name' => 'Update project laboratory'
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-23
     */
    public function testDestroy()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Test Name'
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/projects/%s', $this->project->id), [
            'id' => $this->project->id
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => $this->project->id,
            'deleted_at' => null
        ]);
    }

    /**
     * @group RV-23
     */
    public function testDestroyNotAllowed()
    {
        // Not current user's project
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, factory(User::class)->create(), [
            'name' => 'Project Test Name'
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id
        ]);

        $this->delete(sprintf('api/projects/%s', $this->project->id), [
            'id' => $this->project->id
        ])->assertStatus(403);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id
        ]);
    }

    // Project Sharing Related

    /**
     * @group RV-42
     */
    public function testShowNotShared()
    {
        // Not current user's project
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Not Allowed'
        ]);

        $this->project->stopSharingWith($this->user);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s', $this->project->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testShowAsViewer()
    {
        // Not current user's project
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Viewer'
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s', $this->project->id))
            ->assertJson([
                'name' => 'Project Viewer'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-42
     */
    public function testShowAsContributor()
    {
        // Not current user's project
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Viewer'
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/projects/%s', $this->project->id))
            ->assertJson([
                'name' => 'Project Viewer'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-42
     */
    public function testUpdateNameAsViewer()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'My test project',
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'name' => 'Update project laboratory'
        ])->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testUpdateNameAsContributor()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'My test project',
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'name' => 'Update project laboratory'
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testUpdateDescriptionAsViewer()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'project name',
            'description' => 'My project description',
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'name' => 'Update project laboratory'
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testUpdateDescriptionAsContributor()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'project name',
            'description' => 'My project description',
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->put(sprintf('api/projects/%s', $this->project->id), [
            'description' => 'Update project description'
        ])
            ->assertStatus(200);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'description' => '<p>Update project description</p>'
        ]);
    }

    /**
     * @group RV-42
     */
    public function testDestroyAsViewer()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Test Name'
        ])
            ->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/projects/%s', $this->project->id), [
            'id' => $this->project->id
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testDestroyAsContributor()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Test Name'
        ])
            ->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/projects/%s', $this->project->id), [
            'id' => $this->project->id
        ])
            ->assertStatus(403);
    }
}
