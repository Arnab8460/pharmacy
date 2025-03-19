<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentChoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'choice_id'                    =>  $this->ch_id,
            'inst_code'                  =>  $this->ch_inst_code,
            'choice_pref'                =>  $this->ch_pref_no,
            'choice_save'                =>  $this->student->is_lock_manual,
            'choice_auto_lock'                =>  $this->student->is_lock_auto,
        ];
    }
}
