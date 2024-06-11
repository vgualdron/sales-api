<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\MaterialSettlementServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Validator\SettlementAditionalInformationValidator;
    use App\Models\{ Ticket, Settlement, Movement };
    use App\Traits\{ Commons, Settlements };
    use Illuminate\Support\Facades\DB;
    
    class MaterialSettlementServiceImplement implements MaterialSettlementServiceInterface {

        use Commons, Settlements;

        private $ticket;
        private $settlement;
        private $movement;
        private $validator;

        function __construct(SettlementAditionalInformationValidator $validator){
            $this->ticket = new Ticket;
            $this->settlement = new Settlement;
            $this->movement = new Movement;
            $this->validator =  $validator;
        }

        function list () {
            try {
                $settlement = $this->settlement::from('settlements as s')
                    ->select(
                        's.id as id',
                        's.consecutive as consecutive',
                        DB::Raw('DATE_FORMAT(s.date, "%d/%m/%Y") as date'),
                        DB::Raw('CONCAT(t.nit, " / ", t.name) as third'),
                        DB::Raw('FORMAT(s.subtotal_settlement, 2) as subtotalSettlement'),
                        DB::Raw('FORMAT(s.retentions, 2) as retentions'),
                        DB::Raw('FORMAT(s.royalties, 2) as royalties'),
                        DB::Raw('FORMAT(s.total_settle, 2) as totalSettle'),
                        's.invoice as invoice',
                        's.internal_document as internalDocument'
                    )
                    ->leftJoin('thirds as t', 's.third', 't.id')
                    ->where('s.type', 'M')
                    ->get();
                if (count($settlement) > 0) {
                    return response()->json([
                        'data' => $settlement
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay liquidaciones de material para mostrar',
                                'detail' => 'Aún no se ha realizado ninguna liquidación'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar las liquidaciones',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getTickets(string $type, string $startDate, string $finalDate, int $third, int $material, string $materialType){
            try {
                $tickets = $this->ticket::from('tickets as t')
                    ->select(
                        't.id as id',
                        DB::Raw('CASE t.type WHEN "C" THEN "COMPRA" WHEN "V" THEN "VENTA" END as typeName'),
                        't.type as type',
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                        DB::Raw('IF(t.type = "C", t.receipt_number, "") as receiptNumber'),
                        't.referral_number as referralNumber',
                        'm.name as material',
                        DB::Raw('0 as auxNetWeight'),
                        DB::Raw('IF(t.type = "C", ts.name,  oy.name) as originYard'),
                        DB::Raw('IF(t.type = "V", tc.name,  dy.name) as destinyYard'),
                        DB::Raw('IF(m.unit = "U", 1, FORMAT(t.net_weight, 2)) as netWeight'),
                        DB::Raw('FORMAT(IF(t.type = "C", (COALESCE(r.material_price, 0) * IF(m.unit = "U", 1, t.net_weight)), COALESCE(r.total_price,0)), 2) as netPrice'),
                        'm.unit as materialUnit',
                        DB::Raw('FORMAT(IF(t.type = "C", COALESCE(r.material_price, 0), COALESCE(r.total_price, 0)), 2) as materialPrice')
                    )
                    ->join('materials as m', 't.material', '=', 'm.id')
                    ->leftJoin('thirds as ts', 't.supplier', '=', 'ts.id')
                    ->leftJoin('thirds as tc', 't.customer', '=', 'tc.id')
                    ->leftJoin('yards as oy', 't.origin_yard', '=', 'oy.id')
                    ->leftJoin('yards as dy', 't.destiny_yard', '=', 'dy.id')
                    ->leftJoin('rates as r', function($join) use($type) {
                        $join->on('t.type', '=', 'r.movement');
                        $join->when($type === 'C', function ($q) {
                            return $q->on('t.destiny_yard', 'r.destiny_yard')
                                ->on('t.supplier', 'r.supplier');
                        });
                        $join->when($type === 'V', function ($q) {
                            return $q->on('t.origin_yard', 'r.origin_yard')
                                ->on('t.customer', 'r.customer');
                        });
                        //$join->on(DB::Raw('IF("'.$type.'" = "C", t.destiny_yard, t.origin_yard)'), '=', DB::Raw('IF("'.$type.'" = "C", r.destiny_yard, r.origin_yard)'));
                        //$join->on(DB::Raw('IF("'.$type.'" = "C", t.supplier, t.customer)'), '=', DB::Raw('IF("'.$type.'" = "C", r.supplier, r.customer)'));
                        $join->on('t.date', '>=', 'r.start_date');
                        $join->on('t.date', '<=', 'r.final_date');
                        $join->on('t.material', '=', 'r.material');
                    })
                    ->where('t.type', '=', $type)
                    ->when($material !== 0, function ($q) use ($material) {
                        return $q->where('t.material', $material);
                    })
                    ->when($material === 0, function ($q) use ($materialType) {
                        return $q->where('m.unit', $materialType);
                    })
                    ->whereRaw('IF(t.type = "C", t.supplier, t.customer) = ?', [$third])
                    ->whereNull('t.material_settlement')
                    ->whereBetween('t.date', [$startDate, $finalDate])
                    ->orderBy('t.date', 'ASC')
                    ->get();
                if (count($tickets) > 0) {
                    return response()->json([
                        'data' => $tickets
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se pudieron obtener tiquetes',
                                'detail' => 'No hay tiquetes pendientes a liquidar'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                //dd($e->getMessage());
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los tiquetes para liquidar',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function settle(array $data) {
            try {
                $tickets = $data['tickets'];
                $ticketIds = array_column($tickets, 'id');
                $settleds = $this->ticket::select('id')
                    ->whereNotNull('material_settlement')
                    ->whereIn('id', $ticketIds)
                    ->get();
                $settlementToPrint = [];
                if(count($settleds) === 0) {
                    DB::transaction(function () use ($data, $tickets, $ticketIds, &$settlementToPrint) {
                        $searchConsecutive = $this->settlement::select(DB::Raw('MAX(CAST(consecutive AS UNSIGNED)) as consecutive'))
                            ->get()
                            ->first();             
                        $consecutive = $searchConsecutive->consecutive;
                        $consecutive = str_pad(((!empty($consecutive) ? $consecutive : 0) + 1), 10, "0", STR_PAD_LEFT);
                        $date = date('Y-m-d');
                        $settlement = $this->settlement::create([
                            'type' => $data['type'],
                            'date' => $date,
                            'third' => $data['third'],
                            'subtotal_amount' => $data['weightSubtotal'],
                            'subtotal_settlement' => $data['settledSubtotal'],
                            'retentions_percentage' => $data['retentionPercentage'],
                            'retentions' => $data['retention'],
                            'unit_royalties' => $data['baseRoyalties'],
                            'royalties' => $data['royalties'],
                            'total_settle' => $data['totalSettled'],
                            'start_date' => $data['startDate'],
                            'final_date' => $data['finalDate'],
                            'observation' => $data['observation'],
                            'consecutive' => $consecutive
                        ]);
                        $settlementId = $settlement->id;
                        $ticketsToUpdate = $this->ticket::whereIn('id', $ticketIds)
                            ->get()
                            ->toArray();
                        
                        foreach ($ticketsToUpdate as $index => $ticket) {
                            $key = array_search($ticket['id'], array_column($tickets, 'id'));
                            if($key !== false) {
                                $ticketsToUpdate[$index]['material_settlement'] = $settlementId;
                                $ticketsToUpdate[$index]['material_settlement_retention_percentage'] = $data['retentionPercentage'];
                                $ticketsToUpdate[$index]['material_settlement_royalties'] = $data['baseRoyalties'];
                                $ticketsToUpdate[$index]['material_settlement_unit_value'] = $tickets[$key]['unitValue'];
                                $ticketsToUpdate[$index]['material_settlement_net_value'] = $tickets[$key]['netValue'];
                                $ticketsToUpdate[$index]['material_settle_receipt_weight'] = $tickets[$key]['settleReceiptWeight'];
                                $ticketsToUpdate[$index]['material_weight_settled'] = $tickets[$key]['weightSettled'];
                                $ticketsToUpdate[$index]['created_at'] = null;
                                $ticketsToUpdate[$index]['updated_at'] = null;
                            }
                        }
                        $this->ticket::upsert($ticketsToUpdate,
                            ['id'], [
                                'material_settlement',
                                'material_settlement_retention_percentage',
                                'material_settlement_royalties',
                                'material_settlement_unit_value',
                                'material_settlement_net_value',
                                'material_settle_receipt_weight',
                                'material_weight_settled'
                            ]
                        );

                        $settlementToPrint = $this->getSettlementToPrint($settlementId);
                    });
                    return response()->json([
                        'data' => $settlementToPrint,
                        'message' => [
                            [
                                'text' => 'Liquidación finalizada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'La liquidación no se ha logrado completar',
                                'detail' => 'Uno o varios de los tiquetes que intenta liquidar ya cuentan con una liquidación de material'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al liquidar materiales',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function print(int $id) {
            try {
                $settlementToPrint = $this->getSettlementToPrint($id);
                if ($settlementToPrint !== null) {
                    return response()->json([
                        'data' => $settlementToPrint
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Error al imprimir la liquidación',
                                'detail' => 'la liquidación seleccionada no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al imprimir la liquidación',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function get(int $id) {
            try {
                $sql = $this->settlement::from('settlements as s')
                    ->select(
                        's.id as id',
                        's.consecutive as consecutive',
                        DB::Raw('DATE_FORMAT(s.date, "%d/%m/%Y") as date'),
                        DB::Raw('CONCAT(t.nit, " / ", t.name) as third'),
                        DB::Raw('FORMAt(s.total_settle, 2) as totalSettled'),
                        's.invoice as invoice',
                        DB::Raw('DATE_FORMAT(s.invoice_date, "%d/%m/%Y") as invoiceDate'),
                        's.internal_document as internalDocument'
                    )
                ->join('thirds as t', 's.third', 't.id')
                ->where('s.id', $id)   
                ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Error al cargar la liquidación',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                //dd($e->getMessage());
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al obtener la liquidación',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function addInformation(array $data, int $id){
            try {
                $validation = $this->validate($this->validator, $data, $id, 'agregar', 'información de liquidación', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->settlement::find($id);
                if(!empty($sql)) {
                    $sql->invoice = $data['invoice'];
                    $sql->invoice_date = $data['invoiceDate'];
                    $sql->internal_document = $data['internalDocument'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Información de liquidación actualizada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar información de liquidación',
                                'detail' => 'La liquidación no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar información de liquidación',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function validateMovements(int $id){
            try {
                $settlement = $this->settlement::select(
                    'id',
                    'consecutive',
                    'observation',
                    DB::Raw('FORMAT(retentions_percentage, 2)'),
                    DB::Raw('FORMAT(unit_royalties, 2)'),
                    'start_date',
                    'final_date',
                    'third'
                )
                ->where('id', $id)
                ->first();
                if (!empty($settlement)) {
                    $tickets = $this->ticket->select('id')
                        ->where('material_settlement', $id)
                        ->get()
                        ->toArray();
                    if (count($tickets) > 0) {
                        $ticketIds = array_column($tickets, 'id');
                        $movements = $this->movement->from('movements as m')
                            ->select('m.id')
                            ->join('movements_tickets as mt', 'm.id', 'mt.movement')
                            ->whereIn('mt.ticket', $ticketIds)
                            ->distinct()
                            ->get()
                            ->toArray();
                        $movementIds = array_column($movements, 'id');
                        return response()->json([
                           'data' => $movementIds
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Advertencia al validar movimientos',
                                    'detail' => 'Esta liquidación no tiene tiquetes asociados'
                                ]
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al validar movimientos',
                                'detail' => 'La liquidación seleccionada no existe'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al validar movimientos',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getSettledTickets(int $id) {
            try {
                $settlement = $this->settlement::find($id);
                if(empty($settlement)) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido obtener la información de la liquidación',
                                'detail' => 'Esta liquidación no existe'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $tickets = $this->ticket::from('tickets as t')->select(
                    't.id as id',
                    't.type as type',
                    DB::Raw('CASE t.type WHEN "C" THEN "COMPRA" WHEN "V" THEN "VENTA" END as typeName'),
                    DB::Raw('FORMAT(IF(t.material_settle_receipt_weight = 0, 0, t.material_weight_settled), 2) as auxNetWeight'),
                    DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                    DB::Raw('IF(t.type = "V", tc.name,  dy.name) as destinyYard'),
                    'm.name as material',
                    DB::Raw('FORMAT(t.material_settlement_unit_value, 2) as materialPrice'),
                    'm.unit as materialUnit',
                    DB::Raw('FORMAT(t.material_settlement_net_value, 2) as netPrice'),
                    DB::Raw('FORMAT(t.net_weight, 2) as netWeight'),
                    DB::Raw('IF(t.type = "C", ts.name,  oy.name) as originYard'),
                    DB::Raw('IF(t.type = "C", t.receipt_number, "") as receiptNumber'),
                    't.referral_number as referralNumber',
                    't.material_settle_receipt_weight as settleReceiptWeight'
                )
                ->join('materials as m', 't.material', '=', 'm.id')
                ->leftJoin('thirds as ts', 't.supplier', '=', 'ts.id')
                ->leftJoin('thirds as tc', 't.customer', '=', 'tc.id')
                ->leftJoin('yards as oy', 't.origin_yard', '=', 'oy.id')
                ->leftJoin('yards as dy', 't.destiny_yard', '=', 'dy.id')
                ->where('material_settlement', $id)
                ->orderBy('t.date', 'ASC')
                ->get();
                
                $settlement = [
                    'id' => $settlement->id,
                    'consecutive' => $settlement->consecutive,
                    'observation' => $settlement->observation,
                    'retention' => $settlement->retentions_percentage,
                    'royalties' => $settlement->unit_royalties,
                    'startDateSettled' => $settlement->start_date,
                    'finalDateSettled' => $settlement->final_date,
                    'thirdSettled' => $settlement->third
                ];

                $data = [
                    'tickets' => $tickets,
                    'settlement' => $settlement
                ];

                return response()->json([
                    'data' => $data
                 ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al obtener los tiquetes de la liquidación',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $data, int $id) {
            try {
                $settlement = $this->settlement::find($id);
                if(empty($settlement)) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido obtener la información de la liquidación',
                                'detail' => 'Esta liquidación no existe'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if(count($data) === 0) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido actualizar la liquidación',
                                'detail' => 'Faltan datos para completar el proceso'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $tickets = $data['tickets'];
                $ticketIds = array_column($tickets, 'id');
                $ticketsToUpdate = $this->ticket::whereIn('id', $ticketIds)
                    ->get()
                    ->toArray();
                if(count($ticketsToUpdate) === 0) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido actualizar la liquidación',
                                'detail' => 'No se han proporcionado datos de tiquetes'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $settlementToPrint = [];
                $settlement->subtotal_amount = $data['weightSubtotal'];
                $settlement->subtotal_settlement = $data['settledSubtotal'];
                $settlement->retentions_percentage = $data['retentionPercentage'];
                $settlement->retentions = $data['retention'];
                $settlement->unit_royalties = $data['baseRoyalties'];
                $settlement->royalties = $data['royalties'];
                $settlement->total_settle = $data['totalSettled'];
                $settlement->observation = $data['observation'];
                foreach ($ticketsToUpdate as $index => $ticket) {
                    $key = array_search($ticket['id'], array_column($tickets, 'id'));
                    if($key !== false) {
                        $ticketsToUpdate[$index]['material_settlement_retention_percentage'] = $data['retentionPercentage'];
                        $ticketsToUpdate[$index]['material_settlement_royalties'] = $data['baseRoyalties'];
                        $ticketsToUpdate[$index]['material_settlement_unit_value'] = $tickets[$key]['unitValue'];
                        $ticketsToUpdate[$index]['material_settlement_net_value'] = $tickets[$key]['netValue'];
                        $ticketsToUpdate[$index]['material_settle_receipt_weight'] = $tickets[$key]['settleReceiptWeight'];
                        $ticketsToUpdate[$index]['material_weight_settled'] = $tickets[$key]['weightSettled'];
                    }
                }
                DB::transaction(function () use($settlement, $ticketsToUpdate) {
                    $settlement->save();
                    $this->ticket::upsert($ticketsToUpdate,
                        ['id'], [
                            'material_settlement_retention_percentage',
                            'material_settlement_royalties',
                            'material_settlement_unit_value',
                            'material_settlement_net_value',
                            'material_settle_receipt_weight',
                            'material_weight_settled'
                        ]
                    );
                });
                $settlementToPrint = $this->getSettlementToPrint($id);
                return response()->json([
                    'data' => $settlementToPrint,
                    'message' => [
                        [
                            'text' => 'Actualización finalizada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al actualizar la liquidación',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id) {
            try {
                $settlement = $this->settlement::find($id);
                if(is_null($settlement)) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido obtener la información de la liquidación',
                                'detail' => 'Esta liquidación no existe'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                DB::transaction(function () use($id, $settlement) {
                    $this->ticket::where('material_settlement', $id)
                    ->update([
                        'material_settlement' => null,
                        'material_settlement_retention_percentage' => 0,
                        'material_settlement_royalties' => 0,
                        'material_settlement_unit_value' => 0,
                        'material_settlement_net_value' => 0,
                        'material_settle_receipt_weight' => 0,
                        'material_weight_settled' => 0
                    ]);
                    $settlement->delete();
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Liquidación eliminada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al eliminar la liquidación',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>