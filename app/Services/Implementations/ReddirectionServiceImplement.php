<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ReddirectionServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Reddirection;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class ReddirectionServiceImplement implements ReddirectionServiceInterface {

        use Commons;

        private $reddirection;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->reddirection = new Reddirection;
            $this->profileValidator = $profileValidator;
        }    

        function create(array $reddirection){
            try {
                DB::transaction(function () use ($reddirection) {
                    $sql = $this->reddirection::create([
                        'collector_id' => $reddirection['collector_id'],
                        'registered_by' => $reddirection['idUserSesion'],
                        'registered_date' => date('Y-m-d H:i:s'),
                        'lending_id' => $reddirection['lending_id'],
                        'address' => $reddirection['address'],
                        'district_id' => $reddirection['district_id'],
                        'type_ref' => $reddirection['type_ref'],
                        'description_ref' => $reddirection['description_ref'],
                        'value' => $reddirection['value'],
                        'status' => $reddirection['status'],
                    ]);
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>