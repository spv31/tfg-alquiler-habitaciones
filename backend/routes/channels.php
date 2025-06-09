<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    return (int) $user->id === Conversation::find($id)->owner_id 
        || (int) $user->id === Conversation::find($id)->tenant_id;
});