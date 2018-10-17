<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\RemoteDirectory::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'last_action_at' => \Carbon\Carbon::now(),
        'secret_key' => $faker->regexify('[a-z0-9]{12}'),
        'computer_id' => $faker->randomElement([
            'Mathieu Computer',
            'Emily Computer',
            'Emily Laptop'
        ]),
        'team_id' => function () {
            return factory(\App\Models\Team::class)->create()->id;
        }
    ];
});
