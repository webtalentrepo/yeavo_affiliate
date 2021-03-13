<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkerLikesController extends Controller
{
    public function voteLikeDislike(Request $request)
    {
        $user = auth()->user();

        $worker_id = $request->input('worker_id');
        if ($request->input('flag') === 'like') {
            if ($request->input('add') === 'yes') {
                $user->worker_likes()->attach($worker_id);
            } else {
                $user->worker_likes()->detach($worker_id);
            }
        } else {
            if ($request->input('add') === 'yes') {
                $user->worker_dislikes()->attach($worker_id);
            } else {
                $user->worker_dislikes()->detach($worker_id);
            }
        }

        return response()->json([
            'result' => 'success'
        ]);
    }
}
