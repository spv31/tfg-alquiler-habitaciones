<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      try {
        $invitations = Invitation::where('owner_id', Auth::id())->paginate(10);

        return InvitationResource::collection($invitations);
      } catch (Exception $e) {
        return response()->json([
          'error' => 'Error al obtener las habitaciones.',
          'message' => $e->getMessage(), 
        ], 500);
      }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
