<?php
    namespace App\Traits;
    use App\Models\{ Ticket };
    use Illuminate\Support\Facades\DB;

    trait Movements
    {
        private $ticket;

        function __construct(){
            $this->ticket = new Ticket;
        }
    
        public function getTicketsByDate(string $startDate, string $finalDate) {
            $transferTickets = $this->ticket::from('tickets as t1')
                ->select(
                    'oz.code as PREFIJO',
                    'oy.code as CCOSTO',
                    't1.referral_number as NUMERO',
                    DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as FECHA'),
                    DB::Raw('"00" as ORIGEN'),
                    DB::Raw('"00" as DESTINO'),
                    't1.license_plate as PLACA', 
                    DB::Raw('CONCAT(oz.code, oy.code, om.code) as ART1'), 
                    DB::Raw('"00" as BODEGA'),
                    DB::Raw('CONCAT(dz.code,dy.code,dm.code) as ART2'),
                    DB::Raw('"00" as BODEGA2'),
                    DB::Raw('FORMAT(t1.gross_weight, 2) as BRUTO'),
                    DB::Raw('FORMAT(t1.tare_weight, 2) as TARA'),
                    DB::Raw('IF(om.unit <> "U", t1.net_weight, 1) as NETO'),
                    DB::Raw('0 as TARIFAC'),
                    DB::Raw('FORMAT(t1.freight_settlement_unit_value, 2) as TARIFAT'),
                    DB::Raw('"T" as TIPOES'),
                    DB::Raw('TRIM(CONCAT(COALESCE(t1.observation, ""), "   ", COALESCE(t2.observation, ""))) as OBS'),
                    DB::Raw('COALESCE(tcc.nit, "") as NITTRANS'),
                    't1.id as TICKET'
                )
                ->join('tickets as t2', function($join){
                    $join->on('t1.referral_number', '=', 't2.referral_number');
                    $join->on('t2.type','=',DB::raw('"R"'));
                })
                ->join('thirds as tcc', 't1.conveyor_company', 'tcc.id')
                ->join('yards as oy', 't1.origin_yard', '=', 'oy.id')
                ->join('zones as oz', 'oy.zone', '=', 'oz.id')
                ->join('yards as dy', 't1.destiny_yard', '=', 'dy.id')
                ->join('zones as dz', 'dy.zone', '=', 'dz.id')
                ->join('materials as om', 't1.material', '=', 'om.id')
                ->join('materials as dm', 't2.material', '=', 'dm.id')
                ->leftJoin('movements_tickets as mt', 't1.id', '=', 'mt.ticket')
                ->where('t1.type', '=', 'D')
                ->whereNotNull('t1.freight_settlement')
                ->whereBetween('t1.date', [$startDate, $finalDate])
                ->whereNull('mt.ticket'); 
            
            $purchaseSaleTickets = $this->ticket::from('tickets as t')
                ->select(
                    DB::Raw('IF(t.type = "C", dz.code, oz.code) as PREFIJO'),
                    DB::Raw('IF(t.type = "C", dy.code, oy.code) as CCOSTO'), 
                    DB::Raw('IF(t.type = "V", t.referral_number, t.receipt_number) as NUMERO'),
                    DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as FECHA'),
                    DB::Raw('IF(t.type = "C", ts.nit, "00") as ORIGEN'),
                    DB::Raw('IF(t.type = "V", tc.nit, "00") as DESTINO'),
                    't.license_plate as PLACA',
                    DB::Raw('CONCAT(IF(t.type = "C", dz.code, oz.code), IF(t.type = "C", dy.code, oy.code), m.code) as ART1'),
                    DB::Raw('"00" as BODEGA'),
                    DB::Raw('"00" as ART2'),
                    DB::Raw('"00" as BODEGA2'),
                    DB::Raw('FORMAT(t.gross_weight, 2) as BRUTO'),
                    DB::Raw('FORMAT(t.tare_weight, 2) as TARA'),
                    DB::Raw('IF(m.unit <> "U", FORMAT(t.net_weight, 2), 1) as NETO'),
                    DB::Raw('FORMAT(t.material_settlement_unit_value, 2) as TARIFAC'),
                    DB::Raw('FORMAT(COALESCE(t.freight_settlement_unit_value, 0), 2) as TARIFAT'),
                    DB::Raw('IF(type = "C", "E", "S") as TIPOES'),
                    DB::Raw('COALESCE(t.observation, "") as OBS'),
                    DB::Raw('COALESCE(tcc.nit, "") as NITTRANS'),
                    't.id as TICKET'
                )
                ->leftJoin('thirds as ts', 't.supplier', 'ts.id')
                ->leftJoin('thirds as tc', 't.customer', 'tc.id')
                ->leftJoin('thirds as tcc', 't.conveyor_company', 'tcc.id')
                ->leftJoin('yards as oy', 't.origin_yard', '=', 'oy.id')
                ->leftJoin('zones as oz', 'oy.zone', '=', 'oz.id')
                ->leftJoin('yards as dy', 't.destiny_yard', '=', 'dy.id')
                ->leftJoin('zones as dz', 'dy.zone', '=', 'dz.id')
                ->join('materials as m', 't.material', '=', 'm.id')
                ->leftJoin('movements_tickets as mt', 't.id', '=', 'mt.ticket')
                ->where(function ($query) {
                    $query->where('t.type', '=', 'C')
                        ->orWhere('t.type', '=', 'V');
                })
                //->whereNotNull('t.freight_settlement')
                //->whereRaw('IF(tcc.conveyor_company IS NOT NUL, t.freight_settlement IS NOT NULL, TRUE)')
                ->whereNotNull('t.material_settlement')
                ->whereBetween('t.date', [$startDate, $finalDate])
                ->whereNull('mt.ticket'); 
            
            $movements = $transferTickets->union($purchaseSaleTickets)->get();

            return $movements;
        }

        public function getTicketsById(array $tickets) {
            $transferTickets = $this->ticket::from('tickets as t1')
                ->select(
                    DB::Raw('REPLACE(oz.code, ";", "") as PREFIJO'),
                    DB::Raw('REPLACE(oy.code, ";", "") as CCOSTO'),
                    DB::Raw('REPLACE(t1.referral_number, ";", "") as NUMERO'),
                    DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as FECHA'),
                    DB::Raw('"00" as ORIGEN'),
                    DB::Raw('"00" as DESTINO'),
                    DB::Raw('REPLACE(t1.license_plate, ";", "") as PLACA'), 
                    DB::Raw('REPLACE(CONCAT(oz.code, oy.code, om.code), ";", "") as ART1'), 
                    DB::Raw('"00" as BODEGA'),
                    DB::Raw('REPLACE(CONCAT(dz.code,dy.code,dm.code), ";", "") as ART2'),
                    DB::Raw('"00" as BODEGA2'),
                    't1.gross_weight as BRUTO',
                    't1.tare_weight as TARA',
                    DB::Raw('IF(om.unit <> "U", t1.net_weight, 1) as NETO'),
                    DB::Raw('0 as TARIFAC'),
                    't1.freight_settlement_unit_value as TARIFAT',
                    DB::Raw('"T" as TIPOES'),
                    DB::Raw('REPLACE(TRIM(CONCAT(COALESCE(t1.observation, ""), "   ", COALESCE(t2.observation, ""))), ";", "") as OBS'),
                    DB::Raw('REPLACE(COALESCE(tcc.nit, ""), ";", "") as NITTRANS'),
                    't1.id as TICKET',
                    'mv.id as CONSECUTIVO'
                )
                ->join('tickets as t2', function($join){
                    $join->on('t1.referral_number', '=', 't2.referral_number');
                    $join->on('t2.type','=',DB::raw('"R"'));
                })
                ->join('thirds as tcc', 't1.conveyor_company', 'tcc.id')
                ->join('yards as oy', 't1.origin_yard', '=', 'oy.id')
                ->join('zones as oz', 'oy.zone', '=', 'oz.id')
                ->join('yards as dy', 't1.destiny_yard', '=', 'dy.id')
                ->join('zones as dz', 'dy.zone', '=', 'dz.id')
                ->join('materials as om', 't1.material', '=', 'om.id')
                ->join('materials as dm', 't2.material', '=', 'dm.id')
                ->join('movements_tickets as mt', 't1.id', '=', 'mt.ticket')
                ->join('movements as mv', 'mt.movement', 'mv.id')
                ->where('t1.type', '=', 'D')
                ->whereIn('t1.id', $tickets); 
            
            $purchaseSaleTickets = $this->ticket::from('tickets as t')
                ->select(
                    DB::Raw('REPLACE(IF(t.type = "C", dz.code, oz.code), ";", "") as PREFIJO'),
                    DB::Raw('REPLACE(IF(t.type = "C", dy.code, oy.code), ";", "") as CCOSTO'), 
                    DB::Raw('REPLACE(IF(t.type = "V", t.referral_number, t.receipt_number), ";", "") as NUMERO'),
                    DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as FECHA'),
                    DB::Raw('REPLACE(IF(t.type = "C", ts.nit, "00"), ";", "") as ORIGEN'),
                    DB::Raw('REPLACE(IF(t.type = "V", tc.nit, "00"), ";", "") as DESTINO'),
                    DB::Raw('REPLACE(t.license_plate, ";", "") as PLACA'), 
                    DB::Raw('REPLACE(CONCAT(IF(t.type = "C", dz.code, oz.code), IF(t.type = "C", dy.code, oy.code), m.code), ";", "") as ART1'),
                    DB::Raw('"00" as BODEGA'),
                    DB::Raw('"00" as ART2'),
                    DB::Raw('"00" as BODEGA2'),
                    't.gross_weight as BRUTO',
                    't.tare_weight as TARA',
                    DB::Raw('IF(m.unit <> "U", t.net_weight, 1) as NETO'),
                    't.material_settlement_unit_value as TARIFAC',
                    DB::Raw('COALESCE(t.freight_settlement_unit_value, 0) as TARIFAT'),
                    DB::Raw('IF(type = "C", "E", "S") as TIPOES'),
                    DB::Raw('REPLACE(COALESCE(t.observation, ""), ";", "") as OBS'),
                    DB::Raw('REPLACE(COALESCE(tcc.nit, ""), ";", "") as NITTRANS'),
                    't.id as TICKET',
                    'mv.id as CONSECUTIVO'
                )
                ->leftJoin('thirds as ts', 't.supplier', 'ts.id')
                ->leftJoin('thirds as tc', 't.customer', 'tc.id')
                ->leftJoin('thirds as tcc', 't.conveyor_company', 'tcc.id')
                ->leftJoin('yards as oy', 't.origin_yard', '=', 'oy.id')
                ->leftJoin('zones as oz', 'oy.zone', '=', 'oz.id')
                ->leftJoin('yards as dy', 't.destiny_yard', '=', 'dy.id')
                ->leftJoin('zones as dz', 'dy.zone', '=', 'dz.id')
                ->join('materials as m', 't.material', '=', 'm.id')
                ->join('movements_tickets as mt', 't.id', '=', 'mt.ticket')
                ->join('movements as mv', 'mt.movement', 'mv.id')
                ->where(function ($query) {
                    $query->where('t.type', '=', 'C')
                        ->orWhere('t.type', '=', 'V');
                })
                ->whereIn('t.id', $tickets);
            
            $movements = $transferTickets->union($purchaseSaleTickets)->get();

            return $movements;
        }
    }  
?>