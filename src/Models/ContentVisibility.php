<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int $user_id
 * @property array{id:int, full_name:string} $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentVisibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'user_id',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'value' => 'array',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('cms.user_model')
        );
    }

    /** @return HasOne<Content> */
    public function content(): HasOne
    {
        return $this->hasOne(Content::class);
    }
}
