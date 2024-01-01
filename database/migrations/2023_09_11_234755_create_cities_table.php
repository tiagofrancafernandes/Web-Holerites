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
        Schema::create('cities', function (Blueprint $table) {
            $table->timestamps();
            // $table->id();
            $table->uuid('id')->unique();

            $table->string('name');
            $table->bigInteger('city_code')->nullable()->index();
            $table->string('state_code')->nullable()->index();
            $table->string('state_name')->nullable();
            $table->string('state_local_name')->nullable();
            $table->string('country_iso_code')->index();

            $table->index('id');
            $table->primary('id');

            $table->unique(['city_code', 'state_code', 'country_iso_code', ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
