<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table        =   'pharmacy_schedule_master';
    protected $primaryKey   =   'sch_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
