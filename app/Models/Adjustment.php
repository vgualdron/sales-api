<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    public $table = "adjustments";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'yard',
        'material',
        'amount',
        'observation',
        'date',
        'created_at',
        'updated_at'
    ];
}
