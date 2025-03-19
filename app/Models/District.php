<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table        =   'district_master';
    protected $primaryKey   =   'd_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
