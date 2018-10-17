<?php

namespace App\Functional\Api\V1\Controllers;

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
    public function testShow()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $remoteDirectory = factory(RemoteDirectory::class)->create([
            'team_id' => $this->team->id
        ]);

        $this->get(sprintf('/api/remote-directories/%s', $remoteDirectory->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'last_action_at',
                'directory' => [
                    'id',
                    'size',
                    'type',
                    'created_at',
                    'updated_at',
                    'name'
                ]
            ]);
    }

    /**
     * @group RV-53
     */
    public function testUpdate()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $remoteDirectory = factory(RemoteDirectory::class)->create([
            'name' => 'My test remoteDirectory',
            'team_id' => $this->team->id
        ]);

        $this->assertDatabaseHas('remote_directories', ['name' => 'My test remoteDirectory']);
        $this->assertDatabaseMissing('remote_directories', ['name' => 'Updated test remoteDirectory']);

        $this->put(sprintf('/api/remote-directories/%s', $remoteDirectory->id), [
            'name' => 'Updated test remoteDirectory'
        ])
            ->assertStatus(200);

        $this->assertDatabaseMissing('remote_directories', ['name' => 'My test remoteDirectory']);
        $this->assertDatabaseHas('remote_directories', ['name' => 'Updated test remoteDirectory']);
    }
}
