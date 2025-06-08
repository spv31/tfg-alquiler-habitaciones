<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return Conversation::where('id', $conversationId)
        ->where(fn($q) => $q->where('owner_id', $user->id)
                           ->orWhere('tenant_id', $user->id))
        ->exists();
});
