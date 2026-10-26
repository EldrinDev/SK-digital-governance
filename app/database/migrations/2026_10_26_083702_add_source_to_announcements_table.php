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
            $table->string('source_url')->nullable()->after('image_path');
            $table->timestamp('originally_posted_at')->nullable()->after('source_url');
            $table->string('image_credit')->nullable()->after('originally_posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'originally_posted_at', 'image_credit']);
        });
    }
};
