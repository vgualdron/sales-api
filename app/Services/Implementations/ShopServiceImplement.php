<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ShopServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Shop;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class ShopServiceImplement implements ShopServiceInterface {

        use Commons;

        private $shop;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->shop = new Shop;
            $this->profileValidator = $profileValidator;
        }

        function list() {
            try {
                $sql = $this->shop->from('shops as s')
                    ->select(
                        's.*',
                        'c.name as category_name',
                        'fa.url as url_logo',
                    )
                    ->join('categories as c', 'c.id', 's.category_id')
                    ->leftJoin('files as fa', function($join) {
                        $join->where('fa.model_name', '=', 'shops')
                             ->on('fa.model_id', '=', 's.id')
                             ->where('fa.name', '=', 'LOGO_SHOP');
                    })
                    ->orderBy('s.order', 'ASC')
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
