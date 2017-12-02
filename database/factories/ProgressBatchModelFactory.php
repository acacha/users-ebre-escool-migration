<?php


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Acacha\UsersEbreEscoolMigration\Models\ProgressBatch::class, function (Faker\Generator $faker) {
    return [
        'accomplished' => $id = $faker->unique()->numberBetween($min = 1, $max = 10000),
        'incidences' => $id = $faker->unique()->numberBetween($min = 1, $max = 10000),
        'state' => $faker->randomElement(['pending','finished','stopped']),
        'type' => 'App\User'
    ];
});
