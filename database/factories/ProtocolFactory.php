<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Protocol::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'content' => $faker->paragraphs(5, true),
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        }
    ];
});
