<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $table = 'workers';
    protected $fillable = ['worker_title', 'worker_url', 'image_name', 'search_tags', 'worker_description'];

    public function like_users()
    {
        return $this->belongsToMany('App\User', 'worker_likes', 'worker_id', 'user_id');
    }

    public function dislike_users()
    {
        return $this->belongsToMany('App\User', 'worker_dislikes', 'worker_id', 'user_id');
    }

    public function favorites_users()
    {
        return $this->belongsToMany('App\User', 'worker_favorites', 'worker_id', 'user_id');
    }

    public function owner_user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function comments()
    {
        return $this->morphMany(WorkerComment::class, 'commentable')->where('parent_id', 0);
    }
}
