<?php

namespace LiviuVoica\LbCms\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LiviuVoica\LbCms\Models\Content;
use LiviuVoica\LbCms\Services\ContentManagementService;

class ContentPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $contentManagementService = new ContentManagementService(new Content);

        $contentManagementService->publishContent();
    }
}
