<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlotedAdmittedSeatMaster extends Model
{
    protected $table        =   'alloted_admitted_seat_master';
    protected $primaryKey   =   'sm_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
