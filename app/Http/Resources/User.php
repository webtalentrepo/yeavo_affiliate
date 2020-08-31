<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $nameAry = explode(' ', $this->name);
        $f_name = $nameAry[0];
        $l_name = isset($nameAry[1]) ? $nameAry[1] : '';
        return [
            'id'         => $this->id,
            'first_name' => $f_name,
            'last_name'  => $l_name,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => isset($this->roles[0]) ? new Role($this->roles[0]) : null,
            'profile'    => isset($this->user_profile) ? new Profile($this->user_profile) : null,
            'plan'       => isset($this->user_plans[0]) ? $this->user_plans[0] : null
        ];
    }
}
