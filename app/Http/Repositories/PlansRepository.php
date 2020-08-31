<?php

namespace App\Http\Repositories;

use App\Plan;

class PlansRepository extends Repository
{
    public function create($request)
    {
        $ins_data = [
            'plan_name'         => $request->plan_name,
            'product_id'        => is_null($request->product_id) ? '' : $request->product_id,
            'product_url'       => is_null($request->product_url) ? '' : $request->product_url,
            'amount'            => is_null($request->amount) ? 0 : $request->amount,
            'free_plan'         => $request->free_plan ? 1 : 0,
            'duration'          => is_null($request->duration) ? 0 : $request->duration,
            'duration_schedule' => is_null($request->duration_schedule) ? '' : $request->duration_schedule,
            'keyword_limit'     => is_null($request->keyword_limit) ? 0 : $request->keyword_limit,
            'status'            => is_null($request->status) ? '' : $request->status,
            'description'       => $request->description,
        ];

        $this->model()->insert($ins_data);
    }

    public function model()
    {
        return app(Plan::class);
    }
}
