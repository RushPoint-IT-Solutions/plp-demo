<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\RegistrarRequirementDefinition::class, function (Faker $faker) {
    $typeId = App\RegistrarRequirementType::query()->inRandomOrder()->value('id');

    if (!$typeId) {
        $typeId = App\RegistrarRequirementType::query()->create([
            'code' => 'DOCUMENT',
            'name' => 'Document',
        ])->id;
    }

    return [
        'requirement_name' => strtoupper($faker->unique()->words(3, true)),
        'registrar_requirement_type_id' => $typeId,
        'non_filipino_only' => $faker->boolean(20),
        'created_by_user_id' => App\User::query()->inRandomOrder()->value('id'),
    ];
});

$factory->define(App\RegistrarRequirementPolicy::class, function (Faker $faker) {
    $definitionId = App\RegistrarRequirementDefinition::query()->inRandomOrder()->value('id');

    if (!$definitionId) {
        $definitionId = factory(App\RegistrarRequirementDefinition::class)->create()->id;
    }

    $semesterId = App\SystemSchoolSemester::query()->inRandomOrder()->value('id');

    if (!$semesterId) {
        $yearStart = (int) Carbon::now()->format('Y');

        $semesterId = App\SystemSchoolSemester::query()->create([
            'school_year' => $yearStart . '-' . ($yearStart + 1),
            'semester' => 'First Semester',
        ])->id;
    }

    $yearBlockId = App\YearBlock::query()->inRandomOrder()->value('id');

    return [
        'registrar_requirement_definition_id' => $definitionId,
        'system_school_semester_id' => $semesterId,
        'year_block_id' => $faker->boolean(30) ? null : $yearBlockId,
        'created_by_user_id' => App\User::query()->inRandomOrder()->value('id'),
    ];
});

$factory->define(App\StudentRequirementStatus::class, function (Faker $faker) {
    $studentId = App\Student::query()->inRandomOrder()->value('id');
    $policyId = App\RegistrarRequirementPolicy::query()->inRandomOrder()->value('id');

    if (!$policyId) {
        $policyId = factory(App\RegistrarRequirementPolicy::class)->create()->id;
    }

    $isSubmitted = $faker->boolean(70);

    return [
        'student_id' => $studentId,
        'registrar_requirement_policy_id' => $policyId,
        'is_submitted' => $isSubmitted,
        'remarks' => $isSubmitted ? $faker->sentence(6) : 'Pending student submission',
        'date_verified' => $isSubmitted ? Carbon::today()->subDays($faker->numberBetween(0, 30)) : null,
        'verified_by_user_id' => App\User::query()->inRandomOrder()->value('id'),
    ];
});
