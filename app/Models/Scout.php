<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scout extends Model
{
    //
    protected $table = 'scouts';
    protected $fillable = [
        'network', 'site_id', 'user_id', 'advertiser_id', 'account_status', 'seven_day_epc', 'three_month_epc', 'advertiser_name',
        'program_url', 'relationship_status', 'mobile_supported', 'mobile_tracking_certified', 'cookieless_tracking_enabled',
        'network_rank', 'primary_category_parent', 'primary_category_child', 'performance_incentives', 'action', 'link_types',
        'default_commission', 'commission_unit', 'commission_value', 'commission_currency', 'deleted_flag', 'edited_flag',
    ];
}
