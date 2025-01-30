<?php

use App\Models\User;
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
        Schema::create('property_tenants', function (Blueprint $table) {
            $table->id();

            /**
             * Relaciń Polimórfica
             * ID Property | Room
             */
            $table->unsignedBigInteger('rentable_id');
            $table->string('rentable_type');

            $table->foreignIdFor(User::class, 'tenant_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['rentable_id', 'rentable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_tenants');
    }
};
