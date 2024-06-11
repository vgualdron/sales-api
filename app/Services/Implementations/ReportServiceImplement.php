<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ReportServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\{ Ticket, Adjustment };
    use App\Validator\ZoneValidator;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;
    
    class ReportServiceImplement implements ReportServiceInterface {

        use Commons;

        private $ticket;
        private $adjustment;
        private $validator;

        function __construct(ZoneValidator $validator){
            $this->ticket = new Ticket;
            $this->adjustment = new Adjustment;
            $this->validator = $validator;
        }    

        function movements(string $movement, string $startDate, string $finalDate, int $originYard, int $destinyYard, int $material){
            try {
                $movement = trim(urldecode($movement));
                
                $transferTickets = $this->ticket::from('tickets as t1')
                                    ->select(
                                        DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as date'),
                                        DB::Raw('"TRASLADO" as movement'),
                                        't2.receipt_number as receiptNumber',
                                        't1.referral_number as referralNumber',
                                        't1.license_plate as licensePlate',
                                        't1.trailer_number as trailerNumber',
                                        't1.driver_name as driverName',
                                        't1.driver_document as driverDocument',
                                        'oy.name as originYard',
                                        'dy.name as destinyYard',
                                        'tc.name as conveyorCompany',
                                        'm.name as material',
                                        DB::Raw('FORMAT(t1.net_weight, 2) as originNetWeight'),
                                        DB::Raw('FORMAT(t2.net_weight, 2) as destinyNetWeight'),
                                        DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as originDate'),
                                        DB::Raw('DATE_FORMAT(t2.date, "%d/%m/%Y") as destinyDate'),
                                        DB::Raw('FORMAT(COALESCE(t1.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t1.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(t1.material_settlement_unit_value, 0), 2) as materialSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t1.material_settlement_net_value, 0), 2) as materialSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(ms.unit_royalties, 0), 2) as unitRoyalties'),
                                        DB::Raw('FORMAT(COALESCE(ms.royalties, 0), 2) as royalties'),
                                        'fs.consecutive as freightSettlementConsecutive',
                                        'ms.consecutive as materialSettlementConsecutive',
                                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                                        'mv.id as movementId',
                                        DB::Raw('DATE_FORMAT(fs.invoice_date, "%d/%m/%Y") as freightInvoiceDate'),
                                        'fs.invoice as freightInvoice',
                                        'fs.internal_document as freightInternalDocument',
                                        DB::Raw('DATE_FORMAT(ms.invoice_date, "%d/%m/%Y") as materialInvoiceDate'),
                                        'ms.invoice as materialInvoice',
                                        'ms.internal_document as materialInternalDocument'
                                    )
                                    ->join('tickets as t2', function($join)
                                         {
                                             $join->on('t1.referral_number', 't2.referral_number');
                                             $join->on('t2.type', DB::raw('"R"'));
                                         })
                                    ->join('yards as oy', 't1.origin_yard', 'oy.id')
                                    ->join('yards as dy', 't2.destiny_yard', 'dy.id')
                                    ->join('thirds as tc', 't1.conveyor_company', 'tc.id')
                                    ->join('materials as m', 't1.material', 'm.id')
                                    ->leftJoin('settlements as fs', 't1.freight_settlement', 'fs.id')
                                    ->leftJoin('settlements as ms', 't1.material_settlement', 'ms.id')
                                    ->leftJoin('movements_tickets as mt', 't1.id', 'mt.ticket')
                                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                                    ->where('t1.type', 'D')
                                    ->whereBetween('t1.date', [$startDate, $finalDate])
                                    ->when($movement === 'T' && $originYard !== 0, function ($q) use ($originYard) {
                                        return $q->where('t1.origin_yard', $originYard);
                                    })
                                    ->when($movement === 'T' && $destinyYard !== 0, function ($q) use ($destinyYard) {
                                        return $q->where('t1.destiny_yard', $destinyYard);
                                    })
                                    ->when($material !== 0, function ($q) use ($material) {
                                        return $q->where('t1.material', $material);
                                    });

         
                                    
                $saleTickets = $this->ticket::from('tickets as t')
                                    ->select(
                                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                                        DB::Raw('"VENTA" as movement'),
                                        't.receipt_number as receiptNumber',
                                        't.referral_number as referralNumber',
                                        't.license_plate as licensePlate',
                                        't.trailer_number as trailerNumber',
                                        't.driver_name as driverName',
                                        't.driver_document as driverDocument',
                                        'y.name as originYard',
                                        'tc.name as destinyYard',
                                        'tcc.name as conveyorCompany',
                                        'm.name as material',
                                        DB::Raw('FORMAT(t.net_weight, 2) as originNetWeight'),
                                        DB::Raw('0.00 as destiny_net_weight'),
                                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as originDate'),
                                        DB::Raw('"" as destinyDate'),
                                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(t.material_settlement_unit_value, 0), 2) as materialSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t.material_settlement_net_value, 0), 2) as materialSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(ms.unit_royalties, 0), 2) as unitRoyalties'),
                                        DB::Raw('FORMAT(COALESCE(ms.royalties, 0), 2) as royalties'),
                                        'fs.consecutive as freightSettlementConsecutive',
                                        'ms.consecutive as materialSettlementConsecutive',
                                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                                        'mv.id as movementId',
                                        DB::Raw('DATE_FORMAT(fs.invoice_date, "%d/%m/%Y") as freightInvoiceDate'),
                                        'fs.invoice as freightInvoice',
                                        'fs.internal_document as freightInternalDocument',
                                        DB::Raw('DATE_FORMAT(ms.invoice_date, "%d/%m/%Y") as materialInvoiceDate'),
                                        'ms.invoice as materialInvoice',
                                        'ms.internal_document as materialInternalDocument'
                                    )
                                    ->join('yards as y', 't.origin_yard', '=', 'y.id')
                                    ->leftJoin('thirds as tc', 't.customer', 'tc.id')
                                    ->leftJoin('thirds as tcc', 't.conveyor_company', 'tcc.id')
                                    ->join('materials as m', 't.material', '=', 'm.id')
                                    ->leftJoin('settlements as fs', 't.freight_settlement', '=', 'fs.id')
                                    ->leftJoin('settlements as ms', 't.material_settlement', '=', 'ms.id')
                                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                                    ->where('t.type', "=", 'V')
                                    ->whereBetween('t.date', [$startDate, $finalDate])
                                    ->when($movement === 'V' && $originYard !== 0, function ($q) use ($originYard) {
                                        return $q->where('t.origin_yard', $originYard);
                                    })
                                    ->when($movement === 'V' && $destinyYard !== 0, function ($q) use ($destinyYard) {
                                        return $q->where('t.customer', $destinyYard);
                                    })
                                    ->when($material !== 0, function ($q) use ($material) {
                                        return $q->where('t.material', $material);
                                    });

                $purchaseTickets = $this->ticket::from('tickets as t')
                                    ->select(
                                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                                        DB::Raw('"COMPRA" as movement'),
                                        't.receipt_number as receipt_Number',
                                        't.referral_number as referralNumber',
                                        't.license_plate as licensePlate',
                                        't.trailer_number as trailerNumber',
                                        't.driver_name as driverName',
                                        't.driver_document as driverDocument',
                                        'ts.name as originYard',
                                        'y.name as destinyYard',
                                        'tcc.name as conveyorCompany',
                                        'm.name as material',
                                        DB::Raw('0.00 as originNetWeight'),
                                        DB::Raw('FORMAT(t.net_weight, 2) as destinyNetWeight'),
                                        DB::Raw('"" as originDate'),
                                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as destinyDate'),
                                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(t.material_settlement_unit_value, 0), 2) as materialSettlementUnitValue'),
                                        DB::Raw('FORMAT(COALESCE(t.material_settlement_net_value, 0), 2) as materialSettlementNetValue'),
                                        DB::Raw('FORMAT(COALESCE(ms.unit_royalties, 0), 2) as unitRoyalties'),
                                        DB::Raw('FORMAT(COALESCE(ms.royalties, 0), 2) as royalties'),
                                        'fs.consecutive as freightSettlementConsecutive',
                                        'ms.consecutive as materialSettlementConsecutive',
                                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                                        'mv.id as movementId',
                                        DB::Raw('DATE_FORMAT(fs.invoice_date, "%d/%m/%Y") as freightInvoiceDate'),
                                        'fs.invoice as freightInvoice',
                                        'fs.internal_document as freightInternalDocument',
                                        DB::Raw('DATE_FORMAT(ms.invoice_date, "%d/%m/%Y") as materialInvoiceDate'),
                                        'ms.invoice as materialInvoice',
                                        'ms.internal_document as materialInternalDocument'
                                    )
                                    ->leftJoin('thirds as ts', 't.supplier', 'ts.id')
                                    ->join('yards as y', 't.destiny_yard', 'y.id')
                                    ->leftJoin('thirds as tcc', 't.conveyor_company', 'tcc.id')
                                    ->join('materials as m', 't.material', 'm.id')
                                    ->leftJoin('settlements as fs', 't.freight_settlement', 'fs.id')
                                    ->leftJoin('settlements as ms', 't.material_settlement', 'ms.id')
                                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                                    ->where('t.type', 'C')
                                    ->whereBetween('t.date', [$startDate, $finalDate])
                                    ->when($movement === 'C' && $originYard !== 0, function ($q) use ($originYard) {
                                        return $q->where('t.supplier', $originYard);
                                    })
                                    ->when($movement === 'C' && $destinyYard !== 0, function ($q) use ($destinyYard) {
                                        return $q->where('t.destiny_yard', $destinyYard);
                                    })
                                    ->when($material !== 0, function ($q) use ($material) {
                                        return $q->where('t.material', $material);
                                    });
            
                if($movement == '') {
                    $tickets = $transferTickets->union($saleTickets)->union($purchaseTickets);
                } else if($movement == 'T') {
                    $tickets = $transferTickets;
                } else if($movement == 'C') {
                    $tickets = $purchaseTickets;
                } else {
                    $tickets = $saleTickets;
                }
                                    
                $tickets = $tickets->get();
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function yardStock(string $date){
            try{
                $ticketsOut = $this->ticket::from('tickets as t')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        't.type as type',
                        't.net_weight as amount',
                        DB::Raw('"TONELADA" as unit')
                    )
                    ->join('yards as y', 't.origin_yard', 'y.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->where(function ($query) {
                        $query->where('type', 'D')
                            ->orWhere('type', 'V');
                        }
                    )
                    ->where('m.unit', 'T')
                    ->where('t.date', '<=', $date);
               
                $ticketsIn = $this->ticket::from('tickets as t')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        't.type as type',
                        't.net_weight as amount',
                        DB::Raw('"TONELADA" as unit')
                    )
                    ->join('yards as y', 't.destiny_yard', 'y.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->where(function ($query) {
                        $query->where('type', 'R')
                            ->orWhere('type', 'C');
                        }
                    )
                    ->where('m.unit', 'T')
                    ->where('t.date', '<=', $date);
                  
                $adjustment = $this->adjustment::from('adjustments as a')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        'a.type as type',
                        'a.amount as amount',
                        DB::Raw('"TONELADA" as unit')
                    )
                    ->join('yards as y', 'a.yard', 'y.id')
                    ->join('materials as m', 'a.material', 'm.id')
                    ->where('a.date', '<=', $date)
                    ->where('m.unit', 'T')
                    ->union($ticketsOut)
                    ->union($ticketsIn);
                                    
                $stocks = DB::table($adjustment)
                    ->select(
                        'yardName',
                        'materialName',
                        DB::Raw('FORMAT(SUM(IF(type = "C" OR type = "R" OR type = "A", amount, amount*(-1))), 2) as amount'),
                        'unit'
                    )
                    ->groupBy('yard', 'material')
                    ->get();
                
                if (count($stocks) > 0) {
                    return response()->json([
                        'data' => $stocks
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function completeTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard){
            try {
                $tickets = $this->ticket::from('tickets as t1')
                    ->select(
                        DB::Raw('"TRASLADO" as movement'),
                        't1.referral_number as referralNumber',
                        't2.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as originDate'),
                        DB::Raw('DATE_FORMAT(t2.date, "%d/%m/%Y") as destinyDate'),
                        't1.license_plate as originLicensePlate',
                        't2.license_plate as destinyLicensePlate',
                        't1.trailer_number as originTrailerNumber',
                        't2.trailer_number as destinyTrailerNumber',
                        't1.driver_name as originDriverName',
                        't2.driver_name as destinyDriverName',
                        't1.driver_document as originDriverDocument',
                        't2.driver_document as destinyDriverDocument',
                        'ooy.name as originOriginYard',
                        'ody.name as originDestinyYard',
                        'doy.name as destinyOriginYard',
                        'ddy.name as destinyDestinyYard',
                        'tcco.name as originConveyorCompanyName',
                        'tccd.name as destinyConveyorCompanyName',
                        'om.name as originMaterial',
                        'dm.name as destinyMaterial',
                        DB::Raw('FORMAT(t1.net_weight, 2) as originNetWeight'),
                        DB::Raw('FORMAT(t2.net_weight, 2) as destinyNetWeight'),
                        DB::Raw('FORMAT(ABS(t1.net_weight-t2.net_weight), 2) as weightDifference'),
                        DB::Raw('FORMAT(ABS((((ABS(t1.net_weight/t2.net_weight))*100)-100)), 2) as percentageWeightDifference')
                    )
                    ->join('tickets as t2', function($join) {
                        $join->on('t1.referral_number', 't2.referral_number');
                        $join->on('t2.type', DB::raw('"R"'));
                    })
                    ->join('materials as om', 't1.material', 'om.id')
                    ->join('materials as dm', 't2.material', 'dm.id')
                    ->join('yards as ooy', 't1.origin_yard', 'ooy.id')
                    ->join('yards as ody', 't1.destiny_yard', 'ody.id')
                    ->join('yards as doy', 't2.origin_yard', 'doy.id')
                    ->join('yards as ddy', 't2.destiny_yard', 'ddy.id')
                    ->join('thirds as tcco', 't1.conveyor_company', 'tcco.id')
                    ->join('thirds as tccd', 't2.conveyor_company', 'tccd.id')
                    ->where('t1.type', 'D')
                    ->where(function ($query) use ($startDate, $finalDate){
                        $query->whereBetween('t1.date', [$startDate, $finalDate])
                            ->orWhereBetween('t2.date', [$startDate, $finalDate]);
                    })
                    ->where(function ($query) use ($originYard){
                        $query->when($originYard !== 0, function ($q) use ($originYard) {
                            return $q->where('t1.origin_yard', $originYard)
                                ->orWhere('t2.origin_yard', $originYard);
                        });
                    })
                    ->where(function ($query) use ($destinyYard){
                        $query->when($destinyYard !== 0, function ($q) use ($destinyYard) {
                            return $q->where('t1.destiny_yard', $destinyYard)
                                ->orWhere('t2.destiny_yard', $destinyYard);
                        });
                    })
                    ->get();
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function uncompleteTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard){
            try {
                $ticketsRF = $this->ticket::from('tickets as t1')
                    ->select(
                        DB::Raw('"DESPACHO" as movement'),
                        't1.referral_number as referralNumber',
                        DB::Raw('"" as receiptNumber'),
                        't1.date as date',
                        't1.license_plate as licensePlate',
                        't1.trailer_number as trailerNumber',
                        't1.driver_name as driverName',
                        't1.driver_document as driverDocument',
                        'oy.name as originYard',
                        'dy.name as destinyYard',
                        'tcc.name as conveyorCompany',
                        'm.name as material',
                        't1.net_weight as netWeight'
                    )
                    ->leftJoin('tickets as t2', function($join) {
                        $join->on('t1.referral_number', 't2.referral_number');
                        $join->on('t2.type', DB::raw('"R"'));
                    })
                    ->join('materials as m', 't1.material', 'm.id')
                    ->join('yards as oy', 't1.origin_yard', 'oy.id')
                    ->join('yards as dy', 't1.destiny_yard', 'dy.id')
                    ->join('thirds as tcc', 't1.conveyor_company', 'tcc.id')
                    ->where('t1.type', '=', 'D')
                    ->when($originYard !== 0, function ($q) use ($originYard) {
                        return $q->where('t1.origin_yard', $originYard);
                    })
                    ->when($destinyYard !== 0, function ($q) use ($destinyYard) {
                        return $q->where('t1.destiny_yard', $destinyYard);
                    })
                    ->whereNull('t2.referral_number')
                    ->whereBetween('t1.date', [$startDate, $finalDate]);
                                
                $ticketsRC = $this->ticket::from('tickets as t1')
                    ->select(
                        DB::Raw('"RECEPCION" as movement'),
                        't2.referral_number as referralNumber',
                        't1.receipt_number as receiptNumber',
                        't1.date as date',
                        't1.license_plate as licensePlate',
                        't1.trailer_number as trailerNumber',
                        't1.driver_name as driverName',
                        't1.driver_document as driverDocument',
                        'oy.name as originYard',
                        'dy.name as destinyYard',
                        'tcc.name as conveyorCompany',
                        'm.name as material',
                        't1.net_weight as netWeight'
                    )
                    ->leftJoin('tickets as t2', function($join) {
                        $join->on('t1.referral_number', 't2.referral_number');
                        $join->on('t2.type', DB::raw('"D"'));
                    })
                    ->join('materials as m', 't1.material', 'm.id')
                    ->join('yards as oy', 't1.origin_yard', 'oy.id')
                    ->join('yards as dy', 't1.destiny_yard', 'dy.id')
                    ->join('thirds as tcc', 't1.conveyor_company', 'tcc.id')
                    ->where('t1.type', 'R')
                    ->when($originYard !== 0, function ($q) use ($originYard) {
                        return $q->where('t1.origin_yard', $originYard);
                    })
                    ->when($destinyYard !== 0, function ($q) use ($destinyYard) {
                        return $q->where('t1.destiny_yard', $destinyYard);
                    })
                    ->whereNull('t2.referral_number')
                    ->whereBetween('t1.date', [$startDate, $finalDate]);
                
                $tickets = $ticketsRF->union($ticketsRC)->get();

                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function unbilledPurchases(string $startDate, string $finalDate, int $supplier, int $material){
            try {
                $tickets = $this->ticket::from('tickets as t')
                    ->select(
                        DB::Raw('"COMPRA" as movement'),
                        't.referral_number as referralNumber',
                        't.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                        't.license_plate as licensePlate',
                        't.trailer_number as trailerNumber',
                        't.driver_name as driverName',
                        't.driver_document as driverDocument',
                        'ts.name as supplierName',
                        'y.name as destinyYard',
                        'm.name as material',
                        DB::Raw('FORMAT(COALESCE(t.net_weight, 0), 2) as net_weight'),
                        DB::Raw('FORMAT(COALESCE(t.material_settlement_unit_value, 0), 2) as materialSettlementUnitValue'),
                        DB::Raw('FORMAT(COALESCE(t.material_settlement_net_value, 0), 2) as materialSettlementNetValue'),
                        DB::Raw('FORMAT(COALESCE(s.unit_royalties, 0), 2) as unitRoyalties'),
                        DB::Raw('FORMAT(COALESCE(s.royalties, 0), 2) as royalties'),
                        's.consecutive as settlementConsecutive',
                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                        'mv.id as movementId'
                    )
                    ->leftJoin('settlements as s', 't.material_settlement','s.id')
                    ->join('thirds as ts', 't.supplier', 'ts.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->join('yards as y', 't.destiny_yard', 'y.id')
                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                    ->where('t.type', 'C')
                    ->whereBetween('t.date', [$startDate, $finalDate])
                    ->whereRaw('TRIM(COALESCE(s.invoice, "")) = ""')
                    ->when($supplier !== 0, function ($q) use ($supplier) {
                        return $q->where('t.supplier', $supplier);
                    })
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t.material', $material);
                    })
                    ->get();
                    
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function unbilledSales(string $startDate, string $finalDate, int $customer, int $material){
            try {
                $tickets = $this->ticket::from('tickets as t')
                    ->select(
                        DB::Raw('"VENTA" as movement'),
                        't.referral_number as referralNumber',
                        't.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                        't.license_plate as licensePlate',
                        't.trailer_number as trailerNumber',
                        't.driver_name as driverName',
                        't.driver_document as driverDocument',
                        'tc.name as customerName',
                        'y.name as destinyYard',
                        'm.name as material',
                        DB::Raw('FORMAT(COALESCE(t.net_weight, 0), 2) as netWeight'),
                        DB::Raw('FORMAT(COALESCE(t.material_settlement_unit_value, 0), 2) as materialSettlementUnitValue'),
                        DB::Raw('FORMAT(COALESCE(t.material_settlement_net_value, 0), 2) as materialSettlementNetValue'),
                        DB::Raw('FORMAT(COALESCE(s.unit_royalties, 0), 2) as unitRoyalties'),
                        DB::Raw('FORMAT(COALESCE(s.royalties, 0), 2) as royalties'),
                        's.consecutive as settlementConsecutive',
                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                        'mv.id as movementId'
                    )
                    ->leftJoin('settlements as s', 't.material_settlement', 's.id')
                    ->join('thirds as tc', 't.customer', 'tc.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->join('yards as y', 't.origin_yard', 'y.id')
                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                    ->where('t.type', 'V')
                    ->whereBetween('t.date', [$startDate, $finalDate])
                    ->whereRaw('TRIM(COALESCE(s.invoice, "")) = ""')
                    ->when($customer !== 0, function ($q) use ($customer) {
                        return $q->where('t.customer', $customer);
                    })
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t.material', $material);
                    })
                    ->get();
                
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function unbilledFreights(string $startDate, string $finalDate, int $conveyorCompany, int $material) {
            try {
                $transferTickets = $this->ticket::from('tickets as t1')
                    ->select(
                        DB::Raw('"TRASLADO" as movement'),
                        't1.referral_number as referralNumber',
                        't2.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as originDate'),
                        DB::Raw('DATE_FORMAT(t2.date, "%d/%m/%Y") as destinyDate'),
                        't1.license_plate as licensePlate',
                        't1.trailer_number as trailerNumber',
                        't1.driver_name as driverName',
                        't1.driver_document as driverDocument',
                        'oy.name as originYard',
                        'dy.name as destinyYard',
                        'm.name as material',
                        'tcc.name as conveyorCompany',
                        DB::Raw('FORMAT(COALESCE(t1.net_weight, 0), 2) as originNetWeight'),
                        DB::Raw('FORMAT(COALESCE(t2.net_weight, 0), 2) as destinyNetWeight'),
                        DB::Raw('FORMAT(COALESCE(t1.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                        DB::Raw('FORMAT(COALESCE(t1.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                        's.consecutive as settlementConsecutive',
                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                        'mv.id as movementId'
                    )
                    ->join('tickets as t2', function($join) {
                        $join->on('t1.referral_number', 't2.referral_number');
                        $join->on('t2.type', DB::raw('"R"'));
                    })
                    ->join('yards as oy', 't1.origin_yard', 'oy.id')
                    ->join('yards as dy', 't1.destiny_yard', 'dy.id')
                    ->join('materials as m', 't1.material', 'm.id')
                    ->join('thirds as tcc', 't1.conveyor_company', 'tcc.id')
                    ->leftJoin('settlements as s', 't1.freight_settlement', 's.id')
                    ->leftJoin('movements_tickets as mt', 't1.id', 'mt.ticket')
                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                    ->where('t1.type', 'D')
                    ->whereNull('s.invoice')
                    ->whereBetween('t1.date', [$startDate, $finalDate])
                    ->when($conveyorCompany !== 0, function ($q) use ($conveyorCompany) {
                        return $q->where('t1.conveyor_company', $conveyorCompany);
                    })
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t1.material', $material);
                    });
                 
                $purchaseTickets = $this->ticket::from('tickets as t')
                    ->select(
                        DB::Raw('"COMPRA" as movement'),
                        't.referral_number as referralNumber',
                        't.receipt_number as receiptNumber',
                        DB::Raw('"" as originDate'),
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as destiniyDate'),
                        't.license_plate as licensePlate',
                        't.trailer_number as trailerNumber',
                        't.driver_name as driverName',
                        't.driver_document as driverDocument',
                        'ts.name as originYard',
                        'y.name as destinyYard',
                        'm.name as material',
                        'tcc.name as conveyorCompany',
                        DB::Raw('"0.00" as originNetWeight'),
                        DB::Raw('FORMAT(COALESCE(t.net_weight, 0), 2) as destinyNetWeight'),
                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                        's.consecutive as settlementConsecutive',
                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                        'mv.id as movementId'
                    )
                    ->LeftJoin('settlements as s', 't.freight_settlement', 's.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->join('yards as y', 't.destiny_yard', 'y.id')
                    ->join('thirds as ts', 't.supplier', 'ts.id')
                    ->join('thirds as tcc', 't.conveyor_company', 'tcc.id')
                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                    ->where('t.type', 'C')
                    ->whereBetween('t.date', [$startDate, $finalDate])
                    ->whereNull('s.invoice')
                    ->when($conveyorCompany !== 0, function ($q) use ($conveyorCompany) {
                        return $q->where('t.conveyor_company', $conveyorCompany);
                    })
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t.material', $material);
                    });  

                $saleTickets = $this->ticket::from('tickets as t')
                    ->select(
                        DB::Raw('"VENTA" as movement'),
                        't.referral_number as referralNumber',
                        't.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as originDate'),
                        DB::Raw('"" as destiniyDate'),
                        't.license_plate as licensePlate',
                        't.trailer_number as trailerNumber',
                        't.driver_name as driverName',
                        't.driver_document as driverDocument',
                        'y.name as originYard',
                        'tc.name as destinyYard',
                        'm.name as material',
                        'tcc.name as conveyorCompany',
                        DB::Raw('FORMAT(COALESCE(t.net_weight, 0), 2) as originNetWeight'),
                        DB::Raw('"0.00" as destinyNetWeight'),
                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_unit_value, 0), 2) as freightSettlementUnitValue'),
                        DB::Raw('FORMAT(COALESCE(t.freight_settlement_net_value, 0), 2) as freightSettlementNetValue'),
                        's.consecutive as settlementConsecutive',
                        DB::Raw('DATE_FORMAT(mv.created_at, "%d/%m/%Y") as movementDate'),
                        'mv.id as movementId'
                    )
                    ->LeftJoin('settlements as s', 't.freight_settlement', '=', 's.id')
                    ->join('materials as m', 't.material', '=', 'm.id')
                    ->join('yards as y', 't.origin_yard', '=', 'y.id')
                    ->join('thirds as tc', 't.customer', 'tc.id')
                    ->join('thirds as tcc', 't.conveyor_company', 'tcc.id')
                    ->leftJoin('movements_tickets as mt', 't.id', 'mt.ticket')
                    ->leftJoin('movements as mv', 'mt.movement', 'mv.id')
                    ->where('t.type', 'V')
                    ->whereBetween('t.date', [$startDate, $finalDate])
                    ->whereNull('s.invoice')
                    ->when($conveyorCompany !== 0, function ($q) use ($conveyorCompany) {
                        return $q->where('t.conveyor_company', $conveyorCompany);
                    })
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t.material', $material);
                    });
                                    
                $tickets = $transferTickets->union($purchaseTickets)->union($saleTickets)->get();
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se obtuvieron registros',
                                'detail' => 'No existen movimientos que cumplan los criterios ingresados'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al realizar el reporte',
                            'detail' => 'Intente recargar la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>