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
        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')
                ->constrained('contents')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->enum('type', [
                'Images',
                'Documents',
                'Video',
                'Audio',
                'Others'
            ])->default('Draft');
            $table->string('path');
            $table->string('title');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('type', 'content_media_type');
            $table->index('title', 'content_media_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_media', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('content_media_type');
            $table->dropIndex('content_media_title');

            // Drop foreign keys
            $table->dropForeign(['content_id']);
        });

        Schema::dropIfExists('content_media');
    }
};
