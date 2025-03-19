<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'district_id'           =>  $this->d_id,
            'district_name'         =>  $this->d_name,
            'district_code'            =>  $this->d_code,
        ];
    }
}
