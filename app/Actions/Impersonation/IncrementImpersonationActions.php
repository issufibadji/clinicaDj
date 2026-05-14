<?php

namespace App\Actions\Impersonation;

use App\Models\ImpersonationLog;

class IncrementImpersonationActions
{
    public function handle(): void
    {
        if ($logId = session('impersonation_log_id')) {
            ImpersonationLog::where('id', $logId)->increment('actions_count');
        }
    }
}
