<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TeamScope::class])]
final class Category extends Model
{
    use HasFactory;
    use HasTeam;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['team_id', 'user_id', 'name', 'description', 'icon'];

    /**
     * Get the user that owns the category.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bills associated with the category.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
