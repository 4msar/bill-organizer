<?php

namespace App\Models;

use App\Observers\TeamObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[ObservedBy([TeamObserver::class])]
final class Team extends Model
{
    use HasFactory;

    const PivotTableName = 'team_user';

    const TableName = 'teams';

    protected $fillable = [
        'user_id', // Owner ID
        'name',
        'description',
        'icon',
        'status',
        'currency',
        'currency_symbol',
    ];

    protected $appends = [
        'icon_url',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        self::addGlobalScope('user', function (Builder $builder) {
            $table = self::TableName;
            // check if the user can access the model via pivot table
            $builder->whereHas('users', function (Builder $query) {
                // In the users relation
                $query->where('user_id', Auth::id());
            })->orWhere("{$table}.user_id", Auth::id());
        });
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ?
            Storage::url($this->icon) :
            Storage::url('teams/default.png');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            self::PivotTableName
        )->distinct();
    }

    /**
     * Get the current team for the user.
     */
    public static function current(): self
    {
        $user = Auth::user();

        return $user->activeTeam;
    }

    /**
     * Relation with bills
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Relation with transactions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relation with categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Relation with notes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
