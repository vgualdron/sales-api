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
                        'p.nombre as period_name',
                    )
                    ->join('asociados as a', 'a.id', 'r.asociado_id')
                    ->join('periodos as p', 'p.id', 'r.periodo_id')
                    ->orderBy('r.fecha_recaudo', 'DESC')
                    ->where('a.cedula', $document)
                    ->get();

                if (count($sql) > 0) {
                    $indexedResults = $sql->map(function ($item, $index) {
                        $item->index = $index + 1;
                        return $item;
                    });
                    return response()->json([
                        'data' => $indexedResults
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
