<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'         => ['required', 'string'],
            'p256dh_key'       => ['nullable', 'string'],
            'auth_key'         => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string', 'in:aes128gcm,aesgcm'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id'          => auth()->id(),
                'p256dh_key'       => $data['p256dh_key'] ?? null,
                'auth_key'         => $data['auth_key'] ?? null,
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
            ]
        );

        return response()->json(['ok' => true]);
    }
}
