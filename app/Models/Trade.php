<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $table        =   'trade_master';
    protected $primaryKey   =   't_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
