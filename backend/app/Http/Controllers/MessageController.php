<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Get all messages in a conversation.
     */
    public function index(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->owner_id !== $user->id && $conversation->tenant_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return MessageResource::collection($messages);
    }

    /**
     * Store a new message and broadcast the event.
     */
    public function store(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->owner_id !== $user->id && $conversation->tenant_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'body'     => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body'      => $data['body'],
            'metadata'  => $data['metadata'] ?? [],
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message));

        return response()->json($message);
    }
}
