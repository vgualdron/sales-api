<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCobro extends Model
{
    use HasFactory;

    protected $connection = 'mysql_secondary';
    protected $table = 'movimiento_cobros';

    protected $fillable = [
        'periodo_id',
        'asociado_id',
        'credito_id',
        'lineaaporte_id',
        'total_aportes',
        'total_cuotas_credito',
        'fecha_cierre',
        'estado',
        'comentario',
    ];

    /**
     * Obtiene el periodo del cobro.
     */
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtiene el asociado del cobro.
     */
    public function asociado()
    {
        return $this->belongsTo(Asociado::class);
    }

    /**
     * Obtiene la linea de crédito asociada al cobro.
     */
    public function lineacredito()
    {
        return $this->belongsTo(LineaCredito::class);
    }

    /**
     * Obtiene la linea de aporte asociada al cobro.
     */
    public function lineaaporte()
    {
        return $this->belongsTo(LineaAporte::class);
    }

}
