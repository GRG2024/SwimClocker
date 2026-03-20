<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwimSplit extends Model
{
    protected $fillable = [
        'swim_session_id',
        'swimmer_index',
        'swimmer_name',
        'round',
        'split_number',
        'lap_time_ms',
        'total_time_ms',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SwimSession::class, 'swim_session_id');
    }
}
