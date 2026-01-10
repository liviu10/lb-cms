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
        Schema::create('content_apreciation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')
                ->constrained('contents')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->integer('likes')->nullable();
            $table->integer('dislikes')->nullable();
            $table->integer('rating')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('likes', 'content_apreciation_likes');
            $table->index('dislikes', 'content_apreciation_dislikes');
            $table->index('rating', 'content_apreciation_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_apreciation', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('content_apreciation_likes');
            $table->dropIndex('content_apreciation_dislikes');
            $table->dropIndex('content_apreciation_rating');

            // Drop foreign keys
            $table->dropForeign(['content_id']);
        });

        Schema::dropIfExists('content_apreciation');
    }
};
