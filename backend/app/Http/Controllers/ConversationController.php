<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = Conversation::where('owner_id', $user->id)
            ->orWhere('tenant_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Creates or retrieves a conversation between owner and tenant.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'owner_id'    => 'required|exists:users,id',
            'tenant_id'   => 'required|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        // It ensures the authenticated user is either the owner or tenant
        if ($user->id !== $data['owner_id'] && $user->id !== $data['tenant_id']) {
            abort(403, 'Unauthorized');
        }

        // It guarantees a single conversation per pair
        $conversation = Conversation::firstOrCreate(
            [
                'owner_id'  => $data['owner_id'],
                'tenant_id' => $data['tenant_id'],
            ],
            [
                'property_id' => $data['property_id'] ?? null,
            ]
        );

        return response()->json($conversation);
    }
}
