<?php

namespace Functional\Api\V1\Controllers\User;

use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JoinInvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;

    const EMAIL_ADMIN = 'admin@email.com';
    const PASSWORD_ADMIN = 'secret';

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'user',
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'permissions' => [User::PERMISSION_USER_TEAM_MANAGEMENT]
        ]);
    }

    /**
     * @group RV-114
     */
    public function testStore()
    {
        $this->assertDatabaseMissing('join_invitations', []);

        $this->login(self::EMAIL_ADMIN, self::PASSWORD_ADMIN);

        $this->post(sprintf('/api/users/%s/invitations', $this->user->id))
            ->assertStatus(201);

        $this->assertDatabaseHas('join_invitations', []);
    }
}
