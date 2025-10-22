<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Http\Resources\LinkResource;
use App\Models\Chat;
use App\Models\Link;
use App\Models\Membership;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:group,channel'],
            'publicity' => ['required', 'string', 'in:public,private'],
        ]);
        $validated['admin_id'] = $request->user('user')->id;
        $chat = Chat::query()->create($validated);
        Membership::query()->updateOrCreate(['user_id' => $request->user('user')->id, 'chat_id' => $chat->id, 'type' => "admin"]);
        return response()->json(["message" => "Chat created"]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('user');
        return Utils::automatedPaginationWithBuilder($request, Chat::with('memberships', 'messages', 'admin')
            ->whereHas('memberships', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }), ChatResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(Chat::query()->findOrFail($id));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:group,channel'],
            'publicity' => ['sometimes', 'string', 'in:public,private'],
        ]);
        Chat::query()->findOrFail($id)->update($validated);
        return response()->json(["message" => "Chat updated"]);
    }

    public function destroy($id): JsonResponse
    {
        Chat::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Chat deleted"]);
    }

    public function makeLink(Request $request, $id): JsonResponse
    {
        $chat = Chat::query()->findOrFail($id);
        if ($chat->memberships()->firstWhere('user_id', $request->user('user')->id)->type == "normal") {
            return response()->json(["error" => "Not enough Qualifications"]);
        }
        $link = Link::query()->create(['token' => rand(100000000, 10000000000000), 'chat_id' => $id]);
        return response()->json(LinkResource::make($link));
    }

    public function joinLink(Request $request, $token): JsonResponse
    {
        $link = Link::query()->firstWhere("token", $token);
        Membership::query()->create([
            'user_id' => $request->user('user')->id,
            'chat_id' => $link->chat_id,
        ]);
        return response()->json(["message" => "Joined chat!"]);
    }
}
