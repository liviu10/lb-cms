<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LiviuVoica\LbCms\Enums\ContentMediaType;

/**
 * @property int $id
 * @property int $content_id
 * @property ContentMediaType $type
 * @property string $path
 * @property string $title
 * @property array{
 *     size?: int,
 *     mime_type?: string,
 *     width?: int,
 *     height?: int,
 *     aspect_ratio?: string,
 *     resolution?: string,
 *     duration?: int,
 *     fps?: int,
 *     codec?: string,
 *     bitrate?: int,
 *     sample_rate?: int,
 *     pages?: int,
 *     language?: string
 * }|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content_id',
        'type',
        'path',
        'title',
        'metadata',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    /** @return BelongsTo<Content> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
