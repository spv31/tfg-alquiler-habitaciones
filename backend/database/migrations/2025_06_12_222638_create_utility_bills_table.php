<?php

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
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Property::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Room::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'owner_id')->constrained()->cascadeOnDelete();

            $table->date('issue_date');
            $table->date('due_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('total_amount', 10, 2);
            
            $table->enum('category', ['utility', 'general', 'tax'])->default('utility');
            $table->string('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'split', 'settled'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_bills');
    }
};
