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
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->boolean('remit_to_tenants')
                ->default(true)
                ->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utility_bills', function (Blueprint $table) {
            $table->dropColumn('remit_to_tenants');
        });
    }
};
