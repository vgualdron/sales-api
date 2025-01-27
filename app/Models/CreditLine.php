<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditLine extends Model
{
    protected $connection = 'mysql_secondary';
    public $table = "lineacreditos";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nombre',
        'plazo',
        'valor',
        'interes_anual',
        'interes',
        'seguro_deudor',
        'seguro_credito',
        'estado',
        'updated_at',
        'created_at',
    ];
}
