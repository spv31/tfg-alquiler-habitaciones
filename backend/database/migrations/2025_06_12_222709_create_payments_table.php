<?php

use App\Models\BillShare;
use App\Models\RentPayment;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BillShare::class)
                ->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RentPayment::class)
                ->nullable()->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->enum('method', ['stripe', 'manual_transfer'])->default('stripe');
            $table->string('stripe_payment_intent_id')->nullable();

            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
