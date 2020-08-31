<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Profile extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $user_email = $this->user->email;
        $photo = (!is_null($this->image_ext) && !empty($this->image_ext)) ? $this->image_ext : 'https://www.gravatar.com/avatar/' . md5($user_email) . '.jpg?s=60&d=mm';

        return [
            'id'             => $this->id,
            'activated'      => $this->activated,
            'banned'         => $this->banned,
            'current_plan'   => $this->current_plan,
            'image_ext'      => $photo,
            'company'        => $this->company,
            'address'        => $this->address,
            'city'           => $this->city,
            'postal_code'    => $this->postal_code,
            'country'        => $this->country,
            'state_code'     => $this->state_code,
            'phone'          => $this->phone,
            'rendering_flag' => $this->rendering_flag,
            'updated_at'     => $this->updated_at
        ];
    }
}
