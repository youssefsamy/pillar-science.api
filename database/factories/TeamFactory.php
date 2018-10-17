<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Models\Team::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->company
    ];
});