<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\ChatMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Insurance;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\ChatMessagePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\EventPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InsurancePolicy;
use App\Policies\PatientPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\RoomPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Insurance::class, InsurancePolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Doctor::class, DoctorPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(ChatMessage::class, ChatMessagePolicy::class);

        // admin bypassa todas as gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}
