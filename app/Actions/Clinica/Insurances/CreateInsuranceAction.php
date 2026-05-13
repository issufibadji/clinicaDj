<?php

namespace App\Actions\Clinica\Insurances;

use App\Models\Insurance;

class CreateInsuranceAction
{
    public function handle(string $name, string $planType, ?string $contactPhone): Insurance
    {
        return Insurance::create([
            'name'          => $name,
            'plan_type'     => $planType,
            'contact_phone' => $contactPhone,
            'is_active'     => true,
        ]);
    }
}
