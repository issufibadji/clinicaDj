<?php

namespace App\Actions\Clinica\Departments;

use App\Models\Department;

class UpdateDepartmentAction
{
    public function handle(Department $department, string $name, ?string $description, bool $isActive): void
    {
        $department->update([
            'name'        => $name,
            'description' => $description,
            'is_active'   => $isActive,
        ]);
    }
}
