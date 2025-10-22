<?php

namespace App\Http\Resources;

use App\Models\Membership;
use Illuminate\Http\Request;

class ChatResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = parent::toArray($request);
        $customFields = [
            'memberships' => MembershipResource::collection($this->whenLoaded('memberships')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'admin' => UserResource::make($this->whenLoaded('admin')),
        ];
        return array_merge($attributes, $customFields);
    }
}
