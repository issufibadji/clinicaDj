<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Event extends Model implements AuditableContract
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = ['title', 'description', 'start_at', 'end_at', 'color', 'user_id', 'is_public'];

    protected $auditInclude = ['title', 'start_at', 'end_at', 'is_public'];

    protected function casts(): array
    {
        return [
            'start_at'  => 'datetime',
            'end_at'    => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
