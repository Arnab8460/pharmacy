<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttendenceXi extends Model
{
    use HasFactory;

    protected $table        =   'wbscte_council_student_master_xi_tbl';
    protected $primaryKey   =   'exam_xi_att_id_pk';
    public $timestamps      =   false;

    protected $guarded = [];
}
