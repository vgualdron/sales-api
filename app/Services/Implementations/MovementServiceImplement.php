<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\MovementServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\{ Movement, Ticket, MovementTicket };
    use Illuminate\Support\Facades\{DB, Auth};
    //use App\Validator\ZoneValidator;
    use App\Traits\Movements;
    use DateTime;
    
    class MovementServiceImplement implements MovementServiceInterface {

        use Movements;

        private $movement;
        private $movementTicket;
        private $ticket;
        //private $validator;

        function __construct(/*ZoneValidator $validator*/){
            $this->movement = new Movement;
            $this->ticket = new Ticket;
            $this->movementTicket = new MovementTicket;
           // $this->validator = $validator;
        }    

        function list(){
            try {
                $movements = $this->movement::select(
                    'id',
                    'id as consecutive',
                    DB::Raw('DATE_FORMAT(created_at, "%d/%m/%Y") as date'),
                    DB::Raw('DATE_FORMAT(start_date, "%d/%m/%Y") as startDate'),
                    DB::Raw('DATE_FORMAT(final_date, "%d/%m/%Y") as finalDate')
                )
                ->get();
                return response()->json([
                    'data' => $movements
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los movimientos',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(string $startDate, string $finalDate, string $tickets){
            try {
                $tickets = explode(',', $tickets);
                $ticketsToGenerate = $this->ticket::from('tickets as t')
                    ->select(
                        't.id as id'
                    )
                    ->join('movements_tickets as mt', 't.id', 'mt.ticket')
                    ->whereIn('mt.ticket', $tickets)
                    ->get();
                $movementId = null;
                $movementConsecutive = null;
                if (count($ticketsToGenerate) === 0) {
                    DB::transaction(function () use ($tickets, $startDate, $finalDate, &$movementId, &$movementConsecutive) {
                        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
                        $consecutive = md5(Auth::id().'/'.$now->format("m-d-Y H:i:s.u"));
                        $movementConsecutive = $consecutive;
                        $movement = $this->movement::create([
                            'consecutive' => $consecutive,
                            'start_date' => $startDate,
                            'final_date' => $finalDate,
                        ]);
                        $id = $movement->id;
                        $movementId = $id;
                        $movements = [];
                        foreach ($tickets as $ticket) {
                            $movements[] = [
                                'ticket' => $ticket,
                                'movement' => $id,
                            ];
                        }
                        $this->movementTicket::insert($movements);
                    });
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Movimiento generado con éxito',
                                'detail' => null
                            ]
                        ],
                        'movement' => [
                            'id' => $movementId,
                            'consecutive' => $movementId
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al generar movimiento',
                                'detail' => 'Uno o varios tiquetes ya cuentan con un movimiento generado'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al generar el movimiento',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){
            try {
                $movement = $this->movement::find($id);
                if (is_null($movement)) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el movimiento',
                                'detail' => 'El movimiento seleccionado, no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
                DB::transaction(function () use ($id, $movement) {
                    $this->movementTicket::where('movement', $id)->delete();
                    $movement->delete();
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Movimiento eliminado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al eliminar el movimiento',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getTickets(string $startDate, string $finalDate){
            try {
                $data = $this->getTicketsByDate($startDate, $finalDate);
                if(count($data) > 0) {
                    return response()->json([
                        'data' => $data
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al consultar tiquetes',
                                'detail' => 'No hay tiquetes pendientes por generación de movimiento'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al consultar tiquetes',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function print(int $id){
            try {
                $tickets = $this->movementTicket::select('ticket')
                    ->where('movement', $id)
                    ->get();
                $ids = $tickets->pluck('ticket')->toArray();
                $movements = $this->getTicketsById($ids);
                return response()->json([
                    'data' => $movements
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un inconveniente al imprimir tiquete',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>