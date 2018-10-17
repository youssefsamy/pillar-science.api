<?php

namespace App\Functional\Api\V1\Controllers;

use App\Models\Dataset;
use App\Models\Team;
use App\Models\User;
use App\Services\Datasets\DatasetManager;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;

    /** @var Team */
    private $team;

    const EMAIL_ADMIN = 'admin@email.com';
    const PASSWORD_ADMIN = 'secret';

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'Super Admin',
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'permissions' => [User::PERMISSION_USER_TEAM_MANAGEMENT]
        ]);

        $this->team = factory(Team::class)->create([
            'name' => 'Laboratoire de tests fonctionnels'
        ]);

        $this->team->admins()->save($this->user);
    }

    /**
     * @group RV-23
     */
    public function testSearch()
    {
        factory(Team::class)->create([
            'name' => 'Search team'
        ]);

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get('api/teams/search')
            ->assertJsonFragment([
                'name' => 'Search team'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-9
     */
    public function testIndex()
    {
        /** @var Team $team */
        $team = factory(Team::class)->create([
            'name' => 'List team',
        ]);

        $user = User::whereEmail(self::EMAIL_ADMIN)->first();

        $team->users()->attach($user);

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get('api/teams')
            ->assertJsonFragment([
                'name' => 'List team'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-9
     */
    public function testShow()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get(sprintf('api/teams/%s', $this->team->id))
            ->assertJson([
                'name' => 'Laboratoire de tests fonctionnels'
            ])
            ->assertStatus(200);
    }

    /**
     * @group RV-9
     */
    public function testShowNotFound()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get(sprintf('api/teams/%s', 999))
            ->assertStatus(404);
    }

    /**
     * @group RV-9
     */
    public function testStore()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $response = $this->post('api/teams', [
            'name' => 'Laboratoire de storage'
        ])
            ->assertStatus(201);

        $id = $response->json('id');

        $this->assertDatabaseHas('teams', [
            'id' => $id,
            'name' => 'Laboratoire de storage'
        ]);
    }

    /**
     * @group RV-9
     */
    public function testStoreNameRequired()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->post('api/teams')
            ->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'name'
                    ]
                ]
            ]);
    }

    /**
     * @group RV-9
     */
    public function testUpdate()
    {
        $team = factory(Team::class)->create();

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->put(sprintf('api/teams/%s', $team->id), [
            'name' => 'Update laboratory'
        ])->assertStatus(200);

        $this->assertDatabaseHas('teams', [
            'name' => 'Update laboratory'
        ]);
    }

    /**
     * @group RV-9
     */
    public function testDestroy()
    {
        $team = factory(Team::class)->create();

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/teams/%s', $team->id), [
            'id' => $team->id
        ]);

        $this->assertDatabaseMissing('teams', [
            'id' => $team->id,
            'deleted_at' => null
        ]);
    }

    /**
     * @group RV-9
     */
    public function testDestroyWithMoreData()
    {
        $team = factory(Team::class)->create();

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'deleted_at' => null
        ]);

        $manager = app(DatasetManager::class);

        /** @var Dataset $userDirectory */
        $userDirectory = $this->team->userDirectories->first();

        $sub = $manager->createDirectory(['name' => 'firstDir'], $userDirectory);
        $manager->createOrUpdateDataset(UploadedFile::fake()->create('data.txt'), 'data.txt', $userDirectory);
        $manager->createOrUpdateDataset(UploadedFile::fake()->create('subdata.txt'), 'subdata.txt', $sub);

        $this->delete(sprintf('api/teams/%s', $team->id), [
            'id' => $team->id
        ]);

        $this->assertDatabaseMissing('teams', [
            'id' => $team->id,
            'deleted_at' => null
        ]);
    }
}
