<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CreditLineServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\CreditLine;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class CreditLineServiceImplement implements CreditLineServiceInterface {

        use Commons;

        private $creditline;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->creditline = new CreditLine;
            $this->profileValidator = $profileValidator;
        }

        function list() {
            try {
                $sql = $this->creditline->from('lineacreditos as lc')
                    ->select(
                        'lc.id',
                        'lc.nombre as name',
                        'lc.plazo as term',
                        'lc.valor as value',
                        'lc.interes_anual as annual_interest',
                        'lc.interes as interest',
                        'lc.seguro_deudor as debtor_insurance',
                        'lc.seguro_credito as credit_insurance',
                        'lc.estado as status',
                    )
                    ->orderBy('lc.id', 'ASC')
                    ->get();

                if (count($sql) > 0) {
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
