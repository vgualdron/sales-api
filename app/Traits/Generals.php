<?php
    namespace App\Traits;

    trait Generals
    {
        public function camelArrayFromModel($model) {
            $keys = $model->getFillable();
            $camelKeys = array_map(function ($key){
                return lcfirst(implode('', array_map('ucfirst', explode('_', $key))));;
            }, $keys);
            $filled = array_fill_keys($camelKeys, null);
            return $filled;
        }
    }  
?>