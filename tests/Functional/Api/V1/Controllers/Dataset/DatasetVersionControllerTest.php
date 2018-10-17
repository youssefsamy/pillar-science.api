<?php

namespace Functional\Api\V1\Controllers\Dataset;

use App\DatabaseMigrations;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Projects\ProjectManager;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DatasetVersionControllerTest extends TestCase
{
    use DatabaseMigrations;

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

        /** @var ProjectManager $projectManager */
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $user, [
            'name' => 'Dataset testing project',
        ]);

        $this->team->projects()->save($this->project);

        $this->login();

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
     * @group RV-114
     * @group active
     */
    public function testStore()
    {
        $this->login();

        $this->assertEquals(1, $this->dataset->versions()->count());

        $this->post(sprintf('api/datasets/%s/dataset-versions', $this->dataset->id), [
            'file' => UploadedFile::fake()->create('test2.txt')
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test.txt'
            ]);

        $this->assertEquals(2, $this->dataset->versions()->count());
    }

    /**
     * @group RV-114
     * @group active
     */
    public function testPreview()
    {
        $this->login();

        $this->get(sprintf('api/datasets/%s/dataset-versions/%s/preview', $this->dataset->id, $this->dataset->currentVersion->id))
            ->assertStatus(200);
    }
}
