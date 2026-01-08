<?php

namespace LiviuVoica\LbCms\Utilities;

trait EmailFooterTrait
{
    /**
     * Generates the email footer text with the current year and application name.
     *
     * @return string The formatted copyright string
     */
    public function handleEmailFooter(): string
    {
        $year = date('Y');
        $appName = (string) config('cms.app_name');
        $copyright = "© {$year} {$appName}. All rights reserved";

        return $copyright;
    }
}
