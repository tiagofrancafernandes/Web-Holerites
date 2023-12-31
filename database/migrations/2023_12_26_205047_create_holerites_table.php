<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holerites', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->integer('status')->default(0)->nullable()->index();
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holerites');
    }
};
