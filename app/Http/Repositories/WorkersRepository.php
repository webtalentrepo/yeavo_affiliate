<?php


namespace App\Http\Repositories;


use App\Models\Worker;

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
}
