<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotStudent extends Model
{
    use HasFactory;

    protected $table        =   'pharmacy_spot_student_master';
    protected $primaryKey   =   's_id';
    public $timestamps      =   false;

    protected $guarded = [];

    public function spotAllotment()
    {
        return $this->hasOne(SpotAllotment::class, 'stu_id', 's_id');
    }

    public function institute()
    {
        return $this->hasOne(Institute::class, 'i_code', 'spot_inst_code');
    }
}
