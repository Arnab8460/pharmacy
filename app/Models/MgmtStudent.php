<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MgmtStudent extends Model
{
    protected $table = 'pharmacy_management_register_student';
    protected $primaryKey = 's_id';
    public $timestamps = false;

    protected $guarded = [];

    public function institute()
    {
        return $this->hasOne(Institute::class, 'i_code', 's_inst_code');
    }
}
