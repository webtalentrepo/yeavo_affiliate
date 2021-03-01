<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerComment;
use Illuminate\Http\Request;

class WorkerCommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $comment = new WorkerComment();
        $comment->body = $request->get('comment_body');
        $comment->user()->associate($request->user());

        if ($request->input('is_reply') == 'yes') {
            $comment->parent_id = $request->get('comment_id');
        }

        $worker = Worker::find($request->get('worker_id'));
        $worker->comments()->save($comment);

        return response()->json(['result' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\WorkerComment $workerComment
     * @return \Illuminate\Http\Response
     */
    public function show(WorkerComment $workerComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\WorkerComment $workerComment
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkerComment $workerComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\WorkerComment $workerComment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkerComment $workerComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\WorkerComment $workerComment
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkerComment $workerComment)
    {
        //
    }
}
