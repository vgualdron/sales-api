<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Third extends Model
{
    public $table = "thirds";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nit',
        'name',
        'customer',
        'associated',
        'contractor',
        'active'
    ];
}
