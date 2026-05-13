<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Insurance extends Model implements AuditableContract
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = ['name', 'plan_type', 'contact_phone', 'is_active'];

    protected $auditInclude = ['name', 'plan_type', 'contact_phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
