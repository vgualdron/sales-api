<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Asociado;
use App\Models\MovimientoCobro;
use App\Models\MovimientoRecaudo;

class StatementController extends Controller
{
    /**
     * Obtiene cobros y recaudos pendientes y realizados de un asociado específico.
     */
    public function get(string $document)
    {
        $asociadoId = null;
        $asociado = Asociado::where('cedula', $document);
        if ($asociado) {
            $asociadoId = $asociado->id;
        }
        //dd($asociadoId);

        // Obtener aportes realizados donde lineaaporte_id no sea nula
        $aportes_realizados = MovimientoRecaudo::join('recaudos', 'movimiento_recaudos.recaudo_id', '=', 'recaudos.id')
            ->join('periodos', 'recaudos.periodo_id', '=', 'periodos.id') // Suponiendo que hay un campo periodo_id en recaudos
            ->join('lineaaportes', 'movimiento_recaudos.lineaaporte_id', '=', 'lineaaportes.id') // Unir con lineaaportes
            ->where('recaudos.asociado_id', $asociadoId)  // Usa asociado_id de la tabla recaudos
            ->whereNotNull('movimiento_recaudos.lineaaporte_id')  // Filtrar solo cuando lineaaporte_id no sea nula
            ->whereNotNull('movimiento_recaudos.valor_aporte')
            ->where('movimiento_recaudos.valor_aporte', '>', 0)
            ->where('movimiento_recaudos.valor_cuota_credito', '=', 0)
            ->select(
                'movimiento_recaudos.*',
                'recaudos.fecha_recaudo',
                'periodos.nombre as periodo_nombre',
                'lineaaportes.nombre as linea_aporte_nombre'
                ) // Selecciona los campos necesarios
            ->get();
        //dd($aportes_realizados);

        // Obtener aportes pendientes
        $aportes_pendientes = MovimientoCobro::join('periodos', 'movimiento_cobros.periodo_id', '=', 'periodos.id') // Unir con periodos usando periodo_id
            ->join('lineaaportes', 'movimiento_cobros.lineaaporte_id', '=', 'lineaaportes.id') // Unir con lineaaportes usando lineaaporte_id
            ->where('movimiento_cobros.asociado_id', $asociadoId) // Filtrar por asociado
            ->where('movimiento_cobros.estado', 'Pendiente') // Filtrar por estado "Pendiente"
            ->where('movimiento_cobros.total_aportes', '>', 0) // Filtrar por total_aportes mayor a 0
            ->select(
                'movimiento_cobros.*', // Todos los campos de movimiento_cobros
                'periodos.nombre as periodo_nombre', // El nombre del periodo
                'lineaaportes.nombre as linea_aporte_nombre' // El nombre de la línea de aporte
            )
            ->get();
        //dd($aportes_pendientes);

        // Obtener cuotas de crédito realizadas donde credito_id no sea nula
        $cuotas_credito_realizadas = MovimientoRecaudo::join('recaudos', 'movimiento_recaudos.recaudo_id', '=', 'recaudos.id')
            ->join('periodos', 'recaudos.periodo_id', '=', 'periodos.id') // Unir con la tabla periodos usando periodo_id en recaudos
            ->join('creditos', 'movimiento_recaudos.credito_id', '=', 'creditos.id') // Unir con creditos usando credito_id
            ->join('lineacreditos', 'creditos.lineacredito_id', '=', 'lineacreditos.id') // Unir con lineacreditos usando lineacredito_id en creditos
            ->where('recaudos.asociado_id', $asociadoId)  // Filtrar por asociado_id en la tabla recaudos
            ->whereNotNull('movimiento_recaudos.credito_id')  // Filtrar donde credito_id no sea nula
            ->whereNotNull('movimiento_recaudos.valor_cuota_credito')  // Filtrar donde valor_cuota_credito no sea nulo
            ->where('movimiento_recaudos.valor_cuota_credito', '>', 0)  // Filtrar por valor_cuota_credito mayor a 0
            ->where('movimiento_recaudos.valor_aporte', '=', 0)  // Filtrar donde valor_aporte sea 0
            ->select(
                'movimiento_recaudos.*',  // Seleccionar todos los campos de movimiento_recaudos
                'recaudos.fecha_recaudo',  // Incluir la fecha de recaudo desde la tabla recaudos
                'periodos.nombre as periodo_nombre',  // Incluir el nombre del periodo desde la tabla periodos
                'lineacreditos.nombre as linea_credito_nombre'  // Incluir el nombre de la línea de crédito desde lineacreditos
                )
            ->get();
        //dd($cuotas_credito_realizadas);

        // Obtener cuotas de crédito pendientes
        $cuotas_credito_pendientes = MovimientoCobro::join('periodos', 'movimiento_cobros.periodo_id', '=', 'periodos.id') // Unir con periodos usando periodo_id
            ->join('creditos', 'movimiento_cobros.credito_id', '=', 'creditos.id') // Unir con creditos usando credito_id
            ->join('lineacreditos', 'creditos.lineacredito_id', '=', 'lineacreditos.id') // Unir con lineacreditos usando lineacredito_id en creditos
            ->where('movimiento_cobros.asociado_id', $asociadoId)
            ->where('movimiento_cobros.estado', 'Pendiente')
            ->where('movimiento_cobros.total_cuotas_credito', '>', 0)
            ->select(
                'movimiento_cobros.*', // Todos los campos de movimiento_cobros
                'periodos.nombre as periodo_nombre', // El nombre del periodos
                'lineacreditos.nombre as linea_credito_nombre' // El nombre de la línea de crédito desde lineacreditos
            )
            ->get();
        //dd($cuotas_credito_pendientes);

        return response()->json([
            'aportes_realizados' => $aportes_realizados,
            'aportes_pendientes' => $aportes_pendientes,
            'cuotas_credito_realizadas' => $cuotas_credito_realizadas,
            'cuotas_credito_pendientes' => $cuotas_credito_pendientes
        ]);
    }

}
