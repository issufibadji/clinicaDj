<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Models\Role;

class UserProfile extends Model implements AuditableContract
{
    use HasUuids, Auditable;

    protected $fillable = [
        'user_id',
        'role_id',
        'display_name',
        'avatar',
        'color',
        'is_default',
        'is_active',
        'last_used_at',
        'settings',
    ];

    protected $casts = [
        'is_default'   => 'boolean',
        'is_active'    => 'boolean',
        'settings'     => 'array',
        'last_used_at' => 'datetime',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDisplayLabelAttribute(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        return $this->role?->name ?? 'Perfil';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return $this->user?->avatarUrl();
    }

    public function getRoleInitialAttribute(): string
    {
        $name = $this->role?->name ?? 'P';
        return strtoupper(mb_substr($name, 0, 1));
    }
}
