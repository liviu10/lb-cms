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
        Schema::create('content_visibilities', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('The key of the content visibility');
            $table->json('value')->comment('The name of the content visibility in different languages');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade');
            $table->timestamps();

            // Add indexes
            $table->index('key', 'content_visibilities_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_visibilities', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('content_visibilities_key');

            // Drop foreign keys
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('content_visibilities');
    }
};
