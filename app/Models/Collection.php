<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $connection = 'mysql_secondary';
    public $table = "recaudos";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'fecha_recaudo',
        'asociado_id',
        'periodo_id',
        'valor_recaudo',
        'medio_papo',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
