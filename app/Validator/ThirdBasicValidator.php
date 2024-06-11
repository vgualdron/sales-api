<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class ThirdBasicValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                "nit" => "required|min:2|max:50",
                "name" => "required|min:4|max:200",
                "customer" => "required",
                "associated" => "required",
                "contractor" => "required"
            ];
        }

        private function messages(){
            return [
                "nit.required" => "El NIT es requerido",             
                "nit.min" => "El NIT debe tener un mínimo de 2 caracteres",
                "nit.max" => "El NIT debe tener un máximo de 50 caracteres",
                "name.required" => "El nombre es requerido",
                "name.min" => "El nombre debe tener un mínimo de 4 caracteres",
                "name.max" => "El nombre debe tener un máximo de 200 caracteres",
                "customer.required" => "Debe indicar si el tercero es de tipo cliente o no",
                "associated.required" => "Debe indicar si el tercero es de tipo asociado o no",
                "contractor.required" => "Debe indicar si el tercero es de tipo contratista o no"
            ];
        }
    }
?>