<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwimSession extends Model
{
    protected $fillable = [
        'name',
        'team_name',
        'total_time_ms',
        'total_splits',
        'total_rounds',
        'total_distance_m',
        'swimmers',
        'started_at',
    ];

    protected $casts = [
        'swimmers' => 'array',
        'started_at' => 'datetime',
    ];

    public function splits(): HasMany
    {
        return $this->hasMany(SwimSplit::class)->orderBy('split_number');
    }

    public function splitsForSwimmer(int $index): HasMany
    {
        return $this->splits()->where('swimmer_index', $index);
    }
}
