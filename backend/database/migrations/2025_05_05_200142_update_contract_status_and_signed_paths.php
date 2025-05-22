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
            $table->enum('status', [
                'draft',
                'signed_by_owner',
                'active',
                'finished',
            ])->default('draft')->change();

            $table->string('pdf_path_signed_owner')->nullable()->after('pdf_path');
            $table->string('pdf_path_signed_tenant')->nullable()->after('pdf_path_signed_owner');

            $table->timestamp('signed_by_owner_at')->nullable()->after('pdf_path_signed_tenant');
            $table->timestamp('signed_by_tenant_at')->nullable()->after('signed_by_owner_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->enum('status', [
                'pending_signature',
                'active',
                'finished',
            ])->default('pending_signature')->change();

            $table->dropColumn('pdf_path_signed_owner');
            $table->dropColumn('pdf_path_signed_tenant');
            $table->dropColumn('signed_by_owner_at');
            $table->dropColumn('signed_by_tenant_at');
        });
    }
};
