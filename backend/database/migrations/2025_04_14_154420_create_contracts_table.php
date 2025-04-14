<?php

use App\Models\ContractTemplate;
use App\Models\Property;
use App\Models\Room;
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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            // Contract Template
            $table->foreignIdFor(ContractTemplate::class)->constrained()->cascadeOnDelete();
            /**
             * Relationship with Property or Room
             */
            $table->foreignIdFor(Property::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Room::class)->nullable()->constrained()->cascadeOnDelete();
            // Relationship with User (assigned tenant)
            $table->foreignIdFor(User::class, 'tenant_id')->constrained('users')->cascadeOnDelete();
            // Specific type of contract 
            $table->string('type')->nullable();
            // Economical data
            $table->decimal('price', 8, 2);
            $table->decimal('deposit', 8, 2);
            // Supplies
            $table->boolean('utilities_included')->default(false); 
            $table->string('utilities_payer')->nullable(); // "tenant", "owner", "shared".
            // if utilities shared, % 
            $table->decimal('utilities_proportion', 5, 2)->nullable();          
            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('extension_date')->nullable();
            // Status (pending_signature, active, finished)
            $table->enum('status', ['pending_signature', 'active', 'finished'])
            ->default('pending_signature');
            // PDFs Paths
            $table->string('pdf_path')->nullable();
            $table->string('pdf_path_signed')->nullable();
            // Final contract content
            $table->longText('final_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
