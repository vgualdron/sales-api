<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CategoryServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Category;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class CategoryServiceImplement implements CategoryServiceInterface {

        use Commons;

        private $category;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->category = new Category;
            $this->profileValidator = $profileValidator;
        }

        function list() {
            try {
                $sql = $this->category->from('categories as c')
                    ->select(
                        'c.*',
                    )
                    ->orderBy('c.order', 'ASC')
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
