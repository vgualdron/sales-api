<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\TicketServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Ticket;
    use App\Validator\TicketValidator;
    use App\Traits\Commons;
    use App\Traits\Generals;
    use Illuminate\Support\Facades\{DB, Auth};
    use DateTime;

    class TicketServiceImplement implements TicketServiceInterface {

        use Commons, Generals;

        private $ticket;
        private $validator;

        function __construct(TicketValidator $validator){
            $this->ticket = new Ticket;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->ticket->from('tickets as t')
                    ->select(
                        't.id as id',
                        DB::Raw('
                            CASE t.type
                                WHEN "D" THEN "DESPACHO"
                                WHEN "R" THEN "RECEPCIÓN"
                                WHEN "C" THEN "COMPRA"
                                ELSE "VENTA"
                            END as type'
                        ),
                        't.referral_number as referralNumber',
                        't.receipt_number as receiptNumber',
                        DB::Raw('DATE_FORMAT(t.date, "%d/%m/%Y") as date'),
                        DB::Raw(
                            'CASE t.type
                                WHEN "C" THEN ts.name
                                ELSE oy.name 
                            END as originYard'
                        ),
                        DB::Raw(
                            '(CASE t.type
                                WHEN "V" THEN tc.name
                                ELSE dy.name
                            END) as destinyYard'
                        ),
                        'm.name as material'
                    )
                    ->leftJoin('yards as oy', 't.origin_yard', '=', 'oy.id')
                    ->leftJoin('yards as dy', 't.destiny_yard', '=', 'dy.id')
                    ->leftJoin('thirds as ts', 't.supplier', '=', 'ts.id')
                    ->leftJoin('thirds as tc', 't.customer', '=', 'tc.id')
                    ->leftJoin('materials as m', 't.material', '=', 'm.id')
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay tiquetes para mostrar',
                                'detail' => 'Aún no ha registrado ningun tiquete'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los tiquetes',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $ticket){
            try {
                $ticket['user'] = Auth::id();
                $validation = $this->validate($this->validator, $ticket, null, 'registrar', 'tiquete', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $filled = $this->camelArrayFromModel($this->ticket);
                $ticket = array_merge($filled, $ticket);
                $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                $consecutive = md5(Auth::id().'/'.$now->format("m-d-Y H:i:s.u"));
                $this->ticket::create([
                    'type' => $ticket['type'],
                    'user' => $ticket['user'],
                    'origin_yard' => $ticket['originYard'],
                    'destiny_yard' => $ticket['destinyYard'],
                    'supplier' => $ticket['supplier'],
                    'customer' => $ticket['customer'],
                    'material' => $ticket['material'],
                    'ash_percentage' => $ticket['ashPercentage'],
                    'receipt_number' => $ticket['receiptNumber'],
                    'referral_number' => $ticket['referralNumber'],
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
                    'consecutive' => $consecutive
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Tiquete registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el tiquete',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $ticket, int $id){
            try {
                $sql = $this->ticket::find($id);
                if(!empty($sql)) {
                    $ticket['user'] = $sql->user;
                    $validation = $this->validate($this->validator, $ticket, $id, 'actualizar', 'tiquete', null);
                    if ($validation['success'] === false) {
                        return response()->json([
                            'message' => $validation['message']
                        ], Response::HTTP_BAD_REQUEST);
                    }
                    $filled = $this->camelArrayFromModel($this->ticket);
                    $ticket = array_merge($filled, $ticket);
                    $sql->type = $ticket['type'];
                    $sql->origin_yard = $ticket['originYard'];
                    $sql->destiny_yard = $ticket['destinyYard'];
                    $sql->supplier = $ticket['supplier'];
                    $sql->customer = $ticket['customer'];
                    $sql->material = $ticket['material'];
                    $sql->ash_percentage = $ticket['ashPercentage'];
                    $sql->receipt_number = $ticket['receiptNumber'];
                    $sql->referral_number = $ticket['referralNumber'];
                    $sql->date = $ticket['date'];
                    $sql->time = $ticket['time'];
                    $sql->license_plate = $ticket['licensePlate'];
                    $sql->trailer_number = $ticket['trailerNumber'];
                    $sql->driver_document = $ticket['driverDocument'];
                    $sql->driver_name = $ticket['driverName'];
                    $sql->gross_weight = $ticket['grossWeight'];
                    $sql->tare_weight = $ticket['tareWeight'];
                    $sql->net_weight = $ticket['netWeight'];
                    $sql->conveyor_company = $ticket['conveyorCompany'];
                    $sql->observation = $ticket['observation'];
                    $sql->seals = $ticket['seals'];
                    $sql->round_trip = $ticket['roundTrip'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Tiquete actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el tiquete',
                                'detail' => 'El tiquete no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el tiquete',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->ticket::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Tiquete eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el tiquete',
                                'detail' => 'El tiquete no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el tiquete',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'El tiquete se encuentra asociado a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->ticket::select(
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
                    DB::Raw('CONCAT(DATE_FORMAT(date, "%d/%m/%Y"), " ", TIME_FORMAT(time, "%H:%i")) as dateTime'),
                    'license_plate as licensePlate',
                    'trailer_number as trailerNumber',
                    'driver_document as driverDocument',
                    'driver_name as driverName',
                    DB::Raw('FORMAT(gross_weight, 2) as grossWeight'),
                    DB::Raw('FORMAT(tare_weight, 2) as tareWeight'),
                    DB::Raw('FORMAT(net_weight, 2) as netWeight'),
                    'conveyor_company as conveyorCompany',
                    'observation',
                    'seals',
                    'round_trip as roundTrip'
                )
                    ->where('id', $id)   
                    ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El tiquete no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el tiquete',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>