<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\DesktopClient::class, function (Faker $faker) {
    return [
        'disk' => 'local',
        'path' => 'pillar-science.zip',
        'size' => $faker->numberBetween(5000, 3000000000),
    ];
});
