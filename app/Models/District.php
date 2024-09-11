<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    public $table = "districts";
    
    protected $fillable = [
        'id',
        'name',
        'sector',
        'group',
        'order',
        'status',
        'created_at',
        'updated_at'        
    ];
}
