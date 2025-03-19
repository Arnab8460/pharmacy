<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyAppl_ElgbExam extends Model
{
    use HasFactory;
    protected $table='pharmacy_appl_elgb_exam';
    protected $primaryKey = 'id';
    protected $fillable=['id','exam_appl_form_num','exam_marks_type','exam_elgb_code','exam_board','exam_roll_num','exam_pass_yr','exam_total_marks'];
}
