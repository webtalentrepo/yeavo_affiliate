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
            ->with(['like_users', 'dislike_users', 'owner_user'])
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
     * @param $search_str
     * @param $platform
     * @param $service_category
     * @return mixed
     */
    public function getLikesCount($search_str, $platform, $service_category)
    {
        $qry = $this->model()
            ->select('workers.*', DB::raw('COUNT(worker_likes.user_id) as user_likes'))
            ->leftJoin('worker_likes', 'workers.id', '=', 'worker_likes.worker_id')
            ->with(['like_users', 'dislike_users', 'favorites_users', 'owner_user'])
            ->groupBy('workers.id')
            ->orderBy('user_likes', 'desc')
            ->take(10);

        if ($search_str !== '') {
            $qry = $qry->where(function ($q) use ($search_str) {
                return $q->where('workers.worker_title', 'like', '%' . $search_str . '%')
                    ->orWhere('workers.worker_description', 'like', '%' . $search_str . '%');
            });
        }

        if ($platform !== 'All' && $platform !== '') {
            $qry = $qry->where('workers.search_tags', 'like', '%' . $platform . '%');
        }

        if ($service_category !== 'All' && $service_category !== '') {
            $qry = $qry->where('workers.search_tags', 'like', '%' . $service_category . '%');
        }

        return $qry->get();
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
     * @param $search_str
     * @param $platform
     * @param $service_category
     * @return mixed
     */
    public function getTrendingLists($search_str, $platform, $service_category)
    {
        $qry = $this->model()
            ->with(['like_users', 'dislike_users', 'favorites_users', 'owner_user'])
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->orderBy('id', 'desc')
            ->take(5);

        if ($search_str !== '') {
            $qry = $qry->where(function ($q) use ($search_str) {
                return $q->where('worker_title', 'like', '%' . $search_str . '%')
                    ->orWhere('worker_description', 'like', '%' . $search_str . '%');
            });
        }

        if ($platform !== 'All' && $platform !== '') {
            $qry = $qry->where('search_tags', 'like', '%' . $platform . '%');
        }

        if ($service_category !== 'All' && $service_category !== '') {
            $qry = $qry->where('search_tags', 'like', '%' . $service_category . '%');
        }

        $qry = $qry->get();

        return $qry;
    }
}
