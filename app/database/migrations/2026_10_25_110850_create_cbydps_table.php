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
        Schema::create('cbydps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year');
            $table->string('status')->default('draft');
            $table->string('resolution_number')->nullable();
            $table->date('approved_on')->nullable();
            $table->string('resolution_path')->nullable();
            $table->foreignId('approval_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['barangay_id', 'start_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbydps');
    }
};
