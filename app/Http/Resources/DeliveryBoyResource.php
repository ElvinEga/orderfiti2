<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class  DeliveryBoyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {


        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "username"     => $this->username,
            "email"        => $this->email,
            "branch_id"    => $this->branch_id,
            "phone"        => $this->phone === null ? '' : $this->phone,
            "status"       => $this->status,
            'zone_id'      => $this->zone_id,
            'zone_name'    => $this->zone_name,
            "image"        => $this->image,
            "country_code" => $this->country_code,
        ];
    }
}
