<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Membership;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->firstWhere('username', $validated["username"]);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('user-token')->plainTextToken
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->create([
            'username' => $validated["username"],
            'password' => $validated["password"],
        ]);

        return response()->json(UserResource::make($user));
    }

    public function userInfo(Request $request): JsonResponse
    {
        return response()->json([
            'id' => $request->user()->id,
            'username' => $request->user()->username,
        ]);
    }

    public function isInChat(Request $request, $id): JsonResponse
    {
        $memberships = Membership::query()->where("user_id", $request->user()->id)->get();
        foreach ($memberships as $membership) {
            if ($membership->chat_id == $id) {
                return response()->json(true);
            }
        }
        return response()->json(false);
    }

    public function show(Request $request, $id): JsonResponse
    {
        return response()->json(UserResource::make(User::query()->findOrFail($id)));
    }
}
