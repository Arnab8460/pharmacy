<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateSeat extends Model
{
    protected $table        =   'private_seat_master';
    protected $primaryKey   =   'sm_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
