<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementSeat extends Model
{
    protected $table        =   'management_seat_master';
    protected $primaryKey   =   'sm_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
