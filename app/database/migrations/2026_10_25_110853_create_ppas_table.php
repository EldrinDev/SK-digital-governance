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
        Schema::create('ppas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained()->restrictOnDelete();
            $table->foreignId('abyip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cbydp_priority_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target');
            $table->decimal('budget', 12, 2);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('planned')->index();
            $table->timestamps();

            $table->index(['barangay_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppas');
    }
};
