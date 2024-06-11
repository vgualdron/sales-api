<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class ProfileValidator {

        private $data;

        public function validate($data){
            $this->data = $data;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[                             
                "password" => "required|min:5|max:20",
                "confirmPassword" => "required|same:password"
            ];
        }

        private function messages(){
            return [ 
                "password.min" => "La contraseña debe tener un mínimo de 5 caracteres",
                "password.max" => "La contraseña debe tener un máximo de 20 caracteres",
                "password.required" => "La clave es requerida", 
                "confirmPassword.same" => "La contraseña no coincide con la confirmación",
                "confirmPassword.required" => "La confirmación de la clave es requerida"
            ];
        }
    }
?>