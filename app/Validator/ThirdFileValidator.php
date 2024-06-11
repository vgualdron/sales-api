<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class ThirdFileValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                "file" => "required|mimes:csv,txt"
            ];
        }

        private function messages(){
            return [
                "file.required" => "Debe cargar un archivo",
                "file.mimes" => "Solo se admiten archivos de extensión '.csv' y deben estar delimitados con el caracter ';'"
            ];
        }
    }
?>