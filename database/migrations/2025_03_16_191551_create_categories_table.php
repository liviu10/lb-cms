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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('The key of the content categories');
            $table->json('value')->comment('The name of the content category in different languages');
            $table->boolean('is_active')->default(false);
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade');
            $table->timestamps();

            // Add indexes
            $table->index('key', 'categories_key');
            $table->index('is_active', 'categories_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('categories_key');
            $table->dropIndex('categories_is_active');

            // Drop foreign keys
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('categories');
    }
};
