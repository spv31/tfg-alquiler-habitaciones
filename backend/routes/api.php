<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillShareController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTemplateController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDetailController;
use App\Http\Controllers\RentPaymentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantAssignmentController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Middleware\ExpireInvitationsMiddleware;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use App\Models\User;

Route::get('/user', function (Request $request) {
  return new UserResource($request->user());
})->middleware('auth:sanctum');

Route::get('/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
  if (! URL::hasValidSignature($request)) {
    abort(403, 'Invalid or expired verification link.');
  }

  $user = User::findOrFail($id);

  if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
    abort(403, 'Invalid verification hash.');
  }

  if (! $user->hasVerifiedEmail()) {
    $user->markEmailAsVerified();
    event(new Verified($user));
  }

  return redirect(env('FRONTEND_URL') . '/profile');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');


// Resend email verification
Route::post('/email/verification-notification', function (Request $request) {
  if ($request->user()->hasVerifiedEmail()) {
    return response()->json(['message' => 'email_already_verified'], 400);
  }
  $request->user()->sendEmailVerificationNotification();
  return response()->json(['message' => 'verification_link_sent']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::middleware('auth:sanctum')->group(function () {
  // User
  Route::put('/users/{user}', [AuthController::class, 'update'])
    ->name('users.update');
  Route::post('/users/{user}/avatar', [AuthController::class, 'updateAvatar'])
    ->name('users.avatar.update');

  // Properties
  Route::apiResource('properties', PropertyController::class);
  Route::post('/properties/{id}/update', [PropertyController::class, 'update']);
  Route::apiResource('properties.rooms', RoomController::class);
  Route::post('/properties/{property}/rooms/{room}/update', [RoomController::class, 'update']);

  // See tenants
  Route::get('/properties/{property}/tenants', [PropertyController::class, 'listPropertyTenants']);
  Route::get('/properties/{property}/rooms/{room}/tenants', [RoomController::class, 'listRoomTenants']);

  // Change status
  Route::patch('/properties/{property}/status', [PropertyController::class, 'changeStatus']);
  Route::patch('/properties/{property}/rooms/{room}/status', [RoomController::class, 'changeStatus'])->name('properties.rooms.changeStatus');

  // Routes for creating or updating properties
  Route::get('/properties/{id}/details', [PropertyDetailController::class, 'show']);
  Route::put('/properties/{id}/details', [PropertyDetailController::class, 'updateOrCreate']);

  // Invitations
  Route::middleware(ExpireInvitationsMiddleware::class)->group(function () {
    Route::apiResource('invitations', InvitationController::class);

    // Regenerate Invitation
    Route::post('/invitations/{invitation}/regenerate', [InvitationController::class, 'regenerate']);
  });

  // Reassignment of tenants/properties/rooms 
  Route::post('tenant-assignments/reassign', [TenantAssignmentController::class, 'reassign']);

  // Release tenats
  Route::delete('tenant-assignments/remove', [TenantAssignmentController::class, 'removeAssignment']);

  // Tenant View
  Route::get('/assigned-rentable', [TenantAssignmentController::class, 'getAssignedRentable']);

  // Property images
  Route::get('/properties/{property}/images/{filename}', [ImageController::class, 'showPropertyImage'])
    ->name('image.property.show');

  // Room images
  Route::get('/properties/{property}/rooms/{room}/images/{filename}', [ImageController::class, 'showRoomImage'])
    ->name('image.room.show');

  // User images
  Route::get('/users/{user}/avatar/{filename}', [ImageController::class, 'showUserImage'])
    ->name('image.user.show');

  // Contract Templates
  Route::apiResource('contract-templates', ContractTemplateController::class);
  Route::get('/contract-templates/{contractTemplate}/preview', [ContractTemplateController::class, 'preview'])->name('contract-templates.preview');
  // Contract
  Route::apiResource('contracts', ContractController::class);
  Route::get('contracts/{contract}/preview', [ContractController::class, 'preview']);
  Route::post(
    'contracts/{contract}/signed',
    [ContractController::class, 'uploadSigned']
  )->name('contracts.uploadSigned');
  Route::get('contracts/{contract}/pdf', [ContractController::class, 'previewPdf']);

  /**
   * Routes for chats
   */
  // Get all conversations
  Route::get('conversations', [ConversationController::class, 'index']);
  // Get messages from conversation
  Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
  // Send message
  Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
  // Create or get conversation related to tenant or owner
  Route::post('conversations', [ConversationController::class, 'store']);

  /**
   * Routes for payments (rental and supplies)
   */
  // Utility Bills
  Route::apiResource('utility-bills', UtilityBillController::class)
    ->except(['edit', 'create']);

  // Compartir/facturar suministros
  Route::get('utility-bills/{utilityBill}/shares', [BillShareController::class, 'index']);
  Route::post('utility-bills/{utilityBill}/shares', [BillShareController::class, 'store']);

  // Pago manual de un suministro
  Route::post('bill-shares/{billShare}/pay', [BillShareController::class, 'pay'])
    ->name('bill-shares.pay');

  // Pagos de renta (mensualidades)
  Route::apiResource('rent-payments', RentPaymentController::class)
    ->only(['index', 'show', 'store', 'update']);

  // Pago manual de una mensualidad
  Route::post('rent-payments/{rentPayment}/pay', [RentPaymentController::class, 'pay'])
    ->name('rent-payments.pay');

  // Callbacks tras la sesión de Checkout
  Route::get('payments/stripe-success', [RentPaymentController::class, 'handleSepaSuccess'])
    ->name('payments.success');
  Route::get('payments/stripe-cancel',  [RentPaymentController::class, 'handleSepaCancel'])
    ->name('payments.cancel');

  // API de registros de pagos
  Route::get('payments',           [PaymentController::class, 'index']);
  Route::get('payments/{payment}', [PaymentController::class, 'show']);

  // Marcado manual de un pago ya creado
  Route::post('payments/{payment}/manual', [PaymentController::class, 'markManual']);

  // Webhook de Stripe para sincronizar status
  Route::post('stripe/webhook', [PaymentController::class, 'handleStripeWebhook']);
});

Route::middleware(['auth:sanctum', 'role:tenant'])
  ->prefix('tenant')
  ->group(function () {
    Route::get('/dashboard', [TenantController::class, 'data']);
    Route::get('/rentable', [TenantController::class, 'getAssignedRentable']);
    Route::get('/contract', [TenantController::class, 'getCurrentContract']);
    Route::post('/contract/signed', [TenantController::class, 'uploadSigned']);
    Route::get('/contract/pdf', [TenantController::class, 'previewPdf']);
  });

// Auth Routes for login, register and logout
Route::post('/register/owner', [AuthController::class, 'registerOwner']);
Route::post('/register/tenant', [AuthController::class, 'registerTenantFromInvitation']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Routes por resetting password
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
