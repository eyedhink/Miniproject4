<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/user-login", [UserController::class, "login"]);
Route::post("/user-register", [UserController::class, "register"]);

Route::middleware(['auth:user'])->group(function () {
    Route::post("/chat-store", [ChatController::class, "store"]);
    Route::get("/chat-index", [ChatController::class, "index"]);
    Route::get("/chat-show/{id}", [ChatController::class, "show"]);
    Route::put("/chat-edit/{id}", [ChatController::class, "edit"]);
    Route::delete("/chat-destroy/{id}", [ChatController::class, "destroy"]);
    Route::post("/chat-makeLink/{id}", [ChatController::class, "makeLink"]);
    Route::post("/chat-joinLink/{token}", [ChatController::class, "joinLink"]);

    Route::post("/membership-store", [MembershipController::class, "store"]);
    Route::get("/membership-index", [MembershipController::class, "index"]);
    Route::get("/membership-show/{id}", [MembershipController::class, "show"]);
    Route::put("/membership-edit/{id}", [MembershipController::class, "edit"]);
    Route::delete("/membership-destroy/{id}", [MembershipController::class, "destroy"]);

    Route::post("/message-store", [MessageController::class, "store"]);
    Route::get("/message-index", [MessageController::class, "index"]);
    Route::get("/message-show/{id}", [MessageController::class, "show"]);
    Route::put("/message-edit/{id}", [MessageController::class, "edit"]);
    Route::delete("/message-destroy/{id}", [MessageController::class, "destroy"]);
    Route::get("/chat-messages/{chatId}", [MessageController::class, "getChatMessages"]);

    Route::get("/user-info", [UserController::class, "userInfo"]);
    Route::get("/users/{id}", [UserController::class, "show"]);
    Route::get("/is-in-chat/{id}", [UserController::class, "isInChat"]);
});
