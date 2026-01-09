<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LiviuVoica\LbCms\Enums\ContentType;

/**
 * @property int $id
 * @property int $content_visibility_id
 * @property int $content_category_id
 * @property ContentType $type
 * @property Carbon|null $scheduled_on
 * @property string $slug
 * @property string $url
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
        'content_visibility_id',
        'content_category_id',
        'type',
        'scheduled_on',
        'slug',
        'url',
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
        'scheduled_on' => 'datetime:d.m.Y H:i',
        'allow_comments' => 'boolean',
        'allow_share' => 'boolean',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('cms.user_model')
        );
    }

    /** @return BelongsTo<ContentVisibility> */
    public function content_visibility(): BelongsTo
    {
        return $this->belongsTo(ContentVisibility::class, 'content_visibility_id');
    }

    /** @return BelongsTo<ContentCategory> */
    public function content_category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }
}
