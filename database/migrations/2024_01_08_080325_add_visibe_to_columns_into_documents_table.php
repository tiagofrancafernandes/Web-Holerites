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
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('visible_to_type')->nullable()->index(); // App\Enums\DocumentVisibleToType
            $table->integer('visible_to')->nullable()->index(); // null, user is or group id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('visible_to_type');
            $table->dropColumn('visible_to');
        });
    }
};
