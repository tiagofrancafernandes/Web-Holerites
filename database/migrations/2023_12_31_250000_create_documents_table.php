<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique()->index();
            $table->dateTime('release_date')->nullable()->index(); // Data a partir da qual ficará disponível
            $table->dateTime('available_until')->nullable()->index(); // Data fim da disponibilidade do documento
            $table->integer('status')->default(0)->nullable()->index(); // Rascunho, Inválido, Validado, Em análise, Recusado, Aprovado para publicação
            $table->longText('internal_note')->nullable(); // Nota interna
            $table->longText('public_note')->nullable(); // Nota para quem visualizar o documento
            $table->unsignedBigInteger('storage_file_id')->index()->nullable();
            $table->unsignedBigInteger('document_category_id')->index()->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->boolean('public')->index()->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            $table->foreign('storage_file_id')->references('id')
                ->on('storage_files')->onDelete('cascade');

            $table->foreign('created_by')->references('id')
                ->on('users')->onDelete('set null');

            $table->foreign('document_category_id')->references('id')
                ->on('document_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
