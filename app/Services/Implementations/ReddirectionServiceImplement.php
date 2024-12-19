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

        
        function getCurrentByUser(int $user){
            try {
                $item = $this->reddirection->from('reddirections as rd')
                                        ->select(
                                            'rd.*',
                                            'l.firstDate as lending_first_date',
                                            'l.endDate as lending_end_date',
                                            'li.name as listing_name',
                                            'd.name as district_name',
                                            'd.order as district_order',
                                            'n.observation as new_observation',
                                            'n.name as new_name',
                                            'y.name as sector_name',
                                        )
                                        ->leftJoin('lendings as l', 'l.id', 'rd.lending_id')
                                        ->leftJoin('news as n', 'n.id', 'l.new_id')
                                        ->leftJoin('listings as li', 'li.id', 'l.listing_id')
                                        ->leftJoin('districts as d', 'd.id', 'rd.district_id')
                                        ->leftJoin('yards as y', 'y.id', 'd.sector')
                                        ->where('rd.collector_id', $user)
                                        ->where('rd.status', 'activo')
                                        ->first();
                return response()->json([
                    'data' => $item
                ], Response::HTTP_OK);
                
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>