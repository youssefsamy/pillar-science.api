<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Models\JoinInvitation::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'token' => $faker->md5,
        'expires_at' => \Carbon\Carbon::now()->addDays(7)
    ];
});