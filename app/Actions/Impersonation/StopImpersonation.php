<?php

namespace App\Actions\Impersonation;

use App\Exceptions\ImpersonationException;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class StopImpersonation
{
    public function handle(string $reason = 'manual'): User
    {
        throw_unless(session('impersonating'), ImpersonationException::notImpersonating());

        $logId     = session('impersonation_log_id');
        $adminId   = session('original_user_id');
        $profileId = session('original_profile_id');

        ImpersonationLog::where('id', $logId)->update([
            'ended_at'   => now(),
            'end_reason' => $reason,
        ]);

        session()->forget([
            'impersonating',
            'impersonation_log_id',
            'original_user_id',
            'original_profile_id',
            'impersonation_started',
            'impersonation_expires',
        ]);

        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        if ($profileId) {
            session(['active_profile_id' => $profileId]);
            $admin->updateQuietly(['active_profile_id' => $profileId]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $admin;
    }
}
