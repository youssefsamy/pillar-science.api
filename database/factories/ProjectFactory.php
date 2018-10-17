<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Models\Project::class, function (Faker\Generator $faker) {
    return [
        'name' => sprintf('%s %s', $faker->colorName, $faker->city),
        'description' => $faker->paragraphs(3, true),
        'team_id' => function () {
            return factory(\App\Models\Team::class)->create()->id;
        },
        'created_by' => function () {
            return factory(\App\Models\User::class)->create()->id;
        }
    ];
});