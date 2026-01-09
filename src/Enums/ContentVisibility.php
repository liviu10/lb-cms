<?php

namespace LiviuVoica\LbCms\Enums;

enum ContentVisibility: string
{
    case PUBLISHED = 'Published';
    case DRAFT = 'Draft';
    case SCHEDULED = 'Scheduled';
    case TRASHED = 'Trashed';
    case PRIVATE = 'Private';
    case HIDDEN = 'Hidden';
    case ARCHIVED = 'Archived';
    case PENDING = 'Pending Review';
    case RESTRICTED = 'Restricted';
}
