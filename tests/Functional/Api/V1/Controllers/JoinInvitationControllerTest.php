<?php

namespace App\Functional\Api\V1\Controllers;

use App\Models\JoinInvitation;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JoinInvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group RV-10
     */
    public function testConsume()
    {
        $password = 'testing';
        /** @var JoinInvitation $invitation */
        $invitation =  factory(JoinInvitation::class)->create();

        $passwordBefore = $invitation->user->getAuthPassword();

        $this->assertNull($invitation->consumed_at);

        $this->post(sprintf('api/invitations/%s/consume', $invitation->token), [
            'password' => $password
        ])->isOk();

        $invitation->refresh();
        $invitation->user->refresh();

        $this->assertNotNull($invitation->consumed_at);

        $this->assertNotEquals($passwordBefore, $invitation->user->getAuthPassword());
    }

    /**
     * @group RV-10
     */
    public function testConsumeNonExistent()
    {
        $this->post(sprintf('api/invitations/%s/consume', 'non-existent-token'), [
            'password' => 'new-password'
        ])
            ->assertJsonFragment([
                'status_code' => 404
            ])
            ->assertStatus(404);
    }

    /**
     * @group RV-10
     */
    public function testShow()
    {
        /** @var JoinInvitation $invitation */
        $invitation =  factory(JoinInvitation::class)->create();

        $this->get(sprintf('api/invitations/%s', $invitation->token))
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'permissions'
                ]
            ])
            ->assertJsonFragment([
                'id' => $invitation->user->id
            ]);
    }

    /**
     * @group RV-10
     */
    public function testShowNonExistent()
    {
        $this->get(sprintf('api/invitations/%s', 'non-existent-token'))
            ->assertJsonFragment([
                'status_code' => 404
            ])
            ->assertStatus(404);
    }
}
