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
        Schema::create('abyip_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abyip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained()->restrictOnDelete();
            $table->text('reason');
            $table->string('status')->default('draft');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution_number')->nullable();
            $table->date('approved_on')->nullable();
            $table->string('resolution_path')->nullable();
            $table->foreignId('approval_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['abyip_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abyip_amendments');
    }
};
