<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CompanyServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Company;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class CompanyServiceImplement implements CompanyServiceInterface {

        use Commons;

        private $company;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->company = new Company;
            $this->profileValidator = $profileValidator;
        }

        function list() {
            try {
                $sql = $this->department->from('empresas as e')
                    ->select(
                        'e.id',
                        'e.nit',
                        'e.nombre as name',
                        'e.direccion as address',
                        'e.telefono as phone',
                        'e.email as email',
                        'e.estado as status',
                    )
                    ->orderBy('e.nit', 'ASC')
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
