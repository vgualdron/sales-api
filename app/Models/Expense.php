<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public $table = "expenses";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'amount',
        'date',
        'status',
        'description',
        'item_id',
        'user_id',
        'file_id',
        'registered_by',
        'approved_by',
    ];
}
