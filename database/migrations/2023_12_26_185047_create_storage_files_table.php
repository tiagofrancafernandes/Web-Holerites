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
        Schema::create('storage_files', function (Blueprint $table) {
            $table->id()->index();
            $table->string('disk_name')->index()->nullable();
            $table->string('path')->index()->nullable();
            $table->string('extension')->index()->nullable();
            $table->bigInteger('size_in_kb')->index()->nullable();
            $table->string('file_name')->index()->nullable();
            $table->string('original_name')->index()->nullable();
            $table->boolean('public')->index()->nullable()->default(true);
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->string('reference_class')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            $table->foreign('uploaded_by')->references('id')
                ->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_files');
    }
};
