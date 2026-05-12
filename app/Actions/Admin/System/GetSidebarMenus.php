<?php

namespace App\Actions\Admin\System;

use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetSidebarMenus
{
    public static function forUser(): Collection
    {
        $level = auth()->user()?->roles()->min('level') ?? 99;

        return Cache::remember("sidebar.menu.level.{$level}", 3600, function () use ($level) {
            return MenuItem::visible()
                ->forLevel($level)
                ->ordered()
                ->get()
                ->groupBy('group');
        });
    }
}
