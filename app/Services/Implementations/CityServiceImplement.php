<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CityServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\City;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class CityServiceImplement implements CityServiceInterface {

        use Commons;

        private $city;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->city = new City;
            $this->profileValidator = $profileValidator;
        }

        function list(int $department) {
            try {
                $sql = $this->city->from('municipios as m')
                    ->select(
                        'm.id',
                        'm.nombre as name',
                        'm.estado as status',
                        'm.departamento_id as department_id',
                    )
                    ->where('m.departamento_id', $department)
                    ->orderBy('m.nombre', 'ASC')
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => []
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
