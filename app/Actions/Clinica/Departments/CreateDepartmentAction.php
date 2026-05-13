<?php

namespace App\Actions\Clinica\Departments;

use App\Models\Department;

class CreateDepartmentAction
{
    public function handle(string $name, ?string $description): Department
    {
        return Department::create([
            'name'        => $name,
            'description' => $description,
            'is_active'   => true,
        ]);
    }
}
