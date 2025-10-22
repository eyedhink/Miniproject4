<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        Log::info('Broadcast auth request received', [
            'user' => $request->user() ? $request->user()->id : 'null',
            'channel' => $request->channel_name,
            'headers' => $request->headers->all()
        ]);

        if (!$request->user('user')) {
            Log::warning('Broadcast auth failed: No authenticated user');
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $channelName = $request->channel_name;

        // Handle private channel authentication
        if (str_starts_with($channelName, 'private-')) {
            return $this->authenticatePrivateChannel($request);
        }

        Log::warning('Broadcast auth failed: Unhandled channel type', ['channel' => $channelName]);
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    private function authenticatePrivateChannel(Request $request): JsonResponse
    {
        $user = $request->user('user');
        $channelName = $request->channel_name;

        // Extract chat ID from channel name (private-chat.1)
        if (str_starts_with($channelName, 'private-chat.')) {
            $chatId = str_replace('private-chat.', '', $channelName);

            $isMember = Membership::query()->where('user_id', $user->id)
                ->where('chat_id', $chatId)
                ->exists();

            if ($isMember) {
                Log::info("Broadcast auth successful for user $user->id in chat $chatId");
                return response()->json([
                    'auth' => 'authorized'
                ]);
            } else {
                Log::warning("Broadcast auth failed: User $user->id is not member of chat $chatId");
            }
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
