<?php

namespace App\Http\Controllers;

use App\Http\Resources\MembershipResource;
use App\Models\Chat;
use App\Models\Membership;
use App\Services\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ["required", "integer", "exists:users,id"],
            'chat_id' => ["required", "integer", "exists:chats,id"],
            'type' => ["required", "string", "exists:normal,admin"],
        ]);
        $chat = Chat::query()->findOrFail($validated["chat_id"]);
        if ($chat->publicity == "private") {
            return response()->json(["error" => "Can't join private chat"]);
        }

        Membership::query()->create($validated);

        return response()->json(["message" => "Membership stored"]);
    }

    public function index(Request $request): JsonResponse
    {
        return Utils::automatedPaginationWithBuilder($request, Membership::with("user", "chat"), MembershipResource::class);
    }

    public function show($id): JsonResponse
    {
        return response()->json(MembershipResource::make(Membership::query()->findOrFail($id)));
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ["sometimes", "integer", "exists:users,id"],
            'chat_id' => ["sometimes", "integer", "exists:chats,id"],
            'type' => ["sometimes", "string", "exists:normal,admin"],
        ]);

        Membership::query()->findOrFail($id)->update($validated);

        return response()->json(["message" => "Membership updated"]);
    }

    public function destroy($id): JsonResponse
    {
        Membership::query()->findOrFail($id)->delete();
        return response()->json(["message" => "Membership deleted"]);
    }
}
