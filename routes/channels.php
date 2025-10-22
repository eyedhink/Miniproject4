<?php

use App\Models\Membership;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('private-chat.{chatId}', function ($user, $chatId) {
    Log::info("Channel auth check for user $user->id in chat $chatId");

    $isMember = Membership::query()->where('user_id', $user->id)
        ->where('chat_id', $chatId)
        ->exists();

    Log::info("User $user->id is member of chat $chatId: " . ($isMember ? 'YES' : 'NO'));

    return $isMember;
});
