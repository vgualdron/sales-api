<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\BatterieServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Batterie;
    use App\Models\Oven;
    use App\Validator\BatterieValidator;
    use App\Traits\Commons;
    
    class BatterieServiceImplement implements BatterieServiceInterface {

        use Commons;

        private $batterie;
        private $validator;

        function __construct(BatterieValidator $validator){
            $this->batterie = new Batterie;
            $this->validator = $validator;
        }    

        function list() {
            try {
                $sql = $this->batterie->select(
                    'id',
                    'name',
                    'description',
                    'active',
                    'yard')
                    ->get();


                $batteries = [];
                foreach ($sql as $batterie) {
                    $bat = null;
                    $ovens = Oven::from('ovens as o')
                    ->select(
                        'o.id',
                        'o.name',
                        'o.active',
                        'o.batterie'
                    )
                    ->join('batteries as b', 'b.id', 'o.batterie')
                    ->where('b.id', $batterie->id)
                    ->orderBy('o.name', 'ASC')
                    ->get();

                    $bat["id"] = $batterie->id;
                    $bat["name"] = $batterie->name;
                    $bat["description"] = $batterie->description;
                    $bat["active"] = $batterie->active;
                    $bat["yard"] = $batterie->yard;
                    $bat["ovens"] = $ovens;

                    $batteries[] = $bat;
                }

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $batteries
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay baterias para mostrar',
                                'detail' => 'Aun no ha registrado ninguna bateria'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar las baterias',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $batterie){
            try {
                $validation = $this->validate($this->validator, $batterie, null, 'registrar', 'bateria', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->batterie::create([
                    'name' => $batterie['name'],
                    'description' => $batterie['description'],
                    'active' => $batterie['active'],
                    'yard' => $batterie['yard'],
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Bateria registrada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar la Bateria',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $batterie, int $id){
            try {
                $validation = $this->validate($this->validator, $batterie, $id, 'actualizar', 'bateria', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->batterie::find($id);
                if(!empty($sql)) {
                    $sql->name = $batterie['name'];
                    $sql->description = $batterie['description'];
                    $sql->active = $batterie['active'];
                    $sql->yard = $batterie['yard'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Bateria actualizada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar la bateria',
                                'detail' => 'La bateria no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar la bateria',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->batterie::find($id);
                if(!empty($sql)) {
                    $deleted = $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Bateria eliminada con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la bateria',
                                'detail' => 'La bateria no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar la bateria',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'La bateria se encuentra asociada a otro registro'
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
                    'name',
                    'description',
                    'yard',
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
                                'text' => 'La bateria no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar la bateria',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>