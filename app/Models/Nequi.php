<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nequi extends Model
{
    public $table = "nequis";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'number',
        'order',
        'status',
        'global',
        'listing_id',
    ];
}
