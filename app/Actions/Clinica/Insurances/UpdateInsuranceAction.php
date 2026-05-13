<?php

namespace App\Actions\Clinica\Insurances;

use App\Models\Insurance;

class UpdateInsuranceAction
{
    public function handle(Insurance $insurance, string $name, string $planType, ?string $contactPhone, bool $isActive): void
    {
        $insurance->update([
            'name'          => $name,
            'plan_type'     => $planType,
            'contact_phone' => $contactPhone,
            'is_active'     => $isActive,
        ]);
    }
}
