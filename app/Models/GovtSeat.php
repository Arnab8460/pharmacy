<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovtSeat extends Model
{
    use HasFactory;

    protected $table        =   'govt_seat_master';
    protected $primaryKey   =   'sm_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
