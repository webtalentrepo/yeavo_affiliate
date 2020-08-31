<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Plan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'name'              => $this->plan_name,
            'product_id'        => $this->product_id,
            'product_url'       => $this->product_url,
            'amount'            => ($this->amount != 0 ? number_format($this->amount, 2) : ''),
            'free_plan'         => $this->free_plan,
            'duration'          => $this->duration != 0 ? $this->duration . ' ' . $this->duration_schedule . '(s)' : '',
            'duration_schedule' => $this->duration_schedule,
            'status'            => $this->status,
            'description'       => $this->description,
            'video_limit'       => $this->keyword_limit,
            'owners'            => $this->users()->count()
        ];
    }
}
