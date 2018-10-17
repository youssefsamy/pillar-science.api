<?php

namespace Functional\Api\V1\Controllers;

use App\Models\DesktopClient;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DesktopClientControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'Basic User',
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
            'permissions' => [User::PERMISSION_DESKTOP_CLIENT_MANAGEMENT]
        ]);
    }

    /**
     * @group RV-102
     */
    public function testIndex()
    {
        $this->login();

        factory(DesktopClient::class)->create();

        $this->get('/api/desktop-clients')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testIndexNoPermissionNotAllowed()
    {
        factory(User::class)->create([
            'name' => 'Basic User',
            'email' => 'user2@email.com',
            'password' => self::PASSWORD
        ]);

        $this->login('user2@email.com', self::PASSWORD);

        factory(DesktopClient::class)->create();

        $this->get('/api/desktop-clients')
            ->assertStatus(403);
    }

    /**
     * @group RV-102
     */
    public function testLatest()
    {
        $this->login();

        factory(DesktopClient::class)->create();

        $this->get('/api/desktop-clients/latest')
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'size',
                'created_at'
            ]);
    }

    /**
     * @group RV-102
     */
    public function testDownload()
    {
        $this->login();

        $this->post('api/desktop-clients', [
            'application' => UploadedFile::fake()->create('pillar-science.zip')
        ]);

        $this->get('api/desktop-clients/download')
            ->assertStatus(200);
    }

    /**
     * @group RV-102
     */
    public function testDownloadNoContent()
    {
        $this->login();

        $this->get('api/desktop-clients/download')
            ->assertStatus(204);
    }

    /**
     * @group RV-102
     */
    public function testStore()
    {
        $this->login();

        $this->assertDatabaseMissing('desktop_clients', []);

        $this->post('api/desktop-clients', [
            'application' => UploadedFile::fake()->create('pillar-science.zip')
        ])->assertStatus(201);

        $this->assertDatabaseHas('desktop_clients', []);
    }

    /**
     * @group RV-102
     */
    public function testStoreNoPermissionNotAllowed()
    {
        factory(User::class)->create([
            'name' => 'Basic User',
            'email' => 'user2@email.com',
            'password' => self::PASSWORD
        ]);

        $this->login('user2@email.com', self::PASSWORD);

        $this->assertDatabaseMissing('desktop_clients', []);

        $this->post('api/desktop-clients', [
            'application' => UploadedFile::fake()->create('pillar-science.zip')
        ])->assertStatus(403);
    }
}
