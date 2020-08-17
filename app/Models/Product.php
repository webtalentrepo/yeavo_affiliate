<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'network', 'category', 'child_category', 'site_id', 'popular_rank', 'p_title', 'p_description', 'p_commission', 'p_commission_unit', 'p_gravity', 'p_percent_sale'
    ];
}
