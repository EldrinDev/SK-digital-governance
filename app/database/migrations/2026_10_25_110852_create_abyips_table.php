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
        Schema::create('abyips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained()->restrictOnDelete();
            $table->foreignId('cbydp_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('status')->default('draft');
            $table->string('resolution_number')->nullable();
            $table->date('approved_on')->nullable();
            $table->string('resolution_path')->nullable();
            $table->foreignId('approval_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['barangay_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abyips');
    }
};
