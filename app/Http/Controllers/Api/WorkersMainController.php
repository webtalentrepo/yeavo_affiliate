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
        $counts = $this->workersRepo->getLikesCount();

        return response()->json([
            'result' => 'success',
            'count'  => $counts
        ]);
    }
}
