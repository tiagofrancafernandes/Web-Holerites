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
        Schema::table('document_categories', function (Blueprint $table) {
            $table->boolean('show_on_tab_filter')->nullable()->index()->default(true);
            $table->integer('order_on_tab_filter')->nullable()->index();
            $table->string('icon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropColumn('show_on_tab_filter');
            $table->dropColumn('order_on_tab_filter');
            $table->dropColumn('icon');
        });
    }
};
