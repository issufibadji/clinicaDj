<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Patient extends Model implements AuditableContract
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = ['name', 'cpf', 'birth_date', 'phone', 'email', 'address', 'insurance_id'];

    protected $auditInclude = ['name', 'cpf', 'birth_date', 'phone', 'email', 'insurance_id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'address'    => 'array',
        ];
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function age(): int
    {
        return $this->birth_date->age;
    }
}
