<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\DepartmentServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Department;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class DepartmentServiceImplement implements DepartmentServiceInterface {

        use Commons;

        private $department;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->department = new Department;
            $this->profileValidator = $profileValidator;
        }

        function list() {
            try {
                $sql = $this->department->from('departamentos as d')
                    ->select(
                        'd.*',
                    )
                    ->orderBy('d.nombre', 'ASC')
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
