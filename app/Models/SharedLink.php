<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedLink extends Model
{
    protected $fillable = [
        'org_chart_id', 'uuid', 'expires_at',
    ];

    public function orgChart()
    {
        return $this->belongsTo(OrgChart::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($sharedLink) {
            if (!$sharedLink->uuid) {
                $sharedLink->uuid = (string)Str::uuid();
            }
        });
    }
}
