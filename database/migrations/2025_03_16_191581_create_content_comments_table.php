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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')
                ->constrained('contents')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->enum('status', [
                'Pending',
                'Approved',
                'Spam',
                'Trash'
            ])->default('Pending');
            $table->string('full_name');
            $table->string('email');
            $table->string('message');
            $table->boolean('privacy_policy')->default(false);
            $table->boolean('terms_and_conditions')->default(false);
            $table->string('cookie_visitor_uuid')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('status', 'content_comments_status');
            $table->index('full_name', 'content_comments_full_name');
            $table->index('email', 'content_comments_email');
            $table->index('privacy_policy', 'content_comments_privacy_policy');
            $table->index('terms_and_conditions', 'content_comments_terms_and_conditions');
            $table->index('cookie_visitor_uuid', 'content_comments_cookie_visitor_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('content_comments_status');
            $table->dropIndex('content_comments_full_name');
            $table->dropIndex('content_comments_email');
            $table->dropIndex('content_comments_terms_and_conditions');
            $table->dropIndex('content_comments_cookie_visitor_uuid');

            // Drop foreign keys
            $table->dropForeign(['content_id']);
        });

        Schema::dropIfExists('contents');
    }
};
