<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AlotedAdmittedHillSeatMaster extends Model
{
    protected $table        =   'hill_seat_master';
    protected $primaryKey   =   'sm_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
