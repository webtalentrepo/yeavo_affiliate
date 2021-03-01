<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The roles that belongs to the user
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    }

    public function worker_likes()
    {
        return $this->belongsToMany('App\Models\Worker', 'worker_likes', 'user_id', 'worker_id');
    }

    public function worker_dislikes()
    {
        return $this->belongsToMany('App\Models\Worker', 'worker_dislikes', 'user_id', 'worker_id');
    }

    public function plans()
    {
        return $this->belongsToMany('App\Plan', 'user_plans', 'user_id', 'plan_id');
    }

    public function user_profile()
    {
        return $this->hasOne('App\UserProfile');
    }

    public function user_plans()
    {
        return $this->hasMany('App\UserPlan');
    }
}
