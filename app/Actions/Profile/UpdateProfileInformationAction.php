<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdateProfileInformationAction
{
    public function handle(User $user, string $name, string $email, ?string $phone, ?UploadedFile $avatar): void
    {
        $emailChanged = $user->email !== $email;

        $data = [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
        ];

        if ($avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }
    }
}
