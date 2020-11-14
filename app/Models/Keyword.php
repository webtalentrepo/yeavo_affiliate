<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = ['keywords', 'result', 'volume', 'trend', 'state', 'bid_low', 'bid_high', 'competition', 'write_date', 'updated_flag'];
}
