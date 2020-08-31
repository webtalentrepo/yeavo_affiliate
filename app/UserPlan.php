<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    protected $table = 'user_plans';
    protected $fillable = [
        'free_pack', 'duration', 'duration_schedule', 'amount', 'currency_code', 'payment_status', 'status',
        'activated_on', 'expiry_on', 'invoice_code', 'payment_method', 'free_flag'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan');
    }
}
