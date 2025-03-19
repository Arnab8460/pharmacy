<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyEligiblity extends Model
{
    use HasFactory;
    protected $table = 'pharmacy_eligibility_criteria';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'course_code',
        'elgb_exam',
        'marks_type',
        'is_active',
        'elgb_exam_short_code'
    ];
}
