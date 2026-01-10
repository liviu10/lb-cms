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
            $table->foreignId('content_category_id')
                ->constrained('content_categories')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->enum('visibility', [
                'Published',
                'Draft',
                'Scheduled',
                'Trashed',
                'Private',
                'Hidden',
                'Archived',
                'Pending Review',
                'Restricted'
            ])->default('Draft');
            $table->enum('type', [
                'Page',
                'Article',
                'Product',
                'Event',
                'Portfolio',
                'Service',
                'Testimonial',
                'FAQ',
                'Gallery',
                'Documentation',
                'Landing'
            ])->default('Page');
            $table->timestamp('scheduled_on')
                ->nullable()
                ->default(null)
                ->comment('Date and time when the content becomes publicly available');
            $table->string('slug')->comment('URL-friendly unique identifier for the content');
            $table->string('url')->comment('Full or relative URL used to access the content');
            $table->json('tags')->comment('The list of tags');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->boolean('allow_comments')
                ->default(false)
                ->comment('Whether users are allowed to post comments');
            $table->boolean('allow_share')
                ->default(false)
                ->comment('Whether the content can be shared on external platforms');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('visibility', 'contents_visibility');
            $table->index('type', 'contents_type');
            $table->index('scheduled_on', 'contents_scheduled_on');
            $table->index('slug', 'contents_slug');
            $table->index('title', 'contents_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('contents_visibility');
            $table->dropIndex('contents_type');
            $table->dropIndex('contents_scheduled_on');
            $table->dropIndex('contents_slug');
            $table->dropIndex('contents_title');

            // Drop foreign keys
            $table->dropForeign(['content_category_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('contents');
    }
};
