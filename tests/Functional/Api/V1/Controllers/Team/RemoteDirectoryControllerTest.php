<?php

namespace App\Functional\Api\V1\Controllers\Team;

use App\Models\Project;
use App\Models\RemoteDirectory;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemoteDirectoryControllerTest extends TestCase
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
            'name' => 'Laboratoire de tests fonctionnels sur les remote directories'
        ]);

        $this->team->admins()->save($user);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $user, [
            'name' => 'Dataset testing project',
        ]);

        $this->team->projects()->save($this->project);
    }

    /**
     * @group RV-53
     */
    public function testIndex()
    {
        factory(RemoteDirectory::class)->create([
            'team_id' => $this->team->id
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('/api/teams/%s/remote-directories', $this->team->id))
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'last_action_at'
                ]
            ]);
    }

    /**
     * @group RV-53
     */
    public function testIndexWithQuery()
    {
        factory(RemoteDirectory::class)->create([
            'team_id' => $this->team->id,
            'computer_id' => 'mathieu'
        ]);

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('/api/teams/%s/remote-directories?computer=mathieu', $this->team->id))
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'last_action_at'
                ]
            ]);
    }

    /**
     * @group RV-53
     */
    public function testIndexNoRemoteDirectories()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('/api/teams/%s/remote-directories', $this->team->id))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    /**
     * @group RV-53
     */
    public function testIndexNoRemoteDirectoriesForThisTeam()
    {
        // Creates a new team associated to the remote directory
        factory(RemoteDirectory::class)->create();

        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('/api/teams/%s/remote-directories', $this->team->id))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    /**
     * @group RV-53
     */
    public function testStore()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('/api/teams/%s/remote-directories', $this->team->id), [
            'name' => 'directory-name',
            'computer_id' => 'mat-computer'
        ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'last_action_at',
                'secret_key'
            ]);
    }
}
