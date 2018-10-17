<?php

namespace Functional\Api\V1\Controllers\Dataset;

use App\Models\Dataset;
use App\Models\Metadata;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class MetadataControllerTest extends TestCase
{
    use RefreshDatabase;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;

    /** @var Dataset A dataset in a project */
    private $dataset;

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

        $this->project = factory(Project::class)->create([
            'name' => 'Dataset testing project',
            'created_by' => $user->id
        ]);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $user, [
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
     * @group RV-15
     */
    public function testStore()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $payload = [
            'key' => 'author',
            'value' => 'Mathieu Tanguay'
        ];

        $this->assertDatabaseMissing('metadata', $payload);
        $this->post(sprintf('api/datasets/%s/metadata', $this->dataset->id), $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'key',
                'value',
                'created_at',
                'updated_at'
            ]);
        $this->assertDatabaseHas('metadata', $payload);
    }

    /**
     * @group RV-15
     */
    public function testIndex()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        /** @var Metadata $metadata */
        factory(Metadata::class)->create([
            'dataset_id' => $this->dataset->id
        ]);

        $this->get(sprintf('api/datasets/%s/metadata', $this->dataset->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'key',
                    'value',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }
}
