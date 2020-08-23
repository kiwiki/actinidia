<?php

namespace App\Http\V1\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class UserResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'last_login' => $this->when('last_login_at', function () {
                $diff = now()->diffInHours(Date::make($this->last_login_at));

                if ($diff <= 8) {
                    return 'online';
                } elseif ($diff <= 12) {
                    return 'today';
                } elseif ($diff <= 48) {
                    return 'recently';
                } elseif ($diff <= 72) {
                    return 'week';
                }

                return 'offline';
            }),
            'created_at' => $this->created_at,
        ];
    }
}
