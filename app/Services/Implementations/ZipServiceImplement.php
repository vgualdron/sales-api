<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ZipServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Zip;
    use App\Validator\ZipValidator;
    use App\Traits\Commons;
    
    class ZipServiceImplement implements ZipServiceInterface {

        use Commons;

        private $zip;
        private $validator;

        function __construct(ZipValidator $validator){
            $this->zip = new Zip;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->zip->select(
                    'id',
                    'name',
                    'registered_by',
                    'registered_date',
                )->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay zip para mostrar',
                                'detail' => 'Aun no ha registrado ningun zip'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar lo zips',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $zip){
            try {
                $validation = $this->validate($this->validator, $zip, null, 'registrar', 'zip', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->zone::create([
                    'code' => $zip['code'],
                    'name' => $zip['name']
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Zip registrada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el zip',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>