<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batterie extends Model
{
    public $table = "batteries";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'description',
        'yard',
        'active'
    ];
}
