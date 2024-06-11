<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\SynchronizationServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\{
        Ticket,
        Yard,
        Material,
        Third,
    };
    use App\Validator\TicketValidator;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\{DB, Auth};
    use DateTime;

    class SynchronizationServiceImplement implements SynchronizationServiceInterface {

        use Commons;

        private $ticket;
        private $yard;
        private $material;
        private $third;
        private $validator;

        function __construct(TicketValidator $validator){
            $this->ticket = new Ticket;
            $this->yard = new Yard;
            $this->material = new Material;
            $this->third = new Third;
            $this->validator = $validator;
        }    

        function synchronize(array $data){
            try {
                $ticketsToSkip = [];
                $arrayWarnings = [
                    'tus' => [],
                    'tds' => [],
                    'tu' => [],
                    'tc' => [],
                ];
                //search for settled tickets to skip them in the delete
                $ticketsToDelete = array_filter($data, function ($ticket) {
                    return $ticket['synchronized'] === 1 && $ticket['deleted'] === 1;
                });
                if(count($ticketsToDelete) > 0) {
                    $ticketsToDeleteIds = array_column($ticketsToDelete, 'id');
                    $ticketsToSkip = array_merge($ticketsToSkip, $ticketsToDeleteIds);
                    $settledTicketsDelete = $this->ticket::select('id', 'type', 'referral_number', 'receipt_number')
                        ->where(function($query) {
                            $query->whereNotNull('freight_settlement');
                            $query->orWhereNotNull('material_settlement');
                        })
                        ->whereIn('id', $ticketsToDeleteIds)
                        ->get();
                    if(count($settledTicketsDelete) > 0) {
                        $ticketIds = $settledTicketsDelete->pluck('id')->toArray();
                        $ticketsToDelete = array_diff($ticketsToDeleteIds, $ticketIds);
                        foreach ($settledTicketsDelete as $ticketUpdate) {
                            $type = $ticketUpdate['type'];
                            $arrayWarnings['tds'][] = 'Tiquete de '.($type === 'D' ? 'despacho' : ($type === 'R' ? 'recepción' : ($type === 'C' ? 'compra' : 'venta'))). ' con número de '.($type === 'D' || $type === 'V' ? 'remisión' : 'recibo').' "'.($type === 'D' || $type === 'V' ? $ticketUpdate['referral_number'] : $ticketUpdate['receipt_number']).'"';
                        }
                    }
                }
                //search for settled tickets to skip them in the update
                $ticketsToUpdate = array_filter($data, function ($ticket) {
                    return $ticket['synchronized'] === 1 && $ticket['deleted'] === 0 && $ticket['id'] !== null;
                });
                if(count($ticketsToUpdate) > 0) {
                    $ticketsToUpdateIds = array_column($ticketsToUpdate, 'id');
                    $settledTicketsUpdate = $this->ticket::select('id', 'type', 'referral_number', 'receipt_number')
                        ->where(function($query) {
                            $query->whereNotNull('freight_settlement');
                            $query->orWhereNotNull('material_settlement');
                        })
                        ->whereIn('id', $ticketsToUpdateIds)
                        ->get();
                    if(count($settledTicketsUpdate) > 0) {
                        $ticketIds = $settledTicketsUpdate->pluck('id')->toArray();
                        $ticketsToSkip = array_merge($ticketsToSkip, $ticketIds);
                        foreach ($settledTicketsUpdate as $ticketUpdate) {
                            $type = $ticketUpdate['type'];
                            $arrayWarnings['tus'][] = 'Tiquete de '.($type === 'D' ? 'despacho' : ($type === 'R' ? 'recepción' : ($type === 'C' ? 'compra' : 'venta'))). ' con número de '.($type === 'D' || $type === 'V' ? 'remisión' : 'recibo').' "'.($type === 'D' || $type === 'V' ? $ticketUpdate['referral_number'] : $ticketUpdate['receipt_number']).'"';
                        }
                    }
                }
                // set the tickets that will be updated or created
                $ticketsToSaveOrUpdate = array_filter($data, function ($ticket) use ($ticketsToSkip) {
                    return in_array($ticket['id'], $ticketsToSkip) === false;
                });
                $finalTicketsToSaveOrUpdate = [];
                if(count($ticketsToSaveOrUpdate) > 0) {
                    foreach ($ticketsToSaveOrUpdate as $ticket) {
                        $validation = $this->validate($this->validator, $ticket, $ticket['id'], (empty($ticket['id']) ? 'registrar' : 'actualizar'), 'tiquete', null);
                        if ($validation['success'] === false) {
                            $validationMessage = $validation['message'][0]['detail'];
                            $arrayWarnings[empty($ticket['id']) ? 'tc' : 'tu'][] = $validationMessage;
                        } else {
                            $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                            $finalTicketsToSaveOrUpdate[] = [
                                'id' => $ticket['id'],
                                'type' => $ticket['type'],
                                'user' => $ticket['user'],
                                'origin_yard' => $ticket['originYard'],
                                'destiny_yard' => $ticket['destinyYard'],
                                'supplier' => $ticket['supplier'],
                                'customer' => $ticket['customer'],
                                'material' => $ticket['material'],
                                'ash_percentage' => $ticket['ashPercentage'],
                                'referral_number' => $ticket['referralNumber'],
                                'receipt_number' => $ticket['receiptNumber'],
                                'date' => $ticket['date'],
                                'time' => $ticket['time'],
                                'license_plate' => $ticket['licensePlate'],
                                'trailer_number' => $ticket['trailerNumber'],
                                'driver_document' => $ticket['driverDocument'],
                                'driver_name' => $ticket['driverName'],
                                'gross_weight' => $ticket['grossWeight'],
                                'tare_weight' => $ticket['tareWeight'],
                                'net_weight' => $ticket['netWeight'],
                                'conveyor_company' => $ticket['conveyorCompany'],
                                'observation' => $ticket['observation'],
                                'seals' => $ticket['seals'],
                                'round_trip' => $ticket['roundTrip'],
                                'local_created_at' => $ticket['localCreatedAt'],
                                'consecutive' => $ticket['id'] !== null ? $ticket['consecutive'] : (md5(Auth::id().'/'.$now->format('m-d-Y H:i:s.u')))
                            ];
                        }
                    }
                }
                
                if(count($arrayWarnings['tc']) === 0 && count($arrayWarnings['tu']) === 0 && count($arrayWarnings['tus']) === 0) {
                    DB::transaction(function () use ($finalTicketsToSaveOrUpdate, $ticketsToDelete) {
                        if(count($finalTicketsToSaveOrUpdate) > 0) {
                            $this->ticket::upsert($finalTicketsToSaveOrUpdate,
                                ['id'], [
                                    'type',
                                    'origin_yard',
                                    'destiny_yard',
                                    'supplier',
                                    'customer',
                                    'material',
                                    'ash_percentage',
                                    'referral_number',
                                    'receipt_number',
                                    'date',
                                    'time',
                                    'license_plate',
                                    'trailer_number',
                                    'driver_document',
                                    'driver_name',
                                    'gross_weight',
                                    'tare_weight',
                                    'net_weight',
                                    'conveyor_company',
                                    'observation',
                                    'seals',
                                    'round_trip'
                                ]
                            );
                        }
                        if(count($ticketsToDelete) > 0) {
                            $ticketsToDeleteIds = array_column($ticketsToDelete, 'id');
                            $this->ticket::whereIn('id', $ticketsToDeleteIds)
                                ->delete();
                        }
                    });
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se ha podido finalizar la sincronización',
                                'detail' => 'Por favor verifique las advertencias'
                            ]
                        ],
                        'warnings' => $arrayWarnings
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                // get data to send to local
                $data = [];
                
                $data['yards'] = $this->yard
                    ->select('id', 'code', 'name', 'active')
                    ->get();
                
                $data['materials'] = $this->material
                    ->select('id', 'code', 'name', 'unit', 'active')
                    ->get();

                $data['thirds'] = $this->third
                    ->select('id', 'nit', 'name', 'customer', 'associated', 'contractor', 'active')
                    ->get();

                $data['tickets'] = $this->ticket
                    ->select(
                        'id',
                        'type',
                        'user',
                        'origin_yard as originYard',
                        'destiny_yard as destinyYard',
                        'supplier',
                        'customer',
                        'material',
                        'ash_percentage as ashPercentage',
                        'receipt_number as receiptNumber',
                        'referral_number as referralNumber',
                        'date',
                        DB::raw('TIME_FORMAT(time, "%H:%i") as time'),
                        'license_plate as licensePlate',
                        'trailer_number as trailerNumber',
                        'driver_document as driverDocument',
                        'driver_name as driverName',
                        'gross_weight as grossWeight',
                        'tare_weight as tareWeight',
                        'net_weight as netWeight',
                        'conveyor_company as conveyorCompany',
                        'observation',
                        'seals',
                        'round_trip as roundTrip',
                        'local_created_at as localCreatedAt',
                        'consecutive'
                    )
                    ->where('user', Auth::id())
                    ->where('date', '>=', DB::Raw("DATE_ADD(CURRENT_DATE(), INTERVAL -2 day)"))
                    ->whereNull('freight_settlement')
                    ->whereNull('material_settlement')
                    // ->where('date', '<=',  DB::Raw("DATE_ADD(CURRENT_DATE(), INTERVAL 5 day)"))
                    ->get();
                return response()->json([
                    'data' => $data,
                    'message' => [
                        [
                            'text' => 'sincronización finalizada con '.(count($arrayWarnings['tus']) === 0 ?  'éxito' : 'advertencias'),
                            'detail' => null
                        ]
                    ],                  
                    'warnings' => $arrayWarnings
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                dd($e->getMessage().' '.$e->getLine());
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al sincronizar',
                            'detail' => 'Intente recargando la página'
                        ]
                    ],
                    'warnings' => $arrayWarnings
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function download(){
            try {
                $data = [];
                
                $data['yards'] = $this->yard
                    ->select('id', 'code', 'name', 'active')
                    ->get();
                
                $data['materials'] = $this->material
                    ->select('id', 'code', 'name', 'unit', 'active')
                    ->get();

                $data['thirds'] = $this->third
                    ->select('id', 'nit', 'name', 'customer', 'associated', 'contractor', 'active')
                    ->get();

                $data['tickets'] = $this->ticket
                    ->select(
                        'id',
                        'type',
                        'user',
                        'origin_yard as originYard',
                        'destiny_yard as destinyYard',
                        'supplier',
                        'customer',
                        'material',
                        'ash_percentage as ashPercentage',
                        'receipt_number as receiptNumber',
                        'referral_number as referralNumber',
                        'date',
                        'time',
                        'license_plate as licensePlate',
                        'trailer_number as trailerNumber',
                        'driver_document as driverDocument',
                        'driver_name as driverName',
                        'gross_weight as grossWeight',
                        'tare_weight as tareWeight',
                        'net_weight as netWeight',
                        'conveyor_company as conveyorCompany',
                        'observation',
                        'seals',
                        'round_trip as roundTrip',
                        'local_created_at as localCreatedAt',
                        'consecutive'
                    )
                    ->where('user', Auth::id())
                    ->where('date', '>=', DB::Raw("DATE_ADD(CURRENT_DATE(), INTERVAL -2 day)"))
                    ->get();
                
                return response()->json([
                    'data' => $data
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al descargar datos del servidor',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>