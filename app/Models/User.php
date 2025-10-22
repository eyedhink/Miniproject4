<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function ownedChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'admin_id');
    }

    public function chats(): array
    {
        $memberships = $this->memberships();
        $chats = [];
        foreach ($memberships as $membership) {
            $chats[] = Chat::query()->findOrFail($membership->chat_id);
        }
        return $chats;
    }
}
