<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkersController extends Controller
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
        $worker = new Worker();
        $worker->user_id = $request->user()->id;
        $worker->worker_title = $request->input('worker_title');
        $worker->worker_url = $request->input('worker_url');

        if ($request->has('search_tags') && !is_null($request->input('search_tags'))) {
            $worker->search_tags = json_encode($request->input('search_tags'));
        }

        $worker->worker_description = $request->input('worker_description');

        if ($request->hasFile('worker_image')) {
            $file = $request->file('worker_image');
            $name = '/workers/' . uniqid() . '.' . $file->extension();
            $file->storePubliclyAs('public', $name);
            $worker->image_name = $name;
        }

        $worker->save();

        return response()->json([
            'result' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\Response
     */
    public function show(Worker $worker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\Response
     */
    public function edit(Worker $worker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Worker $worker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Worker $worker)
    {
        //
    }
}
