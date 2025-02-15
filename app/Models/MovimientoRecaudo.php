<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoRecaudo extends Model
{
    use HasFactory;

    protected $connection = 'mysql_secondary';
    protected $table = 'movimiento_recaudos';

    protected $fillable = [
        'recaudo_id',
        'credito_id',
        'lineaaporte_id',
        'movimiento_cobro_id',
        'valor_aporte',
        'valor_cuota_credito',
    ];

    public function recaudo()
    {
        return $this->belongsTo(Recaudo::class);
    }

    public function movimientoCobro()
    {
        return $this->belongsTo(MovimientoCobro::class, 'movimiento_cobro_id');
    }
}
