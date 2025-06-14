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
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'owner_iban')) {
                $table->string('owner_iban')->nullable()->after('pdf_path');
            }
            if (! Schema::hasColumn('contracts', 'tenant_iban')) {
                $table->string('tenant_iban')->nullable()->after('owner_iban');
            }
            if (! Schema::hasColumn('contracts', 'stripe_payment_method_id')) {
                $table->string('stripe_payment_method_id')->nullable()->after('tenant_iban');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'owner_iban')) {
                $table->dropColumn('owner_iban');
            }
            if (Schema::hasColumn('contracts', 'tenant_iban')) {
                $table->dropColumn('tenant_iban');
            }
            if (Schema::hasColumn('contracts', 'stripe_payment_method_id')) {
                $table->dropColumn('stripe_payment_method_id');
            }
        });
    }
};
