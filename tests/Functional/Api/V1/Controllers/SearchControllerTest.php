<?php

namespace Functional\Api\V1\Controllers;

use App\Models\Dataset;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $user;

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;
    /** @var Project */
    private $anotherProject;

    /** @var Dataset A dataset in a project */
    private $dataset;
    /** @var Dataset A dataset in a project */
    private $dataset2;

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'Basic User',
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $anotherUser = factory(User::class)->create([
            'name' => 'Another user',
            'email' => 'another@pillar.science',
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

        $this->anotherProject = $projectManager->create($this->team, $anotherUser, [
            'name' => 'Another testing project'
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

        $this->login('another@pillar.science', self::PASSWORD);

        $datasetId = $this->post(sprintf('api/datasets/%s/upload', $this->anotherProject->directory->id), [
            'file' => UploadedFile::fake()->create('test.docx')
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test.docx',
                'type' => 'dataset'
            ])
            ->json('id');

        $this->dataset2 = Dataset::find($datasetId);
    }

    /**
     * @group RV-47
     */
    public function testSearch()
    {
        $this->login();

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    /**
     * @group RV-47
     */
    public function testSearchNoResult()
    {
        $this->login();

        $this->get('/api/search?query=umpossible')
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    /**
     * @group RV-114
     */
    public function testSearchWithSharing()
    {
        $this->login();

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->anotherProject->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    /**
     * @group RV-114
     */
    public function testSearchStopSharing()
    {
        $this->login();

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->anotherProject->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(2);

        $this->anotherProject->stopSharingWith($this->user);

        $this->get('/api/search?query=test')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }
}
