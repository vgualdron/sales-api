<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $connection = 'mysql';
    public $table = "points";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'amount',
        'status',
        'description',
        'observation',
        'user_id',
        'shop_id',
        'invoice_number',
        'price',
        'type',
    ];
}
