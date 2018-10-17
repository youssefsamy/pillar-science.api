<?php

namespace Functional\Api\V1\Controllers\Project;

use App\DatabaseMigrations;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use App\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

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
    public function testAvailableAutocomplete()
    {
        $this->login();

        $this->get(sprintf('api/projects/%s/users-available', $this->project->id))
            ->assertJsonCount(0);
    }

    /**
     * @group RV-42
     */
    public function testAvailableAutocompleteHasOneAvailable()
    {
        $this->login();

        factory(User::class)->create();

        $this->get(sprintf('api/projects/%s/users-available', $this->project->id))
            ->assertJsonCount(1);
    }

    /**
     * @group RV-42
     */
    public function testAvailableAutocompleteBoundUserIsNotAvailable()
    {
        $this->login();

        $user = factory(User::class)->create();

        $this->get(sprintf('api/projects/%s/users-available', $this->project->id))
            ->assertJsonCount(1);

        $this->project->shareWith($user, ProjectUser::ROLE_VIEWER);

        $this->get(sprintf('api/projects/%s/users-available', $this->project->id))
            ->assertJsonCount(0);
    }

    /**
     * @group RV-42
     */
    public function testIndex()
    {
        $this->login();

        $this->get(sprintf('api/projects/%s/users', $this->project->id))
            ->assertJsonCount(1);

        $user = factory(User::class)->create();

        $this->project->shareWith($user, ProjectUser::ROLE_VIEWER);

        $this->get(sprintf('api/projects/%s/users', $this->project->id))
            ->assertJsonCount(2);
    }

    /**
     * @group RV-42
     */
    public function testIndexNotAllowedToListForAnotherProject()
    {
        $this->login();

        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();

        $this->get(sprintf('api/projects/%s/users', $anotherProject->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testUpdate()
    {
        $this->login();

        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->project->shareWith($anotherUser, ProjectUser::ROLE_MANAGER);

        $this->put(sprintf('api/projects/%s/users/%s', $this->project->id, $anotherUser->id), [
            'role' => ProjectUser::ROLE_VIEWER
        ])
            ->assertStatus(200);
    }

    /**
     * @group RV-42
     */
    public function testUpdateNotAllowed()
    {
        $this->login();

        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->put(sprintf('api/projects/%s/users/%s', $this->project->id, $anotherUser->id), [
            'role' => ProjectUser::ROLE_CONTRIBUTOR
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-42
     */
    public function testDestroy()
    {
        $this->login();

        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->project->shareWith($anotherUser, ProjectUser::ROLE_MANAGER);

        $this->delete(sprintf('api/projects/%s/users/%s', $this->project->id, $anotherUser->id))
            ->assertStatus(200);
    }

    /**
     * @group RV-42
     */
    public function testDestroyNotAllowed()
    {
        $this->login();

        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);
        $this->project->shareWith($anotherUser, ProjectUser::ROLE_MANAGER);

        $this->delete(sprintf('api/projects/%s/users/%s', $this->project->id, $anotherUser->id))
            ->assertStatus(403);
    }
}
