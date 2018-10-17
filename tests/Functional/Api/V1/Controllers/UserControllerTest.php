<?php

namespace App\Functional\Api\V1\Controllers;

use App\DatabaseMigrations;
use App\Models\Team;
use App\Models\User;
use App\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    const EMAIL = 'test@email.com';
    const PASSWORD = '123456';

    const EMAIL_ADMIN = 'admin@email.com';
    const PASSWORD_ADMIN = 'secret';

    public function setUp()
    {
        parent::setUp();

        // Normal user
        factory(User::class)->create([
            'name' => 'Test',
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        factory(User::class)->create([
            'name' => 'Super Admin',
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'permissions' => [User::PERMISSION_USER_TEAM_MANAGEMENT]
        ]);
    }

    public function testMe()
    {
        $response = $this->post('api/auth/login', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $response->assertStatus(200);

        $responseJSON = json_decode($response->getContent(), true);
        $token = $responseJSON['token'];

        $this->get('api/auth/me', [], [
            'Authorization' => 'Bearer ' . $token
        ])->assertJson([
            'name' => 'Test',
            'email' => self::EMAIL
        ])->isOk();
    }

    /**
     * @group RV-9
     */
    public function testMeInvalid()
    {
        $this->get('api/auth/me')
            ->assertExactJson([])
            ->assertStatus(200);
    }

    public function testIndexForbidden()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get('api/users')->isForbidden();
    }

    public function testIndex()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get('api/users')
            ->assertJsonFragment([
                'name' => 'Super Admin'
            ])->isOk();
    }

    /**
     * @group RV-10
     */
    public function testPermissions()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get('api/users/permissions')
            ->assertJson([User::PERMISSION_USER_TEAM_MANAGEMENT]);
    }

    /**
     * @group RV-10
     */
    public function testPermissionsUserForbidden()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get('api/users/permissions')
            ->assertStatus(403);
    }

    /**
     * @group RV-9
     */
    public function testShow()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->get(sprintf('api/users/%s', 1))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
                'permissions',
                'teams'
            ]);
    }

    /**
     * @group RV-9
     */
    public function testShowForbidden()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/users/%s', 1))
            ->assertStatus(403);
    }

    /**
     * @group RV-9
     */
    public function testUpdate()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $user = factory(User::class)->create([
            'name' => 'Stephen the updated',
            'email' => 'updated@pillar.science'
        ]);

        $this->put(sprintf('api/users/%s', $user->id), [
            'name' => 'Stephen the updated 2'
        ])
            ->assertStatus(200);
    }

    /**
     * @group RV-9
     */
    public function testUpdateWithTeams()
    {
        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        /** @var User $user */
        $user = factory(User::class)->create([
            'name' => 'Stephen the updated',
            'email' => 'updated@pillar.science'
        ]);

        $team = factory(Team::class)->create();
        $teamAdmin = factory(Team::class)->create();

        $this->put(sprintf('api/users/%s', $user->id), [
            'name' => 'Stephen the updated 2',
            'teams' => [$team->id],
            'teamsAdmin' => [$teamAdmin->id]
        ])
            ->assertStatus(200);

        $user->refresh();

        $this->assertEquals(2, $user->teams()->count());
    }

    /**
     * @group RV-9
     */
    public function testUpdateForbidden()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/users/%s', 1))
            ->assertStatus(403);
    }
}
