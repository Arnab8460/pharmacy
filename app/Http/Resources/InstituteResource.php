<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstituteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'institute_id'                    =>  $this->i_id,
            'institute_code'                  =>  $this->i_code,
            'institute_name'                  =>  $this->i_name,
            'institute_type'    =>  $this->i_type,
            'institute_active'                =>  $this->is_active
        ];
    }
}
