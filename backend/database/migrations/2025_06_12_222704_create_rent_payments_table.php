<?php

use App\Models\Contract;
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
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Contract::class)->constrained()->cascadeOnDelete();

            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');

            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_mandate_id')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
