<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementTicket extends Model
{
    public $table = "movements_tickets";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'movement',
        'ticket',
        'created_ad',
        'updated_at'
    ];
}
