<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Traits\HasMetaData;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([UserObserver::class])]
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasMetaData, Notifiable;

    use Impersonate;

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
     * Appends`s to the model's array form.
     */
    protected $appends = [
        'is_impersonating',
        'can_impersonate',
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
        return $this->hasMany(Bill::class)->orderBy('due_date', 'asc');
    }

    /**
     * Get the categories for the user.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class)
            ->orderBy('payment_date', 'desc');
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
        return $this->belongsToMany(
            Team::class,
            Team::PivotTableName,
            'user_id',
            'team_id'
        )
            ->distinct();
    }

    /**
     * Owner teams
     */
    public function ownTeams()
    {
        return $this->hasMany(Team::class, 'user_id', 'id');
    }

    /**
     * Get the notes for the user.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
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

    /**
     * Set default meta data for the user.
     */
    public function setDefaultMetaData(): void
    {
        if (! $this->getMeta('email_notification')) {
            $this->setMeta('email_notification', true);
        }

        if (! $this->getMeta('web_notification')) {
            $this->setMeta('web_notification', true);
        }

        if (! $this->getMeta('early_reminder_days')) {
            $this->setMeta('early_reminder_days', [7, 15, 30]);
        }

        if ($this->getMeta('enable_reports') === null) {
            $this->setMeta('enable_reports', false);
        }
    }

    public function getNotificationChannels()
    {
        $channels = [];
        // Check if the user has enabled email notifications
        if ($this?->getMeta('email_notification', false)) {
            $channels[] = 'mail';
        }
        if ($this?->getMeta('web_notification', false)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Check if the user belongs to a specific team.
     */
    public function hasTeam(int $teamId): bool
    {
        return $this->teams()
            ->withoutGlobalScopes()
            ->where('teams.id', $teamId)
            ->where('teams.user_id', $this->id)
            ->exists();
    }

    /**
     * Return true or false if the user can impersonate an other user.
     *
     * @param void
     */
    public function canImpersonate($targetUser = null): bool
    {
        return $this->teams()->withoutGlobalScopes()
            ->whereHas('users', function ($query) use ($targetUser) {
                $query->where(Team::PivotTableName.'.user_id', $targetUser->id);
            })->exists();
    }

    /**
     * Get if the user is impersonating.
     *
     * @return bool
     */
    public function getIsImpersonatingAttribute()
    {
        return app('impersonate')->isImpersonating();
    }

    /**
     * Get if the user can impersonate.
     *
     * @return bool
     */
    public function getCanImpersonateAttribute()
    {
        return $this->activeTeam?->user_id === $this->id;
    }
}
