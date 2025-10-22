<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MessageResource $message;

    public function __construct(Message $message)
    {
        $this->message = MessageResource::make($message->load('user'));
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('private-chat.' . $this->message->chat_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message
        ];
    }

    public function broadcastWhen(): bool
    {
        return true;
    }
}
