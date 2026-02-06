<?php

namespace App\Models;

use App\Models\Pivots\Notable;
use App\Models\Scopes\TeamScope;
use App\Models\Scopes\UserScope;
use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TeamScope::class, UserScope::class])]
final class Note extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'user_id',
        'team_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected $appends = [
        'was_recently_created',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function bills()
    {
        return $this->morphedByMany(Bill::class, 'notable')
            ->using(Notable::class);
    }

    public function transactions()
    {
        return $this->morphedByMany(Transaction::class, 'notable')
            ->using(Notable::class);
    }

    public function related()
    {
        return $this->hasMany(Notable::class, 'note_id')->with('notable');
    }

    public function getWasRecentlyCreatedAttribute(): bool
    {
        return $this->wasRecentlyCreated;
    }
}
