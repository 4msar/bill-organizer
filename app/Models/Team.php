<?php

namespace App\Models;

use App\Observers\TeamObserver;
use App\Traits\Sluggable;
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
    use Sluggable;

    const PivotTableName = 'team_user';

    const TableName = 'teams';

    protected $fillable = [
        'user_id', // Owner ID
        'name',
        'slug',
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

    /**
     * Get the route key name for Laravel Route Model Binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Resolve route binding for the model.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    /**
     * Get slug options
     *
     * @return array
     */
    public function getSlugOptions(): array
    {
        return [
            'source' => 'name',
            'destination' => 'slug',
            'unique' => true,
            'maxLength' => 255,
        ];
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            if (str_starts_with($this->icon, 'http')) {
                return $this->icon;
            }
            return Storage::url($this->icon);
        }

        return Storage::url('teams/default.png');
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
