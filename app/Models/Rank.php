<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $table        =   'jexpo_rank';
    protected $primaryKey   =   'r_id';
    public $timestamps      =   false;

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, "r_index_num", "r_index_num")->withDefault(function () {
            return new User();
        });
    }
}
