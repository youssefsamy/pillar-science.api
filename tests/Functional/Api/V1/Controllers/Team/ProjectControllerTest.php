<?php

namespace App\Functional\Api\V1\Controllers\Team;

use App\Models\Team;
use App\Models\TeamUser;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var Team */
    private $team;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

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

        $this->team->users()->save($user, [
            'role' => TeamUser::ROLE_ADMIN
        ]);
    }

    /**
     * @group RV-23
     */
    public function testStore()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $id = $this->post(sprintf('api/teams/%s/projects', $this->team->id), [
            'name' => 'My storage project'
        ])
            ->assertStatus(201)
            ->json('id');

        $this->assertDatabaseHas('projects', [
            'id' => $id
        ]);
    }

    /**
     * @group RV-23
     */
    public function testStoreNotAllowed()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $team = factory(Team::class)->create();

        $this->post(sprintf('api/teams/%s/projects', $team->id), [
            'name' => 'My storage project'
        ])
            ->assertStatus(403);
    }
}
