<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonationLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'admin_id',
        'target_id',
        'started_at',
        'ended_at',
        'end_reason',
        'admin_ip',
        'admin_user_agent',
        'actions_count',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getDurationAttribute(): ?string
    {
        $end = $this->ended_at ?? now();
        $minutes = (int) $this->started_at->diffInMinutes($end);

        if ($minutes < 1) {
            return 'menos de 1 min';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $h > 0 ? "{$h}h {$m}min" : "{$m}min";
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->ended_at === null;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('ended_at');
    }

    public function scopeByAdmin(Builder $q, string $adminId): Builder
    {
        return $q->where('admin_id', $adminId);
    }
}
