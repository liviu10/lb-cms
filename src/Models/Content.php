<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LiviuVoica\LbCms\Enums\ContentType;
use LiviuVoica\LbCms\Enums\ContentVisibility;
use LiviuVoica\LbCms\Models\ContentMedia;
use LiviuVoica\LbCms\Models\ContentCategory;

/**
 * @property int $id
 * @property int $content_category_id
 * @property ContentVisibility $visibility
 * @property ContentType $type
 * @property Carbon|null $scheduled_on
 * @property string $slug
 * @property string $url
 * @property array<string> $tags
 * @property string $title
 * @property string|null $content
 * @property bool $allow_comments
 * @property bool $allow_share
 * @property int $user_id
 * @property array{id:int, full_name:string} $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content_category_id',
        'visibility',
        'type',
        'scheduled_on',
        'slug',
        'url',
        'tags',
        'title',
        'content',
        'allow_comments',
        'allow_share',
        'user_id',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'visibility' => 'array',
        'scheduled_on' => 'datetime:d.m.Y H:i',
        'tags' => 'array',
        'allow_comments' => 'boolean',
        'allow_share' => 'boolean',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    /** @return BelongsTo<ContentCategory> */
    public function content_category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('cms.user_model')
        );
    }

    /** @return HasOne<ContentMedia> */
    public function content_media(): BelongsTo
    {
        return $this->hasOne(ContentMedia::class);
    }
}
