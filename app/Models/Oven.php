<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oven extends Model
{
    public $table = "ovens";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'batterie',
        'active'
    ];
}
