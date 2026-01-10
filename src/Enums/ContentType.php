<?php

namespace LiviuVoica\LbCms\Enums;

enum ContentType: string
{
    case PAGE = 'Page';
    case ARTICLE = 'Article';
    case PRODUCT = 'Product';
    case EVENT = 'Event';
    case PORTFOLIO = 'Portfolio';
    case SERVICE = 'Service';
    case TESTIMONIAL = 'Testimonial';
    case FAQ = 'FAQ';
    case GALLERY = 'Gallery';
    case DOCUMENTATION = 'Documentation';
    case LANDING = 'Landing';

    public function urlPrefix(): string
    {
        return match($this) {
            self::PAGE => '',
            self::ARTICLE => 'blog',
            self::PRODUCT => 'shop',
            self::EVENT => 'events',
            self::PORTFOLIO => 'portfolio',
            self::SERVICE => 'services',
            self::TESTIMONIAL => 'reviews',
            self::FAQ => 'faq',
            self::GALLERY => 'gallery',
            self::DOCUMENTATION => 'docs',
            self::LANDING => 'campaigns',
        };
    }
}
