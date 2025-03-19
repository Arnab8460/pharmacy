<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotAllotment extends Model
{
    use HasFactory;

    protected $table        =   'pharmacy_spot_student_allotment';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];

    public function spotStudent()
    {
        return $this->hasOne(SpotStudent::class, 's_id', 'stu_id');
    }

    public function payment()
    {
        return $this->hasOne(PaymentTransaction::class, 'pmnt_stud_id', 'stu_id');
    }

    public function institute()
    {
        return $this->hasOne(Institute::class, 'i_code', 'inst_code');
    }
}
