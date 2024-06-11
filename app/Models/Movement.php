<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    public $table = "movements";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'consecutive',
        'start_date',
        'final_date',
        'created_ad',
        'updated_at'
    ];
}
