<?php

use Illuminate\Database\Seeder;

class BrowserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        factory(\App\Models\User::class)->create([
            'name' => 'James the admin',
            'email' => 'admin@pillar.science',
            'permissions' => ['super-admin'],
            'password' => 'secret'
        ]);

        factory(\App\Models\User::class)->create([
            'name' => 'David the user',
            'email' => 'user@pillar.science',
            'password' => 'secret'
        ]);

        $scott = factory(\App\Models\User::class)->create([
            'name' => 'Scott the invited',
            'email' => 'invited@pillar.science',
            'password' => 'secret'
        ]);

        $emily = factory(\App\Models\User::class)->create([
            'name' => 'Emily the collegue',
            'email' => 'emily@pillar.science',
            'password' => 'secret'
        ]);

        factory(\App\Models\JoinInvitation::class)->create([
            'user_id' => $scott->id,
            'token' => '94a08da1fecbb6e8b46990538c7b50b2',
            'expires_at' => \Carbon\Carbon::now()->addHours(24)
        ]);

        $team = factory(\App\Models\Team::class)->create([
            'name' => 'Pillar Science Test Labs'
        ]);

        $emily->teams()->attach($team, [
            'role' => 'user'
        ]);

        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        $project = $projectManager->create($team, $emily, [
            'name' => 'My first project'
        ]);

        $anotherProject = $projectManager->create(
            factory(\App\Models\Team::class)->create(),
            factory(\App\Models\User::class)->create(),
            [
                'name' => 'Photo project'
            ]
        );

        $manager = app(\App\Services\Datasets\DatasetManager::class);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('metadata.png'),
            $project->directory
        );

        $metadata = factory(\App\Models\Metadata::class)->make([
            'key' => 'planet',
            'value' => 'uranus'
        ]);

        $dataset->metadata()->save($metadata);

        /** @var \App\Models\Dataset $dataset */
        list($dataset) = $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('protocol.png'),
            $project->directory
        );

        $documentsDirectory = $manager->createDirectory([
            'name' => 'documents'
        ], $project->directory);

        $alpha = $manager->createDirectory([
            'name' => 'alpha'
        ], $documentsDirectory);

        $beta = $manager->createDirectory([
            'name' => 'beta'
        ], $documentsDirectory);

        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('alpha.png'),
            $alpha
        );

        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('beta.png'),
            $beta
        );

        factory(\App\Models\Protocol::class)->times(20)->create([
            'user_id' => $emily->id
        ]);

        $protocol = factory(\App\Models\Protocol::class)->make([
            'name' => 'Master test protocol',
            'user_id' => $emily->id
        ]);

        $dataset->protocols()->save($protocol);

        foreach (range(1, 5) as $i) {
            $manager->storeAndCreateUploadedFile(
                \Illuminate\Http\UploadedFile::fake()->image($faker->name . '.png'),
                $project->directory
            );
        }

        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('umpossible.png'),
            $anotherProject->directory
        );

        /** @var \App\Models\RemoteDirectory $remoteDirectory */
        $remoteDirectory = factory(\App\Models\RemoteDirectory::class)->create([
            'name' => 'Windows 10 in room 404',
            'team_id' => $team->id,
            'secret_key' => 'zo9tsyetbk4n'
        ]);

        $maxime = $manager->createDirectory(['name' => 'Maxime'], $remoteDirectory->directory);
        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('maxime.png'),
            $maxime
        );

        $angelique = $manager->createDirectory(['name' => 'Angélique'], $remoteDirectory->directory);
        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->image('resultats.png'),
            $angelique
        );

        $elise = $manager->createDirectory(['name' => 'Élise'], $remoteDirectory->directory);
        $manager->storeAndCreateUploadedFile(
            \Illuminate\Http\UploadedFile::fake()->create('list.csv', 50),
            $elise
        );

        $manager->createMapping($remoteDirectory->directory, $project->directory);
    }
}
