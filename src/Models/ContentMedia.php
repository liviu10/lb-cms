<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LiviuVoica\LbCms\Enums\ContentMediaType;

/**
 * @property int $id
 * @property int $content_id
 * @property ContentMediaType $type
 * @property string $path
 * @property string $title
 * @property array $metadata
 * @property int $user_id
 * @property array{id:int, full_name:string} $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'type',
        'path',
        'title',
        'metadata',
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('cms.user_model')
        );
    }
}
