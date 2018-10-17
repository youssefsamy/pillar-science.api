<?php

namespace Functional\Models;

use App\DatabaseMigrations;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use App\TestCase;

class ProjectTest extends TestCase
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

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Project Test Name'
        ]);

        $this->team->admins()->save($this->user);
    }

    /**
     * @group RV-42
     */
    public function testShareWithUser()
    {
        $anotherUser = factory(User::class)->create();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_MANAGER
        ]);

        $this->project->shareWith($anotherUser, ProjectUser::ROLE_CONTRIBUTOR);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_MANAGER
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $anotherUser->id,
            'role' => ProjectUser::ROLE_CONTRIBUTOR
        ]);
    }

    /**
     * @group RV-42
     */
    public function testShareWithUserUpdateRole()
    {
        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_MANAGER
        ]);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_MANAGER
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_VIEWER
        ]);
    }

    /**
     * @group RV-42
     */
    public function testStopSharingWithUser()
    {
        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'role' => ProjectUser::ROLE_MANAGER
        ]);

        $this->project->stopSharingWith($this->user);

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->user->id
        ]);
    }
}
