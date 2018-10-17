<?php

namespace App\Functional\Api\V1\Controllers;

use App\Models\User;
use App\TestCase;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignUpControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;
    /** @var User */
    private $admin;

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'user',
            'email' => 'user@pillar.science',
            'password' => 'user'
        ]);

        $this->admin = factory(User::class)->create([
            'name' => 'admin',
            'email' => 'admin@pillar.science',
            'password' => 'admin',
            'permissions' => [User::PERMISSION_USER_TEAM_MANAGEMENT]
        ]);
    }

    public function testSignUpFailForNonSuperUser()
    {
        $token = $this->loginAs($this->user);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '123456'
        ], [
            'Authorization' => 'Bearer ' . $token
        ])->assertJsonStructure([
            'error'
        ])->isForbidden();
    }

    public function testSignUpSuccessfully()
    {
        $token = $this->loginAs($this->admin);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '123456'
        ], [
            'Authorization' => 'Bearer ' . $token
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpSuccessfullyWithTokenRelease()
    {
        $token = $this->loginAs($this->admin);

        Config::set('boilerplate.sign_up.release_token', true);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '123456'
        ], [], [
            'Authorization' => 'Bearer ' . $token
        ])->assertJsonStructure([
            'status', 'token'
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpReturnsValidationError()
    {
        $token = $this->loginAs($this->admin);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
        ], [], [
            'Authorization' => 'Bearer ' . $token
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
