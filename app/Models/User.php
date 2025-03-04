<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackRoute;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'email',
        'password',
        'phone',
        'country',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function routeNotificationForSlack(Notification $notification): mixed
    {
        return SlackRoute::make(env('SLACK_BOT_USER_DEFAULT_CHANNEL'), env('SLACK_BOT_USER_OAUTH_TOKEN'));
    }

    public function business(): BelongsTo {
        return $this->belongsTo(Business::class);
    }

    public function emailChanges(): HasMany {
        return $this->hasMany(EmailChange::class);
    }

    public function pendingEmailChange() {
        return $this->emailChanges()->where('changed', false)->first();
    }

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
            'data' => Json::class
        ];
    }
}
