<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => ["required", "integer", "exists:chats,id"],
            'content' => ["required", "string"],
        ]);

        $validated['user_id'] = $request->user('user')->id;
        $chat = Chat::query()->findOrFail($validated['chat_id']);

        $membership = $chat->memberships()->where('user_id', $validated['user_id'])->first();
        if (!$membership) {
            return response()->json(["error" => "You are not a member of this chat"], 403);
        }

        if ($chat->type == "channel" && $membership->type == "normal") {
            return response()->json(["error" => "Not enough Qualifications"]);
        }

        $message = Message::query()->create($validated);

        broadcast(new MessageSent($message));

        return response()->json(["message" => "Message stored", "data" => MessageResource::make($message)]);
    }

    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder($request, Message::with("user", "chat"), MessageResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(MessageResource::make(Message::query()->findOrFail($id)));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $message = Message::query()->findOrFail($id);

        if ($message->user_id != $request->user('user')->id) {
            return response()->json(["error" => "Unauthorized"], 403);
        }

        $validated = $request->validate([
            'content' => ["required", "string"],
        ]);

        $message->update($validated);

        broadcast(new MessageSent($message));

        return response()->json(["message" => "Message updated"]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $message = Message::query()->findOrFail($id);

        $user = $request->user('user');
        $isAdmin = $message->chat->memberships()
            ->where('user_id', $user->id)
            ->where('type', 'admin')
            ->exists();

        if ($message->user_id != $user->id && !$isAdmin) {
            return response()->json(["error" => "Not enough Qualifications"]);
        }

        $message->delete();

        return response()->json(["message" => "Message deleted"]);
    }

    public function getChatMessages($chatId): JsonResponse
    {
        $messages = Message::query()->where('chat_id', $chatId)
            ->with('user')
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json([
            'data' => MessageResource::collection($messages),
            'pagination_info' => [
                'total_items' => $messages->total(),
                'total_pages' => $messages->lastPage(),
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
            ]
        ]);
    }
}
