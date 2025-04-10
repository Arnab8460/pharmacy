<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "course_id" => $this->course_id_pk,
            "course_name" => $this->course_name,
            "course_code" => $this->course_code,
            "course_type" => $this->course_type,
            "course_duration" =>  $this->course_duration,
            "is_active" =>  $this->is_active,
            'institute_name' =>  optional($this->institute)->institute_name,
        ];
    }
}
