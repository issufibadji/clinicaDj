<?php

namespace App\Policies;

use App\Models\Insurance;
use App\Models\User;

class InsurancePolicy
{
    public function viewAny(User $user): bool { return $user->can('insurance.view'); }
    public function view(User $user, Insurance $insurance): bool { return $user->can('insurance.view'); }
    public function create(User $user): bool { return $user->can('insurance.manage'); }
    public function update(User $user, Insurance $insurance): bool { return $user->can('insurance.manage'); }
    public function delete(User $user, Insurance $insurance): bool { return $user->can('insurance.manage'); }
}
