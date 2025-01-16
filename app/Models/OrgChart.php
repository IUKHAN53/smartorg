<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrgChart extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'json_data', 'is_shared', 'share_uuid',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'json_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($orgChart) {
            if ($orgChart->is_shared && !$orgChart->share_uuid) {
                $orgChart->share_uuid = (string) Str::uuid();
            }
        });
    }
}
