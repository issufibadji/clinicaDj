<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements AuditableContract, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, HasRoles, Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // Campos auditados
    protected $auditInclude = [
        'name',
        'email',
        'phone',
        'avatar',
        'is_active',
        'two_factor_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'two_factor_confirmed_at'  => 'datetime',
            'password'                 => 'hashed',
            'two_factor_secret'        => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'is_active'                => 'boolean',
        ];
    }

    // ── 2FA helpers ──────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function getRecoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode($this->two_factor_recovery_codes, true) ?? [];
    }

    public function storeRecoveryCodes(array $hashedCodes): void
    {
        $this->update([
            'two_factor_recovery_codes' => json_encode($hashedCodes),
        ]);
    }

    public function validateAndConsumeRecoveryCode(string $plainCode): bool
    {
        $codes = $this->getRecoveryCodes();

        foreach ($codes as $index => $hashed) {
            if (Hash::check($plainCode, $hashed)) {
                unset($codes[$index]);
                $this->storeRecoveryCodes(array_values($codes));
                return true;
            }
        }

        return false;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function doctor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function sentMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class, 'from_user_id');
    }

    public function receivedMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class, 'to_user_id');
    }

    // ── Profile helpers ──────────────────────────────────────────────────────

    public function avatarUrl(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    public function initials(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }
}
