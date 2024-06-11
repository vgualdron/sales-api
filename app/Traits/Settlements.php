<?php
    namespace App\Traits;
    use App\Models\{ Ticket, Settlement };
    use Illuminate\Support\Facades\DB;

    trait Settlements
    {
        private $ticket;
        private $settlement;

        function __construct(){
            $this->ticket = new Ticket;
            $this->settlement = new Settlement;
        }
    
        public function getSettlementToPrint($id) {
            $settlement = $this->settlement::from('settlements as s')
                ->select(
                    's.id as id',
                    's.type as type',
                    's.consecutive as consecutive',
                    DB::Raw('DATE_FORMAT(s.date, "%d/%m/%Y") as date'),
                    DB::Raw('CONCAT(t.nit, " / ", t.name) third'),
                    DB::Raw('FORMAT(s.subtotal_amount, 2) as subtotalAmount'),
                    DB::Raw('FORMAT(s.subtotal_settlement, 2) as subtotalSettlement'),
                    DB::Raw('FORMAT(s.retentions_percentage, 2) as retentionsPercentage'),
                    DB::Raw('FORMAT(s.retentions, 2) as retentions'),
                    DB::Raw('FORMAT(s.unit_royalties, 2) as unitRoyalties'),
                    DB::Raw('FORMAT(s.royalties, 2) as royalties'),
                    DB::Raw('FORMAT(s.total_settle, 2) as totalSettle'),
                    DB::Raw('COALESCE(observation, "") as observation'),
                    DB::Raw('COALESCE(s.invoice, "") as invoice'),
                    DB::Raw('COALESCE(s.invoice_date, "") as invoiceDate'),
                    's.internal_document as internalDocument',
                    DB::Raw('DATE_FORMAT(s.start_date, "%d/%m/%Y") as startDate'),
                    DB::Raw('DATE_FORMAT(s.final_date, "%d/%m/%Y") as finalDate')
                )
                ->join('thirds as t', 's.third', 't.id')
                ->where('s.id', $id)
                ->first();
            if ($settlement === null) {
                return null;
            }
            $settlement = $settlement->toArray();
            $settlementType = $settlement['type'];
            $select = [
                DB::Raw('CASE t1.type WHEN "D" THEN "TRASLADO" WHEN "C" THEN "COMPRA" ELSE "VENTA" END as type'),
                't1.referral_number as referralNumber',
                DB::Raw('DATE_FORMAT(t1.date, "%d/%m/%Y") as date'),
                DB::Raw('IF(COALESCE(t2.receipt_number, "") <> "", t2.receipt_number, t1.receipt_number) as receiptNumber'),
                DB::Raw('CASE t1.type WHEN "D" THEN dy.name WHEN "C" THEN dy.name ELSE CONCAT(tc.nit, " / ", tc.name) END as destinyYard'),
                DB::Raw('CASE t1.type WHEN "D" THEN oy.name WHEN "C" THEN CONCAT(ts.nit, " / ", ts.name) ELSE oy.name END as originYard'),
                't1.license_plate as licensePlate',
                'm.name as materialName'
            ];

            if($settlementType === 'M') {
                $materialSelect = [
                    DB::Raw('FORMAT(t1.material_settlement_unit_value, 2) as unitValue'),
                    't1.material_settle_receipt_weight as materialSettleReceiptWeight',
                    DB::Raw('FORMAT(t1.material_weight_settled, 2) as materialWeightSettled'),
                    DB::Raw('FORMAT(t1.material_settlement_net_value, 2) as materialSettlementNetValue')
                ];
                $select = array_merge($select, $materialSelect);
            } else {
                $freightSelect = [
                    DB::Raw('FORMAT(t1.freight_settlement_unit_value, 2) as unitValue'),
                    't1.freight_settle_receipt_weight as freightSettleReceiptWeight',
                    DB::Raw('FORMAT(t1.freight_weight_settled, 2) as freightWeightSettled'),
                    DB::Raw('FORMAT(t1.freight_settlement_net_value, 2) as freightSettlementNetValue'),
                    DB::Raw('IF(t1.round_trip = 0, t2.round_trip, t1.round_trip) as roundTrip')
                ];
                $select = array_merge($select, $freightSelect);
            }

            $tickets = $this->ticket::from('tickets as t1')
                ->select($select)
                ->leftJoin('tickets as t2', function($join) {
                    $join->on('t1.referral_number', '=', 't2.referral_number');
                    $join->on('t2.type', '=', DB::raw('"R"'));
                })
                ->join('materials as m', 't1.material', '=', 'm.id')
                ->leftJoin('thirds as ts', 't1.supplier', '=', 'ts.id')
                ->leftJoin('thirds as tc', 't1.customer', '=', 'tc.id')
                ->leftJoin('yards as oy', 't1.origin_yard', '=', 'oy.id')
                ->leftJoin('yards as dy', 't1.destiny_yard', '=', 'dy.id')
                ->when($settlementType === 'M', function ($q) use ($id) {
                    return $q->where('t1.material_settlement', $id);
                })
                ->when($settlementType === 'F', function ($q) use ($id) {
                    return $q->where('t1.freight_settlement', $id);
                })
                ->where(function ($query) {
                    $query->where('t1.type', 'D')
                        ->orWhere('t1.type', 'C')
                        ->orWhere('t1.type', 'V');
                })
                ->get()
                ->toArray();
            $settlement['tickets'] = $tickets;

            return $settlement;
        }
    }  
?>