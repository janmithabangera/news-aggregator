<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'preferred_sources',
        'preferred_categories',
        'preferred_authors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'preferred_sources' => 'array',
        'preferred_categories' => 'array',
        'preferred_authors' => 'array',
    ];

    public static function rules()
    {
        return [
            'preferred_sources' => 'array',
            'preferred_categories' => 'array',
            'preferred_authors' => 'array'
        ];
    }

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
