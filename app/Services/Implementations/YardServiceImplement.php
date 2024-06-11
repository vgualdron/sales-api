<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\YardServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Yard;
    use App\Validator\YardValidator;
    use App\Traits\Commons;
    
    class YardServiceImplement implements YardServiceInterface {

        use Commons;

        private $yard;
        private $validator;

        function __construct(YardValidator $validator){
            $this->yard = new Yard;
            $this->validator = $validator;
        }

        function list(string $yard, int $displayAll){
            try {
                $yards = explode(',', $yard);
                $sql = $this->yard->from('yards as y')
                    ->select('y.id', 'y.name', 'y.code', 'y.active', 'z.name as zone', 'y.active as active')
                    ->join('zones as z', 'y.zone', 'z.id')
                    ->when($displayAll === 0, function ($query) use ($yards) {
                        return $query->where(function ($query) use ($yards) {
                            $query->where('y.active', 1)
                                ->orWhereIn('y.id', $yards);
                        });
                    })
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay patios para mostrar',
                                'detail' => 'Aún no se ha registrado ningun patio'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los patios',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $yard){
            try {
                $validation = $this->validate($this->validator, $yard, null, 'registrar', 'patio', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->yard::create([
                    'code' => $yard['code'],
                    'name' => $yard['name'],
                    'zone' => $yard['zone'],
                    'longitude' => $yard['longitude'],
                    'latitude' => $yard['latitude'],
                    'active' => $yard['active']
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Patio registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el patio',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $yard, int $id){
            try {
                $validation = $this->validate($this->validator, $yard, $id, 'actualizar', 'patio', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->yard::find($id);
                if(!empty($sql)) {
                    $sql->code = $yard['code'];
                    $sql->name = $yard['name'];
                    $sql->zone = $yard['zone'];
                    $sql->longitude = $yard['longitude'];
                    $sql->latitude = $yard['latitude'];
                    $sql->active = $yard['active'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Patio actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el patio',
                                'detail' => 'El patio no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el patio',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->yard::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Patio eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el patio',
                                'detail' => 'El patio no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la zona',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'El patio se encuentra asociada a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->yard::select(
                                'id', 'code', 'name',
                                'zone', 'longitude', 'latitude',
                                'active'
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
                                'text' => 'El patio no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el patio',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>