<?php

namespace App\Actions\Admin\System;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Collection;

class GetSidebarMenus
{
    public static function forUser(): Collection
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $permissions = $user->getAllPermissions()->pluck('name');

        return MenuItem::visible()
            ->ordered()
            ->get()
            ->filter(function (MenuItem $item) use ($permissions) {
                if ($item->permission_required === null) {
                    return true;
                }

                return $permissions->contains($item->permission_required);
            })
            ->groupBy('group');
    }
}
