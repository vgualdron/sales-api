<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ZoneServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Zone;
    use App\Validator\ZoneValidator;
    use App\Traits\Commons;
    
    class ZoneServiceImplement implements ZoneServiceInterface {

        use Commons;

        private $zone;
        private $validator;

        function __construct(ZoneValidator $validator){
            $this->zone = new Zone;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->zone->select('id', 'name', 'code')
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay zonas para mostrar',
                                'detail' => 'Aun no ha registrado ninguna zona'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar las zonas',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $zone){
            try {
                $validation = $this->validate($this->validator, $zone, null, 'registrar', 'zona', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->zone::create([
                    'code' => $zone['code'],
                    'name' => $zone['name']
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Zona registrada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar la zona',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $zone, int $id){
            try {
                $validation = $this->validate($this->validator, $zone, $id, 'actualizar', 'zona', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->zone::find($id);
                if(!empty($sql)) {
                    $sql->name = $zone['name'];
                    $sql->code = $zone['code'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Zona actualizada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar la zona',
                                'detail' => 'La zona no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar la zona',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->zone::find($id);
                if(!empty($sql)) {
                    $deleted = $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Zona eliminada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la zona',
                                'detail' => 'La zona no existe'
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
                                'detail' => 'La zona se encuentra asociada a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->zone::select('id', 'code', 'name')
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
                                'text' => 'La zona no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar la zona',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>