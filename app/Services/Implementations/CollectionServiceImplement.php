<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CollectionServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Collection;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class CollectionServiceImplement implements CollectionServiceInterface {

        use Commons;

        private $collection;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->collection = new Collection;
            $this->profileValidator = $profileValidator;
        }

        function list(String $document) {
            try {
                $sql = $this->collection->from('recaudos as r')
                    ->select(
                        'r.id',
                        'r.fecha_recaudo as date',
                        'r.valor_recaudo as amount',
                    )
                    ->leftJoin('asociados')
                    ->orderBy('r.fecha_recaudo', 'DESC')
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
