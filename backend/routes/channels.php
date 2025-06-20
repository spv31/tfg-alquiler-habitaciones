<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/**
 * Channel for sending messages in conversations
 */
Broadcast::channel('conversation.{id}', function ($user, $id) {
    return (int) $user->id === Conversation::find($id)->owner_id
        || (int) $user->id === Conversation::find($id)->tenant_id;
});

/**
 * Channel for tracking users in a conversation
 */
Broadcast::channel('presence-conversation.{conversation}', function ($user, Conversation $conversation) {
    if ($user->id === $conversation->owner_id || $user->id === $conversation->tenant_id) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;  
});
