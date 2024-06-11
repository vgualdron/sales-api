<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Yard extends Model
{
    use HasFactory;

    public $table = "yards";
    
    protected $fillable = [
        'id',
        'code',
        'name',
        'zone',
        'latitude',
        'longitude',
        'created_at',
        'updated_at'        
    ];
}
