<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\WorkersRepository;
use Illuminate\Http\Request;

class WorkersMainController extends Controller
{
    protected $workersRepo;

    public function __construct(WorkersRepository $workersRepository)
    {
        $this->workersRepo = $workersRepository;
    }

    public function index(Request $request)
    {
        $search_str = $request->input('search_str');
        $platform = $request->input('platform');
        $service_category = $request->input('service_category');

        $top_workers = $this->workersRepo->getLikesCount($search_str, $platform, $service_category)->map(function ($el) {
            $el->search_tags = json_decode($el->search_tags);

            return $el;
        });

        $tags = [
            'Writing',
            'Graphic Design',
            'Traffic',
            'SEO',
            'Programming',
            'Video Editing',
            'Others'
        ];

        $recently_added = [];
        for ($i = 0; $i < count($tags); $i++) {
            $added_count = $this->workersRepo->getRecentlyAddedByTag($tags[$i]);

            $recently_added[$tags[$i]] = $added_count;
        }

        $trending_list = $this->workersRepo->getTrendingLists($search_str, $platform, $service_category)->map(function ($el) {
            $el->search_tags = json_decode($el->search_tags);

            return $el;
        });

        return response()->json([
            'result'        => 'success',
            'top_workers'   => $top_workers,
            'recent_added'  => $recently_added,
            'trending_list' => $trending_list,
        ]);
    }

    public function workerFavorites(Request $request)
    {
        $user = auth()->user();

        $worker_id = $request->input('worker_id');

        if ($request->input('add') === 'yes') {
            $user->favorite_workers()->attach($worker_id);
        } else {
            $user->favorite_workers()->detach($worker_id);
        }

        return response()->json([
            'result' => 'success'
        ]);
    }
}
