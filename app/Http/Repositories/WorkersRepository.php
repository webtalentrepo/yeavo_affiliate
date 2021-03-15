<?php


namespace App\Http\Repositories;


use App\Models\Worker;
use Illuminate\Support\Facades\DB;

class WorkersRepository extends Repository
{
    /**
     * get my listings
     * @param $user_id
     * @return mixed
     */
    public function getListings($user_id)
    {
        return $this->model()
            ->where('user_id', $user_id)
            ->with(['like_users', 'dislike_users'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function model()
    {
        return app(Worker::class);
    }

    /**
     * get top workers
     *
     * @return mixed
     */
    public function getLikesCount()
    {
        return $this->model()
            ->select('workers.*', DB::raw('COUNT(worker_likes.user_id) as user_likes'))
            ->leftJoin('worker_likes', 'workers.id', '=', 'worker_likes.worker_id')
            ->with(['like_users', 'dislike_users', 'favorites_users'])
            ->groupBy('workers.id')
            ->orderBy('user_likes', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * get recently added count
     *
     * @param $service
     * @return mixed
     */
    public function getRecentlyAddedByTag($service)
    {
        return $this->model()
            ->where('search_tags', 'like', '%' . $service . '%')
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->orderBy('id', 'desc')
            ->count();
    }

    /**
     * get trending list
     *
     * @return mixed
     */
    public function getTrendingLists()
    {
        return $this->model()
            ->with(['like_users', 'dislike_users', 'favorites_users'])
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
    }
}
