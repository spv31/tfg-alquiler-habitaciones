<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['individual', 'company'])->default('individual');
            $table->string('identifier')->unique(); // DNI or NIF
            $table->enum('role', ['owner', 'tenant'])->default('owner'); // Propietario o inquilino
            $table->string('profile_picture')->default('private/images/profile_pictures/default.jpg');
            $table->string('phone_number');
            $table->string('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
            $table->dropColumn('identifier');
            $table->dropColumn('role');
            $table->dropColumn('profile_picture');
            $table->dropColumn('phone_number');
            $table->dropColumn('address');
        });
    }
};
