<?php

namespace App\Actions\Clinica\Departments;

use App\Models\Department;
use Illuminate\Validation\ValidationException;

class DeleteDepartmentAction
{
    public function handle(Department $department): void
    {
        if ($department->rooms()->exists() || $department->doctors()->exists()) {
            throw ValidationException::withMessages([
                'department' => 'Não é possível excluir: existem salas ou médicos vinculados a este departamento.',
            ]);
        }

        $department->delete();
    }
}
