<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemovedStudent extends Model
{
    protected $table        =   'pharmacy_self_mgmt_removed_students';
    protected $primaryKey   =   's_id';

    protected $guarded = [];
}
