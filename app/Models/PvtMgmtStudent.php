<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvtMgmtStudent extends Model
{
    protected $table        =   'jexpo_management_register_student_pvt';
    protected $primaryKey   =   's_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
