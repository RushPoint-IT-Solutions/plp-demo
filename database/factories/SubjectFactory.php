<?php

use Faker\Generator as Faker;

$factory->define(App\Subject::class, function (Faker $faker) {
    $codeNumber = $faker->unique()->numberBetween(10000, 99999);
    $lec = $faker->randomElement([1, 2, 3]);
    $lab = $faker->randomElement([0, 1, 2, 3]);

    return [
        'code' => 'SJF-' . $codeNumber,
        'name' => ucfirst($faker->words($faker->numberBetween(2, 4), true)),
        'units' => (float) ($lec + $lab),
        'is_subject_file_record' => true,
        'lec' => $lec,
        'lab' => $lab,
        'is_core' => false,
        'is_applied' => false,
        'is_specialized' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ];
});

$factory->state(App\Subject::class, 'subject_file', function (Faker $faker) {
    $core = $faker->boolean(35);
    $applied = !$core && $faker->boolean(40);

    return [
        'is_subject_file_record' => true,
        'is_core' => $core,
        'is_applied' => $applied,
        'is_specialized' => !$core && !$applied,
    ];
});
