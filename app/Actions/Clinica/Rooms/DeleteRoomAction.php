<?php

namespace App\Actions\Clinica\Rooms;

use App\Models\Room;
use Illuminate\Validation\ValidationException;

class DeleteRoomAction
{
    public function handle(Room $room): void
    {
        if ($room->appointments()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'Não é possível excluir: existem agendamentos vinculados a esta sala.',
            ]);
        }

        $room->delete();
    }
}
