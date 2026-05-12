<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class MenuItem extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'label',
        'route',
        'icon',
        'group',
        'min_level',
        'is_visible',
        'order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'min_level'  => 'integer',
        'order'      => 'integer',
    ];

    protected array $auditInclude = ['is_visible', 'min_level', 'order'];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeForLevel($query, int $level)
    {
        return $query->where('min_level', '>=', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('order');
    }
}
