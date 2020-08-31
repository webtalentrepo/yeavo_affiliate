<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activated', 'activation_code', 'banned', 'api_key', 'current_plan', 'persist_code', 'image_ext', 'company', 'address', 'city', 'postal_code', 'country', 'state_code', 'phone'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
