<?php

namespace LiviuVoica\LbCms\Enums;

enum ContentCommentStatus: string
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case SPAM = 'Spam';
    case TRASH = 'Trash';
}
