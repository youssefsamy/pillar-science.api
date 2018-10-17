<?php

namespace Functional\Api\V1\Controllers;

use App\Models\Dataset;
use App\Models\Project;
use App\Models\Protocol;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ProtocolControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;

    /** @var Dataset A dataset in a project */
    private $dataset;

    /** @var User */
    private $user;

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

        $this->login(self::EMAIL, self::PASSWORD);

        $datasetId = $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id), [
            'file' => UploadedFile::fake()->create('test.txt')
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test.txt',
                'type' => 'dataset'
            ])
            ->json('id');

        $this->dataset = Dataset::find($datasetId);
    }

    /**
     * @group RV-115
     */
    public function testIndex()
    {
        factory(Protocol::class)->create([
            'user_id' => $this->user
        ]);

        $this->login();

        $this->get('api/protocols')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'user' => [
                        'id',
                        'name'
                    ],
                    'excerpt'
                ]
            ]);
    }

    /**
     * @group RV-18
     */
    public function testShow()
    {
        $this->login();

        $protocolId = $this->post(sprintf('api/datasets/%s/protocols', $this->dataset->id), [
            'name' => 'My test protocol',
            'content' => 'My protocol text content'
        ])
            ->assertStatus(201)
            ->json('id');

        $this->assertDatabaseHas('protocols', [
            'name' => 'My test protocol'
        ]);

        $this->get(sprintf('api/protocols/%s', $protocolId))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'content',
                'created_at',
                'updated_at'
            ]);
    }

    /**
     * @group RV-18
     */
    public function testUpdate()
    {
        $this->login();

        $protocolId = $this->post(sprintf('api/datasets/%s/protocols', $this->dataset->id), [
            'name' => 'My test protocol',
            'content' => 'My protocol text content'
        ])
            ->assertStatus(201)
            ->json('id');

        $this->assertDatabaseHas('protocols', [
            'name' => 'My test protocol'
        ]);

        $this->put(sprintf('api/protocols/%s', $protocolId), [
            'name' => 'My updated test protocol'
        ]);

        $this->assertDatabaseMissing('protocols', [
            'name' => 'My test protocol'
        ]);

        $this->assertDatabaseHas('protocols', [
            'name' => 'My updated test protocol'
        ]);
    }
}
