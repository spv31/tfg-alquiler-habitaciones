<?php

use App\Models\Property;
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
    Schema::create('invitations', function (Blueprint $table) {
      $table->id();
      $table->string('email')->unique();
      $table->string('token')->unique();

      /**
       * Relación Polimórfica
       * ID Property | Room
       */
      $table->unsignedBigInteger('rentable_id');
      $table->string('rentable_type');

      $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();

      $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
      $table->timestamps();
      $table->index(['rentable_id', 'rentable_type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invitations');
  }
};
