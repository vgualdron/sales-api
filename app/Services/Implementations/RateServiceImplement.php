<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\RateServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Rate;
    use App\Validator\RateValidator;
    use App\Traits\Commons;
    use App\Traits\Generals;
    use Illuminate\Support\Facades\DB;
    
    class RateServiceImplement implements RateServiceInterface {

        use Commons, Generals;

        private $rate;
        private $validator;

        function __construct(RateValidator $validator){
            $this->rate = new Rate;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->rate
                ->from('rates as r')
                ->select(
                    'r.id',
                    DB::Raw(
                        '(CASE r.movement
                            WHEN "T" THEN "TRASLADO"
                            WHEN "C" THEN "COMPRA"
                            WHEN "V" THEN "VENTA"
                        END) as movement'
                    ),
                    DB::Raw('DATE_FORMAT(r.start_date, "%d/%m/%Y") as startDate'),
                    DB::Raw('DATE_FORMAT(r.final_date, "%d/%m/%Y") as finalDate'),
                    DB::Raw(
                        '(CASE r.movement
                            WHEN "C" THEN ts.name
                            ELSE oy.name 
                        END) as originYard'
                    ),
                    DB::Raw(
                        '(CASE r.movement
                            WHEN "V" THEN tc.name
                            ELSE dy.name
                        END) as destinyYard'
                    ),
                    DB::Raw("DATE_FORMAT(r.created_at, '%d/%m/%Y') as creationDate")
                )
                ->leftJoin('yards as oy', 'r.origin_yard', '=', 'oy.id')
                ->leftJoin('yards as dy', 'r.destiny_yard', '=', 'dy.id')
                ->leftJoin('thirds as ts', 'r.supplier', '=', 'ts.id')
                ->leftJoin('thirds as tc', 'r.customer', '=', 'tc.id')
                ->leftJoin('thirds as tcc', 'r.conveyor_company', '=', 'tcc.id')
                ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay tarifas para mostrar',
                                'detail' => 'Aun no ha registrado ninguna tarifa'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar las tarifas',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $rate){
            try {
                $filled = $this->camelArrayFromModel($this->rate);
                $validation = $this->validate($this->validator, $rate, null, 'registrar', 'tarifa', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $rate = array_merge($filled, $rate);
                $exists = $this->rate->where('movement', '=', $rate['movement'])
                                ->where('material', '=', $rate['material'])
                                ->where('origin_yard', '=', $rate['originYard'])
                                ->where('destiny_yard', '=', $rate['destinyYard'])
                                ->where('supplier', '=', $rate['supplier'])
                                ->where('customer', '=', $rate['customer'])
                                ->where('conveyor_company', '=', $rate['conveyorCompany'])
                                ->where('round_trip', '=', (empty($rate['roundTrip']) ? 0 : 1))
                                ->where(function ($query) use ($rate){
                                        $query->whereRaw('? BETWEEN start_date AND final_date', $rate['startDate'])
                                            ->orWhereRaw('? BETWEEN start_date AND final_date', $rate['finalDate'])
                                            ->orWhereRaw('start_date >= ? AND final_date <= ?', [$rate['startDate'], $rate['finalDate']]);
                                })
                                ->get();
                if(count($exists) === 0) {
                    $this->rate::create([
                        'movement' => $rate['movement'],
                        'origin_yard' => $rate['originYard'],
                        'destiny_yard' => $rate['destinyYard'],
                        'supplier' => $rate['supplier'],
                        'customer' => $rate['customer'],
                        'start_date' => $rate['startDate'],
                        'final_date' => $rate['finalDate'],
                        'material' => $rate['material'],
                        'conveyor_company' => $rate['conveyorCompany'],
                        'material_price' => $rate['materialPrice'],
                        'freight_price' => $rate['freightPrice'],
                        'total_price' => $rate['totalPrice'],
                        'observation' => $rate['observation'],
                        'round_trip' => !empty($rate['roundTrip']) ? $rate['roundTrip'] : 0
                    ]);
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Tarifa registrada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al registrar la tarifa',
                                'detail' => 'Ya existe una tarifa con estas caracteristicas, que se cruzan con el rango de fechas ingresado'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar la tarifa',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $rate, int $id){
            try {
                $filled = $this->camelArrayFromModel($this->rate);
                $validation = $this->validate($this->validator, $rate, $id, 'actualizar', 'tarifa', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->rate::find($id);
                if(!empty($sql)) {
                    $rate = array_merge($filled, $rate);
                    $exists = $this->rate->where('movement', '=', $rate['movement'])
                        ->where('material', '=', $rate['material'])
                        ->where('origin_yard', '=', $rate['originYard'])
                        ->where('destiny_yard', '=', $rate['destinyYard'])
                        ->where('supplier', '=', $rate['supplier'])
                        ->where('customer', '=', $rate['customer'])
                        ->where('conveyor_company', '=', $rate['conveyorCompany'])
                        ->where('round_trip', '=', (empty($rate['roundTrip']) ? 0 : 1))
                        ->where(function ($query) use ($rate){
                                $query->whereRaw('? BETWEEN start_date AND final_date', $rate['startDate'])
                                    ->orWhereRaw('? BETWEEN start_date AND final_date', $rate['finalDate'])
                                    ->orWhereRaw('start_date >= ? AND final_date <= ?', [$rate['startDate'], $rate['finalDate']]);
                        })
                        ->where('id', '<>', $id)
                        ->get();
                    if(count($exists) === 0) {
                        $sql->movement = $rate['movement'];
                        $sql->origin_yard = $rate['originYard'];
                        $sql->destiny_yard = $rate['destinyYard'];
                        $sql->supplier = $rate['supplier'];
                        $sql->customer = $rate['customer'];
                        $sql->start_date = $rate['startDate'];
                        $sql->final_date = $rate['finalDate'];
                        $sql->material = $rate['material'];
                        $sql->conveyor_company = $rate['conveyorCompany'];
                        $sql->material_price = $rate['materialPrice'];
                        $sql->freight_price = $rate['freightPrice'];
                        $sql->total_price = $rate['totalPrice'];
                        $sql->observation = $rate['observation'];
                        $sql->round_trip = !empty($rate['roundTrip']) ? $rate['roundTrip'] : 0;
                        $sql->save();
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Tarifa actualizada con éxito',
                                    'detail' => null
                                ]
                            ]
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Advertencia al registrar la tarifa',
                                    'detail' => 'Ya existe una tarifa con estas caracteristicas, que se cruzan con el rango de fechas ingresado'
                                ]
                            ]
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar la tarifa',
                                'detail' => 'La tarifa no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar la tarifa',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->rate::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Tarifa eliminada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la tarifa',
                                'detail' => 'La tarifa no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la tarifa',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'La tarifa se encuentra asociada a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->rate::select(
                    'id',
                    'movement',
                    'origin_yard as originYard',
                    'destiny_yard as destinyYard',
                    'supplier',
                    'customer',
                    DB::Raw("DATE_FORMAT(start_date, '%d/%m/%Y') as startDate"),
                    DB::Raw("DATE_FORMAT(final_date, '%d/%m/%Y') as finalDate"),
                    'material',
                    'conveyor_company as conveyorCompany',
                    DB::Raw('FORMAT(material_price, 2) as materialPrice'),
                    DB::Raw('FORMAT(freight_price,2) as freightPrice'),
                    DB::Raw('FORMAT(total_price, 2) as totalPrice'),
                    'observation',
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
                                'text' => 'La tarifa no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                dd($e->getMessage());
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar la tarifa',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>