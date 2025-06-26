<?php

namespace App\Models;


use App\Traits\HasMetaData;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasMetaData, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the bills for the user.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get the categories for the user.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the active team for the user.
     */
    public function activeTeam()
    {
        return $this->belongsTo(Team::class, 'active_team_id', 'id');
    }

    /**
     * Get the teams for the user.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, Team::PivotTableName)->distinct();
    }

    /**
     * Switch the active team for the user.
     */
    public function switchTeam(Team $team)
    {
        $this->update([
            'active_team_id' => $team->id,
        ]);

        $this->refresh();
    }
}
