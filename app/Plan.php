<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_name', 'product_id', 'product_url', 'amount', 'free_plan', 'duration', 'duration_schedule', 'keyword_limit', 'status', 'description'
    ];

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_plans', 'plan_id', 'user_id');
    }
}
