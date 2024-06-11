<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    public $table = "rates";
    
    protected $fillable = [
        'id',
        'movement',
        'origin_yard',
        'destiny_yard',
        'supplier',
        'customer',
        'start_date',
        'final_date',
        'material',
        'conveyor_company',
        'material_price',
        'freight_price',
        'total_price',
        'observation',
        'round_trip',
        'created_at',
        'deleted_at'
    ];
}
