<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'network', 'category', 'child_category', 'full_category', 'site_id', 'popular_rank', 'p_title', 'p_description', 'p_commission',
        'p_commission_unit', 'p_gravity', 'seven_day_epc', 'three_month_epc', 'earning_uint', 'p_percent_sale'
    ];

    public function child_products()
    {
        return $this->hasMany('App\Models\ChildProduct', 'parent_id', 'id');
    }
}
