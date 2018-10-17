<?php

namespace App\Functional\Api\V1\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\RemoteDirectory;
use App\Models\Team;
use App\Models\User;
use App\Services\Datasets\DatasetManager;
use App\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DatasetControllerTest extends TestCase
{
    use RefreshDatabase;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;

    /** @var RemoteDirectory */
    private $remoteDirectory;

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
            'name' => 'Laboratoire de tests fonctionnels sur les projets'
        ]);

        $this->team->admins()->save($this->user);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Dataset testing project',
        ]);

        $this->team->projects()->save($this->project);

        $this->remoteDirectory = factory(RemoteDirectory::class)->create([
            'name' => 'Dataset testing remote directory',
            'team_id' => $this->team->id
        ]);
    }

    /**
     * @group RV-8
     */
    public function testShowAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/datasets/%s', $this->project->directory->id))
            ->assertStatus(200)
            ->assertJsonFragment([
                'type' => 'directory',
                'name' => 'Dataset testing project'
            ]);
    }

    /**
     * @group RV-53
     */
    public function testShowAsRemoteDirectoryPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->get(sprintf('api/datasets/%s', $this->remoteDirectory->directory->id))
            ->assertStatus(200)
            ->assertJsonFragment([
                'type' => 'directory',
                'name' => 'Dataset testing remote directory'
            ]);
    }

    /**
     * @group RV-8
     */
    public function testCreateDirectoryAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/create-directory', $this->project->directory->id), [
            'name' => 'testCreateDirectoryAsProjectPerspective'
        ])->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'testCreateDirectoryAsProjectPerspective'
            ]);
    }

    /**
     * @group RV-53
     */
    public function testCreateDirectoryAsRemoteDirectoryPerspectiveWithoutSecretKeyNotAllowed()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/create-directory', $this->remoteDirectory->directory->id), [
            'name' => 'testCreateDirectoryAsRemoteDirectoryPerspectiveWithoutSecretKeyNotAllowed'
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-53
     */
    public function testCreateDirectoryAsRemoteDirectoryPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/create-directory', $this->remoteDirectory->directory->id), [
            'name' => 'testCreateDirectoryAsRemoteDirectoryPerspective',
            'secret_key' => $this->remoteDirectory->secret_key,
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'testCreateDirectoryAsRemoteDirectoryPerspective'
            ])
            ->json();
    }

    /**
     * @group RV-8
     */
    public function testCreateDirectoryAsProjectPerspectiveInvalidName()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/create-directory', $this->project->directory->id))
            ->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'message',
                    'errors' => [
                        'name'
                    ]
                ]
            ]);
    }

    /**
     * @group RV-8
     */
    public function testUploadAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id), [
            'file' => UploadedFile::fake()->create('test.txt')
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test.txt',
                'type' => 'dataset'
            ]);
    }

    /**
     * @group RV-53
     */
    public function testUploadAsRemoteDirectoryPerspectiveWithoutSecretKeyNotAllowed()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/upload', $this->remoteDirectory->directory->id), [
            'file' => UploadedFile::fake()->create('test.txt')
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-8
     */
    public function testUploadAsRemoteDirectoryPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id), [
            'secret_key' => $this->remoteDirectory->secret_key,
            'file' => UploadedFile::fake()->create('test.txt')
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test.txt',
                'type' => 'dataset'
            ]);
    }

    /**
     * @group RV-8
     */
    public function testUploadAsProjectPerspectiveInvalidFileRequired()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id))
            ->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'message',
                    'errors' => [
                        'file'
                    ]
                ]
            ]);
    }

    /**
     * @group RV-8
     */
    public function testUploadAsProjectPerspectiveInvalidNotAFile()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id), [
            'file' => 'not a file'
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'message',
                    'errors' => [
                        'file'
                    ]
                ]
            ]);
    }

    /**
     * @group RV-8
     * @depends testCreateDirectoryAsProjectPerspective
     */
    public function testUpdateAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/update', $this->project->directory->id), [
            'name' => 'new directory name'
        ])
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'new directory name'
            ]);
    }

    /**
     * @group RV-17
     * @depends testCreateDirectoryAsProjectPerspective
     */
    public function testUpdateContentAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $manager = app(DatasetManager::class);

        list($dataset) = $manager->createOrUpdateDataset(UploadedFile::fake()->create('test.txt'), 'Test file.txt', $this->project->directory);

        $this->post(sprintf('api/datasets/%s/update', $dataset->id), [
            'content' => 'Je suis une patate'
        ])
            ->assertStatus(200);

        $dataset->refresh();

        $this->assertCount(2, $dataset->versions);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testUpdateAsRemoteDirectoryPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/update', $this->remoteDirectory->directory->id), [
            'name' => 'new directory name for remote directory'
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testUpdateAsRemoteDirectoryPerspectiveWithNewFileWithoutSecretKeyNotAllowed()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->post(sprintf('api/datasets/%s/update', $this->remoteDirectory->directory->id), [
            'name' => 'new directory name for remote directory',
            'file' => UploadedFile::fake()->create('test.txt')
        ])
            ->assertStatus(403);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testUpdateAsRemoteDirectoryPerspectiveWithNewFile()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        list($dataset) = $manager->storeAndCreateUploadedFile(
            UploadedFile::fake()->create('base.txt'),
            $this->remoteDirectory->directory
        );

        $this->assertCount(1, $dataset->versions);

        $this->post(sprintf('api/datasets/%s/update', $dataset->id), [
            'secret_key' => $this->remoteDirectory->secret_key,
            'file' => UploadedFile::fake()->create('updated.txt')
        ])
            ->assertStatus(200)
            ->json();

        $dataset->refresh();

        $this->assertCount(2, $dataset->versions);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testUpdateAsRemoteDirectoryPerspectiveWithNewFileAndRename()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        list($dataset) = $manager->storeAndCreateUploadedFile(
            UploadedFile::fake()->create('base.txt'),
            $this->remoteDirectory->directory
        );

        $this->assertCount(1, $dataset->versions);

        $this->post(sprintf('api/datasets/%s/update', $dataset->id), [
            'secret_key' => $this->remoteDirectory->secret_key,
            'file' => UploadedFile::fake()->create('updated.txt'),
            'name' => 'New name'
        ])
            ->assertStatus(200)
            ->json();

        $dataset->refresh();

        $this->assertCount(3, $dataset->versions);
        $this->assertEquals('New name', $dataset->name);
    }

    /**
     * @group RV-8
     * @depends testCreateDirectoryAsProjectPerspective
     */
    public function testDestroyDirectoryAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $manager = app(DatasetManager::class);

        $directory = $manager->createDirectory([
            'name' => 'garbage'
        ], $this->project->directory);

        $this->assertDatabaseHas('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/datasets/%s', $directory->id))
            ->assertStatus(200);

        $this->assertDatabaseMissing('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testDestroyDirectoryAsRemoteDirectoryPerspectiveWithoutSecretKeyNotAllowed()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $manager = app(DatasetManager::class);

        $directory = $manager->createDirectory([
            'name' => 'garbage'
        ], $this->remoteDirectory->directory);

        $this->assertDatabaseHas('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/datasets/%s', $directory->id))
            ->assertStatus(403);

        $this->assertDatabaseHas('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);
    }

    /**
     * @group RV-53
     * @depends testCreateDirectoryAsRemoteDirectoryPerspective
     */
    public function testDestroyDirectoryAsRemoteDirectoryPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $manager = app(DatasetManager::class);

        $directory = $manager->createDirectory([
            'name' => 'garbage'
        ], $this->remoteDirectory->directory);

        $this->assertDatabaseHas('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/datasets/%s', $directory->id), [
            'secret_key' => $this->remoteDirectory->secret_key
        ])
            ->assertStatus(200);

        $this->assertDatabaseMissing('datasets', [
            'id' => $directory->id,
            'deleted_at' => null
        ]);
    }

    /**
     * @group RV-8
     * @depends testCreateDirectoryAsProjectPerspective
     */
    public function testDestroyDatasetAsProjectPerspective()
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $manager = app(DatasetManager::class);

        list($dataset) = $manager->storeAndCreateUploadedFile(
            UploadedFile::fake()->create('destroy.txt'),
            $this->project->directory
        );

        \Storage::assertExists($dataset->path);

        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id,
            'deleted_at' => null
        ]);

        $this->delete(sprintf('api/datasets/%s', $dataset->id))
            ->assertStatus(200);

        $this->assertDatabaseMissing('datasets', [
            'id' => $dataset->id,
            'deleted_at' => null
        ]);

        \Storage::assertExists($dataset->path);
    }

    /**
     * @group RV-54
     * @dataProvider fileNameProvider
     */
    public function testPreview($filename, $expectedMimeType)
    {
        $this->login();

        $content = $this->post(sprintf('api/datasets/%s/upload', $this->project->directory->id), [
            'file' => UploadedFile::fake()->create($filename)
        ])
            ->assertStatus(201)
            ->json();

        $this->get(sprintf('api/datasets/%s/preview', $content['id']))
            ->assertHeader('Content-Type', $expectedMimeType)
            ->assertStatus(200);
    }

    /**
     * @group RV-18
     */
    public function testAncestors()
    {
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

        $this->get(sprintf('api/datasets/%s/ancestors', $datasetId))
            ->assertStatus(200);
    }

    public function fileNameProvider()
    {

        $tests = [
            ['text.txt', 'text/plain; charset=UTF-8'],
            ['photo.jpg', 'image/jpeg'],
            ['image.png', 'image/png'],
            ['pillar.mp4', 'video/mp4'],
            ['backup.zip', 'application/zip'],
            ['index.html', 'text/html; charset=UTF-8'],
            ['guide.pdf', 'application/pdf'],
            ['sheet.csv', 'application/json']
        ];

        return $this->nameDataProviderTests($tests);
    }

    /**
     * @group RV-94
     */
    public function testMoveDataset()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $targetDirectory = $manager->createDirectory(['name' => 'move-to-me'], $this->project->directory);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $targetDirectory->id))
            ->assertJsonFragment(['parent_id' => $targetDirectory->id]);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetAsContributor()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $targetDirectory = $datasetManager->createDirectory(['name' => 'move-to-me'], $this->project->directory);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $targetDirectory->id))
            ->assertJsonFragment(['parent_id' => $targetDirectory->id]);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetAsViewer()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $targetDirectory = $datasetManager->createDirectory(['name' => 'move-to-me'], $this->project->directory);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $targetDirectory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetFromProjectAsContributorToAnotherProjectAsContributor()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();
        $anotherProject->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $anotherProject->directory->id))
            ->assertJsonFragment(['parent_id' => $anotherProject->directory->id]);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetFromProjectAsContributorToAnotherProjectAsViewer()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();
        $anotherProject->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $anotherProject->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetFromProjectAsViewerToAnotherProjectAsViewer()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();
        $anotherProject->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $anotherProject->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetFromProjectAsContributorToRemoteDirectory()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $this->remoteDirectory->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetFromRemoteDirectoryToProjectAsContributor()
    {
        /** @var DatasetManager $datasetManager */
        $datasetManager = app(DatasetManager::class);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $datasetManager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $this->remoteDirectory->directory->id, $dataset->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetToSameLocation()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $this->project->directory->id))
            ->assertStatus(204);
    }

    /**
     * @group RV-94
     */
    public function testMoveDatasetToADatasetThatIsNotADirectoryIsNotAllowed()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        /** @var \App\Models\Dataset $dataset2 */
        list($dataset2) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $this->project->directory
        );

        $this->login();

        $this->put(sprintf('/api/datasets/%s/move-to/%s', $dataset->id, $dataset2->id))
            ->assertStatus(422);
    }

    /**
     * @group RV-93
     */
    public function testMapDataset()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $source = $manager->createDirectory(['name' => 'map-me'], $this->remoteDirectory->directory);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $this->project->directory->id))
            ->assertJsonFragment([
                'parent_id' => $this->project->directory->id,
                'type' => 'symlink'
            ]);
    }

    /**
     * @group RV-93
     */
    public function testMapDatasetAsContributor()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $source = $manager->createDirectory(['name' => 'map-me'], $this->remoteDirectory->directory);

        $this->project->shareWith($this->user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $this->project->directory->id))
            ->assertJsonFragment([
                'parent_id' => $this->project->directory->id,
                'type' => 'symlink'
            ]);
    }

    /**
     * @group RV-93
     */
    public function testMapDatasetAsViewer()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $source = $manager->createDirectory(['name' => 'map-me'], $this->remoteDirectory->directory);

        $this->project->shareWith($this->user, ProjectUser::ROLE_VIEWER);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $this->project->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-93
     */
    public function testMapDatasetToAnotherProjectIsNotAllowed()
    {
        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();

        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $source = $manager->createDirectory(['name' => 'map-me'], $this->remoteDirectory->directory);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $anotherProject->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-93
     */
    public function testMapDatasetFromAnotherRemoteDirectoryIsNotAllowed()
    {
        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();

        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $anotherRemoteDirectory = factory(RemoteDirectory::class)->create([
            'name' => 'Another Dataset testing remote directory',
            'team_id' => factory(Team::class)->create()->id
        ]);

        $source = $manager->createDirectory(['name' => 'map-me'], $anotherRemoteDirectory->directory);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $anotherProject->directory->id))
            ->assertStatus(403);
    }

    /**
     * @group RV-93
     */
    public function testMapDatasetTwiceToTheSameTargetDatasetIsNotAllowed()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $source = $manager->createDirectory(['name' => 'map-me'], $this->remoteDirectory->directory);

        $this->login();

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $this->project->directory->id))
            ->assertJsonFragment([
                'parent_id' => $this->project->directory->id,
                'type' => 'symlink'
            ]);

        $this->post(sprintf('/api/datasets/%s/map-to/%s', $source->id, $this->project->directory->id))
            ->assertStatus(422);
    }

    /**
     * @group RV-94
     */
    public function testTreeView()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        $manager->createDirectory(['name' => 'My SubDirectory'], $this->project->directory);

        $this->login();

        $this->get(sprintf('/api/datasets/%s/tree', $this->project->directory->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'parent_id',
                    'size',
                    'type',
                    'created_at',
                    'updated_at',
                    'name',
                    'mime_type',
                    'author',
                    'current_version'
                ]
            ]);
    }

    /**
     * @group RV-94
     */
    public function testTreeViewForADatasetCantView()
    {
        /** @var DatasetManager $manager */
        $manager = app(DatasetManager::class);

        /** @var Project $anotherProject */
        $anotherProject = factory(Project::class)->create();

        $manager->createDirectory(['name' => 'My SubDirectory'], $anotherProject->directory);

        $this->login();

        $this->get(sprintf('/api/datasets/%s/tree', $anotherProject->directory->id))
            ->assertStatus(403);
    }
}
