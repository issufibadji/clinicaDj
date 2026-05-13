<?php

namespace App\Actions\Clinica\Rooms;

use App\Models\Room;

class CreateRoomAction
{
    public function handle(string $name, string $type, int $capacity, ?string $departmentId): Room
    {
        return Room::create([
            'name'          => $name,
            'type'          => $type,
            'capacity'      => $capacity,
            'department_id' => $departmentId,
            'is_active'     => true,
        ]);
    }
}
