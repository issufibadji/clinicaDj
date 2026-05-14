<?php

namespace App\Actions\Impersonation;

use App\Exceptions\ImpersonationException;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class StartImpersonation
{
    public function handle(User $admin, User $target, ?string $notes = null): ImpersonationLog
    {
        throw_if(! $admin->hasRole('admin'), ImpersonationException::notAuthorized());
        throw_if($target->hasRole('admin'), ImpersonationException::cannotImpersonateAdmin());
        throw_if($admin->id === $target->id, ImpersonationException::cannotImpersonateSelf());
        throw_if(session('impersonating'), ImpersonationException::alreadyImpersonating());

        $log = ImpersonationLog::create([
            'admin_id'         => $admin->id,
            'target_id'        => $target->id,
            'started_at'       => now(),
            'admin_ip'         => request()->ip(),
            'admin_user_agent' => request()->userAgent(),
            'notes'            => $notes,
        ]);

        session([
            'impersonating'         => true,
            'impersonation_log_id'  => $log->id,
            'original_user_id'      => $admin->id,
            'original_profile_id'   => $admin->active_profile_id,
            'impersonation_started' => now()->timestamp,
            'impersonation_expires' => now()->addHours(2)->timestamp,
        ]);

        Auth::login($target);

        if ($target->active_profile_id) {
            session(['active_profile_id' => $target->active_profile_id]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $log;
    }
}
