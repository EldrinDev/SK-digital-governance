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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category')->default('announcement')->after('user_id');
            $table->string('image_path')->nullable()->after('body');
            $table->timestamp('pinned_at')->nullable()->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['category', 'image_path', 'pinned_at']);
        });
    }
};
