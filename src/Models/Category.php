<?php

namespace LiviuVoica\LbCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property bool $is_active
 * @property int $user_id
 * @property array{id:int, full_name:string} $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'is_active',
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
        'is_active' => 'boolean',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('cms.user_model')
        );
    }
}
