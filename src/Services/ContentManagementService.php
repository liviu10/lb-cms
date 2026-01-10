<?php

namespace LiviuVoica\LbCms\Services;

use Carbon\Carbon;
use LiviuVoica\LbCms\Models\Content;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;
use Illuminate\Support\Facades\Storage;
use LiviuVoica\LbCms\Enums\ContentVisibility;

class ContentManagementService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var Content The content model instance. */
    private Content $content;

    /**
     * Create a new service instance.
     *
     * @see Content
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Publish scheduled content.
     *
     * Finds all content items with status "Scheduled" whose scheduled date
     * is less than or equal to the current date and updates their status
     * to "Published".
     */
    public function publishContent(): void
    {
        $now = Carbon::now();

        $this->content
            ->where('status', 'Scheduled')
            ->where('scheduled_on', '<=', $now)
            ->update([
                'status' => ContentVisibility::PUBLISHED,
                'published_on' => $now,
            ]);
    }

    /**
     * Force delete soft-deleted contents older than 1 month
     * along with all their associated media files.
     */
    public function deleteOlderContent(): void
    {
        $thresholdDate = Carbon::now()->subDays(
            (int) config('cms.content_force_delete_after_days')
        );

        $contents = $this->content
            ->onlyTrashed()
            ->where('deleted_at', '<', $thresholdDate)
            ->with(['media' => function ($query) {
                $query->withTrashed();
            }])
            ->get();

        foreach ($contents as $content) {
            // Delete all associated media files from storage and DB
            foreach ($content->media as $media) {
                if ($media->path && Storage::disk('local')->exists($media->path)) {
                    Storage::disk('local')->delete($media->path);
                }

                $media->forceDelete();
            }

            // Delete content directory entirely (safety cleanup)
            $contentDirectory = "uploads/content/{$content->id}";
            if (Storage::disk('local')->exists($contentDirectory)) {
                Storage::disk('local')->deleteDirectory($contentDirectory);
            }

            $content->forceDelete();
        }
    }
}
