<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\OvenServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Batterie;
    use App\Models\Oven;
    use App\Validator\OvenValidator;
    use App\Traits\Commons;
    
    class OvenServiceImplement implements OvenServiceInterface {

        use Commons;

        private $oven;
        private $validator;

        function __construct(OvenValidator $validator){
            $this->oven = new Oven;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->oven->select(
                    'id',
                    'name',
                    'active',
                    'batterie')->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay hornos para mostrar',
                                'detail' => 'Aun no ha registrado ninguna horno'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los hornos',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $oven){
            try {
                $validation = $this->validate($this->validator, $oven, null, 'registrar', 'horno', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->oven::create([
                    'name' => $oven['name'],
                    'active' => $oven['active'],
                    'batterie' => $oven['batterie'],
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Horno registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el horno',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $oven, int $id){
            try {
                $validation = $this->validate($this->validator, $oven, $id, 'actualizar', 'horno', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->oven::find($id);
                if(!empty($sql)) {
                    $sql->name = $oven['name'];
                    $sql->active = $oven['active'];
                    $sql->batterie = $oven['batterie'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Horno actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el horno',
                                'detail' => 'El horno no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el horno',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->oven::find($id);
                if(!empty($sql)) {
                    $deleted = $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Horno eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el horno',
                                'detail' => 'El horno no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el horno',
                                'detail' => $e->getMessage()
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'El horno se encuentra asociada a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->batterie::select(
                    'id',
                    'description',
                    'batterie',
                    'active')->where('id', $id)
                            ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El horno no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el horno',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>