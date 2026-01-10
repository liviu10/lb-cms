<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LiviuVoica\LbCms\Enums\ContentCommentStatus;
use LiviuVoica\LbCms\Models\Content;

/**
 * @property int $id
 * @property int $content_id
 * @property ContentCommentStatus $status
 * @property string $full_name
 * @property string $email
 * @property string $message
 * @property bool $privacy_policy
 * @property bool $terms_and_conditions
 * @property string|null $cookie_visitor_uuid
 * @property int|null $user_id
 * @property array{id:int, full_name:string}|null $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content_id',
        'status',
        'full_name',
        'email',
        'message',
        'privacy_policy',
        'terms_and_conditions',
        'cookie_visitor_uuid',
        'user_id',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status' => 'array',
        'privacy_policy' => 'boolean',
        'terms_and_conditions' => 'boolean',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    /** @return BelongsTo<Content> */
    public function content_category(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
