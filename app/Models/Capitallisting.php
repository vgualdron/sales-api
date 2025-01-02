<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capitallisting extends Model
{
    public $table = "capitallistings";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'listing_id',
        'capital',
        'created_at',
        'updated_at',
    ];
}
