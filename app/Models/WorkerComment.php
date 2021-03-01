<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WorkerComment extends Model
{
    protected $table = 'worker_comments';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(WorkerComment::class, 'parent_id');
    }
}
