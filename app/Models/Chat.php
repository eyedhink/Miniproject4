<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = 'chats';

    protected $fillable = [
        'name',
        'type',
        'publicity',
        'admin_id',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function admin(): BelongsTo {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
