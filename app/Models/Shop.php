<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $connection = 'mysql';
    public $table = "shops";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nit',
        'name',
        'agreement',
        'address',
        'phone',
        'email',
        'status',
        'observation',
        'order',
        'category_id',
    ];
}
