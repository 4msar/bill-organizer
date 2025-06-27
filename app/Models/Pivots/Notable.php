<?php

namespace App\Models\Pivots;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Notable extends MorphPivot
{
    protected $table = 'notables';
    protected $fillable = [
        'note_id',
        'notable_type',
        'notable_id',
    ];

    public function notable()
    {
        return $this->morphTo();
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
