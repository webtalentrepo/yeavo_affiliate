<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildProduct extends Model
{
    protected $table = 'child_products';

    protected $fillable = [
        'product_id', 'parent_id', 'advertiser_id', 'ad_id', 'source_feed_type', 'title', 'description', 'target_country', 'brand', 'link',
        'image_link', 'p_amount', 'p_currency', 's_amount', 's_currency', 'catalog_id'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'parent_id', 'id');
    }
}
