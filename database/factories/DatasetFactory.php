<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Dataset::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'project_id' => function () {
            return factory(\App\Models\Project::class)->create()->id;
        }
    ];
});
