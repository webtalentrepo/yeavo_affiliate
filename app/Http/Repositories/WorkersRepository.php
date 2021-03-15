<?php


namespace App\Http\Repositories;


use App\Models\Worker;
use Illuminate\Support\Facades\DB;

class WorkersRepository extends Repository
{
    public function getListings($user_id)
    {
        return $this->model()
            ->where('user_id', $user_id)
            ->with(['like_users', 'dislike_users'])
            ->get();
    }

    public function model()
    {
        return app(Worker::class);
    }

    public function getLikesCount()
    {
        return $this->model()
            ->select('workers.*', DB::raw('COUNT(worker_likes.user_id) as user_likes'))
            ->leftJoin('worker_likes', 'workers.id', '=', 'worker_likes.worker_id')
            ->with(['like_users', 'dislike_users'])
            ->groupBy('workers.id')
            ->orderBy('user_likes', 'desc')
            ->take(10)
            ->get();
    }
}
