<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mysql_secondary';
    public $table = "empresas";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nit',
        'nombre',
        'direccion',
        'telefono',
        'email',
        'estado',
        'created_at',
        'updated_at',
    ];
}
