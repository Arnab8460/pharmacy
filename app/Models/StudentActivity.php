<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentActivity extends Model
{
    protected $table        =   'pharmacy_student_activities';
    protected $primaryKey   =   'a_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
