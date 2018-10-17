<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Metadata::class, function (Faker $faker) {
    return [
        'key' => $faker->word,
        'value' => $faker->word,
        'dataset_id' => function () {
            return factory(\App\Models\Dataset::class)->create()->id;
        }
    ];
});
