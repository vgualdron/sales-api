<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redcollector extends Model
{
    public $table = "redcollectors";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'collector_id',
        'registered_by',
        'registered_date',
        'sector_id',
    ];
}
