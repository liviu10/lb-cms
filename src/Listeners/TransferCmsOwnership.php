<?php

namespace LiviuVoica\LbCms\Listeners;

use LiviuVoica\LbCms\Services\ContentCategoryService;
use LiviuVoica\LbCms\Services\ContentService;

class TransferCmsOwnership
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        /** @var object{old_user_id:int} $event */
        $oldUserId = (int) $event->old_user_id;
        /** @var object{default_user_id:int} $event */
        $defaultUserId = (int) $event->default_user_id;

        ContentCategoryService::transferContentCategoryOwnership($oldUserId, $defaultUserId);
        ContentService::transferContentOwnership($oldUserId, $defaultUserId);
    }
}
