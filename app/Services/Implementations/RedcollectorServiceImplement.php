<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\RedcollectorServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Redcollector;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class RedcollectorServiceImplement implements RedcollectorServiceInterface {

        use Commons;

        private $redcollector;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->redcollector = new Redcollector;
            $this->profileValidator = $profileValidator;
        }    

        function create(array $redcollector){
            try {
                DB::transaction(function () use ($redcollector) {
                    $sql = $this->redcollector::create([
                        'collector_id' => $redcollector['collector_id'],
                        'registered_by' => $redcollector['idUserSesion'],
                        'registered_date' => $redcollector['registered_date'],
                        'sector_id' => $redcollector['sector_id'],
                    ]);
    
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Red Collector registrado con éxito',
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