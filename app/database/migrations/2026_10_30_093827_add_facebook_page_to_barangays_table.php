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
        Schema::table('barangays', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable()->after('facebook_url');
            $table->string('facebook_page_name')->nullable()->after('facebook_page_id');
            $table->text('facebook_page_token')->nullable()->after('facebook_page_name');
            $table->timestamp('facebook_connected_at')->nullable()->after('facebook_page_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            $table->dropColumn(['facebook_page_id', 'facebook_page_name', 'facebook_page_token', 'facebook_connected_at']);
        });
    }
};
