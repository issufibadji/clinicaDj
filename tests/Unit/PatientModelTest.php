<?php

use App\Models\Patient;

test('age returns correct age for patient born 30 years ago', function () {
    $patient = Patient::factory()->make([
        'birth_date' => now()->subYears(30)->format('Y-m-d'),
    ]);

    expect($patient->age())->toBe(30);
});

test('age returns 0 for patient born today', function () {
    $patient = Patient::factory()->make([
        'birth_date' => now()->format('Y-m-d'),
    ]);

    expect($patient->age())->toBe(0);
});

test('age accounts for birthday not yet reached this year', function () {
    // Born on December 31 — if today is before Dec 31, still one year less
    $birthDate = now()->subYears(25)->endOfYear()->format('Y-m-d');
    $patient   = Patient::factory()->make(['birth_date' => $birthDate]);

    // Age should be 24 or 25 depending on whether Dec 31 has passed
    $expected = now()->gte(now()->setDateFrom($patient->birth_date)->setYear(now()->year)) ? 25 : 24;

    expect($patient->age())->toBe($expected);
});
