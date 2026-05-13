<?php

namespace App\Actions\Clinica\Rooms;

use App\Models\Room;

class UpdateRoomAction
{
    public function handle(Room $room, string $name, string $type, int $capacity, ?string $departmentId, bool $isActive): void
    {
        $room->update([
            'name'          => $name,
            'type'          => $type,
            'capacity'      => $capacity,
            'department_id' => $departmentId,
            'is_active'     => $isActive,
        ]);
    }
}
