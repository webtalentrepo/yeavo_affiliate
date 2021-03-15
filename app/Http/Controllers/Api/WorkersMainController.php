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
        $top_workers = $this->workersRepo->getLikesCount()->map(function ($el) {
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

        return response()->json([
            'result'       => 'success',
            'top_workers'  => $top_workers,
            'recent_added' => $recently_added,
        ]);
    }
}
