<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\WorkersRepository;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkersController extends Controller
{
    protected $workersRepo;

    public function __construct(WorkersRepository $workersRepository)
    {
        $this->workersRepo = $workersRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $workers = $this->workersRepo->getListings(auth()->id());

        return response()->json([
            'result'  => 'success',
            'message' => $workers
        ]);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Worker $worker)
    {
        return response()->json([
            'result'  => 'success',
            'message' => [
                'id'                 => $worker->id,
                'user_id'            => $worker->user_id,
                'worker_title'       => $worker->worker_title,
                'worker_url'         => $worker->worker_url,
                'worker_description' => $worker->worker_description,
                'image_name'         => $worker->image_name,
                'like_users'         => $worker->like_users()->get(),
                'dislike_users'      => $worker->dislike_users()->get(),
                'owner_user'         => $worker->owner_user()->first(),
                'comments'           => $worker->comments()->with(['replies', 'user'])->get(),
                'search_tags'        => json_decode($worker->search_tags),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Worker $worker)
    {
        //
        return response()->json([
            'result'  => 'success',
            'message' => [
                'worker_title' => $worker->worker_title,
                'worker_url'   => $worker->worker_url,
                'description'  => $worker->worker_description,
                'search_tags'  => json_decode($worker->search_tags),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Worker $worker)
    {
        $this->validate($request, [
            'worker_title'       => 'required',
            'worker_url'         => 'required|url',
            'worker_description' => 'required',
        ]);

        $worker->worker_title = $request->input('worker_title');
        $worker->worker_url = $request->input('worker_url');

        if ($request->has('search_tags') && !is_null($request->input('search_tags'))) {
            $worker->search_tags = json_encode($request->input('search_tags'));
        }

        $worker->worker_description = $request->input('worker_description');

        if ($request->hasFile('worker_image')) {
            $file = $request->file('worker_image');

            if (!is_null($worker->image_name) && Storage::exists('public' . $worker->image_name)) {
                Storage::delete('public' . $worker->image_name);
            }

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
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Worker $worker
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Worker $worker)
    {
        if (!is_null($worker->image_name) && Storage::exists('public' . $worker->image_name)) {
            Storage::delete('public' . $worker->image_name);
        }

        $worker->delete();

        return response()->json([
            'result' => 'success'
        ]);
    }
}
