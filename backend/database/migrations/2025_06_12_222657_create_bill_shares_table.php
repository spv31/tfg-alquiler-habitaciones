<?php

use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bill_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(UtilityBill::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'tenant_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled']);

            // Stripe PaymentIntent asociado (pagos online)
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_mandate_id')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_shares');
    }
};
