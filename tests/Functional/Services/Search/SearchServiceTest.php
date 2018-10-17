<?php

namespace Functional\Services\Search;

use App\DatabaseMigrations;
use App\Models\Dataset;
use App\Models\Metadata;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Datasets\DatasetManager;
use App\Services\Projects\ProjectManager;
use App\Services\Search\SearchService;
use App\TestCase;

class SearchServiceTest extends TestCase
{
    use DatabaseMigrations;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /** @var User */
    private $user;
    /** @var User */
    private $anotherUser;

    /** @var Team */
    private $team;

    /** @var Project */
    private $project;
    /** @var Project */
    private $anotherProject;

    /** @var SearchService */
    private $service;

    /** @var DatasetManager */
    private $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'name' => 'Basic User',
            'email' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $this->anotherUser = factory(User::class)->create([
            'name' => 'Another User'
        ]);

        $this->team = factory(Team::class)->create([
            'name' => 'Laboratoire de tests fonctionnels sur les projets'
        ]);

        $this->team->admins()->save($this->user);

        /** @var ProjectManager $projectManager */
        $projectManager = app(ProjectManager::class);

        $this->project = $projectManager->create($this->team, $this->user, [
            'name' => 'Dataset testing project'
        ]);

        $this->anotherProject = $projectManager->create(factory(Team::class)->create(), $this->anotherUser, [
            'name' => 'Another dataset testing project',
        ]);

        $this->team->projects()->save($this->project);

        $this->manager = app(\App\Services\Datasets\DatasetManager::class);

        $filenames = [
            'accumulation',
            'anniversary',
            'incongruous',
            'machinery',
            'legislation',
        ];

        $filenames2 = [
            'machinery',
            'legislation',
            'population',
            'environmental'
        ];

        foreach ($filenames as $f) {
            $this->manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image($f . '.png'),
                $this->project->directory
            );
        }

        foreach ($filenames2 as $f) {
            $this->manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image($f . '.png'),
                $this->anotherProject->directory
            );
        }

        $this->service = app(SearchService::class);
    }

    /**
     * @group RV-47
     */
    public function testSearch()
    {
        $this->manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('epsilon.png'),
            $this->project->directory
        );

        $results = $this->service->search('epsilon', $this->user);

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('resource', $results[0]);
        $this->assertArrayHasKey('type', $results[0]);
        $this->assertEquals('Dataset', $results[0]['type']);
    }

    /**
     * @group RV-47
     * @dataProvider datasetProvider
     */
    public function testSearchDatasets(array $attributes, string $query, bool $expectedFound)
    {
        $alpha = factory(Dataset::class)->create(array_merge($attributes, [
            'project_id' => $this->project->id
        ]));

        $results = $this->service->searchDatasets($query, $this->user);

        $this->assertEquals($expectedFound, $results->contains('id', $alpha->id));
    }

    /**
     * @group RV-47
     * @group active
     */
    public function testSearchDatasetCount()
    {
        foreach (range(1, 20) as $i) {
            $this->manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image(sprintf('omega-%s.png', $i)),
                $this->project->directory
            );
        }

        $results = $this->service->searchDatasets('omega', $this->user);

        $this->assertCount(20, $results);
    }

    /**
     * @group RV-47
     * @dataProvider lookupProvider
     */
    public function testSearchUserIndex(string $query, int $userId, int $expectedCount)
    {
        $results = Dataset::searchForUser($query, User::find($userId))->get();

        $this->assertCount($expectedCount, $results);
    }

    /**
     * @group RV-47
     */
    public function testSearchDatasetWithMetadataMatchValue()
    {
        /** @var Dataset $dataset */
        list($dataset) = $this->manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('elephant.png'),
            $this->project->directory
        );

        /** @var Metadata $metadata */
        $metadata = factory(Metadata::class)->make([
            'key' => 'shape',
            'value' => 'circle'
        ]);

        $dataset->metadata()->save($metadata);

        $results = $this->service->searchDatasets('circle', $this->user);
        $this->assertCount(1, $results);
    }

    /**
     * @group RV-47
     */
    public function testSearchDatasetWithMetadataMatchMultipleWithValue()
    {
        foreach (range(1, 3) as $i) {
            /** @var Dataset $dataset */
            list($dataset) = $this->manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image(sprintf('elephant%s.png', $i)),
                $this->project->directory
            );

            /** @var Metadata $metadata */
            $metadata = factory(Metadata::class)->make([
                'key' => 'shape',
                'value' => 'square'
            ]);

            $dataset->metadata()->save($metadata);
        }

        $results = $this->service->searchDatasets('square', $this->user);
        $this->assertCount(3, $results);
    }

    /**
     * @group RV-47
     */
    public function testSearchDatasetWithMetadataMatchKey()
    {
        /** @var Dataset $dataset */
        list($dataset) = $this->manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('elephant.png'),
            $this->project->directory
        );

        /** @var Metadata $metadata */
        $metadata = factory(Metadata::class)->make([
            'key' => 'shape',
            'value' => 'circle'
        ]);

        $dataset->metadata()->save($metadata);

        $results = $this->service->searchDatasets('shape', $this->user);
        $this->assertCount(1, $results);
    }

    /**
     * @group RV-47
     * @group active
     */
    public function testSearchDatasetWithMetadataMatchMultipleWithKey()
    {
        foreach (range(1, 3) as $i) {
            /** @var Dataset $dataset */
            list($dataset) = $this->manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image('elephant.png'),
                $this->project->directory
            );

            /** @var Metadata $metadata */
            $metadata = factory(Metadata::class)->make([
                'key' => 'shape',
                'value' => 'square'
            ]);

            $dataset->metadata()->save($metadata);
        }

        $results = $this->service->searchDatasets('shape', $this->user);
        $this->assertCount(3, $results);
    }

    public function lookupProvider()
    {
        $tests =  [
            ['accumulation', 1, 1],
            ['accumulation', 2, 0],
            ['legislation', 1, 1],
            ['legislation', 2, 1],
            ['environmental', 1, 0],
            ['environmental', 2, 1],
        ];

        return $this->nameDataProviderTests($tests);
    }

    public function datasetProvider()
    {
        $tests = [
            [['name' => 'alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'alphabetical', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'beticalalpha', 'type' => 'dataset'], 'alpha', false],
            [['name' => 'beticalalphaomega', 'type' => 'dataset'], 'alpha', false],
            [['name' => 'betical-alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'betical,alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'betical.alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'betical/alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'betical alpha', 'type' => 'dataset'], 'alpha', true],
            [['name' => 'betical alpha omega', 'type' => 'dataset'], 'alpha', true],
        ];

        return $this->nameDataProviderTests($tests);
    }
}
