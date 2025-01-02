<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\CapitallistingServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Capitallisting;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;

    class CapitallistingServiceImplement implements CapitallistingServiceInterface {

        use Commons;

        private $capitallisting;
        private $validator;

        function __construct(){
            $this->capitallisting = new Capitallisting;
        }

        function create(array $capitallisting){
            try {
                DB::transaction(function () use ($capitallisting) {
                    $status = $this->capitallisting::create([
                        'listing_id' => $capitallisting['listing_id'],
                        'capital' => $capitallisting['capital'],
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
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
