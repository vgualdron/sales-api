<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class YardValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'code' => 'required|min:2|max:10|unique:yards,code,'.$this->data['id'],
                'name' => 'required|min:4|max:30|unique:yards,name,'.$this->data['id'],
                'zone' => 'required|exists:zones,id',
                'latitude' => ['nullable', 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,6}))?)|(90(\.0+)?))$/'],
                'longitude' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,6}))?)|180(\.0+)?)$/'],
                'active' => 'required|boolean',
            ];
        }

        private function messages(){
            return [
                'code.required' => 'El código es requerido',
                'code.unique' => 'El código "'.$this->data['code'].'", ya existe',               
                'code.min' => 'El código debe tener un mínimo de 2 caracteres',
                'code.max' => 'El código debe tener un máximo de 10 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.unique' => 'El nombre "'.$this->data['name'].'", ya existe',               
                'name.min' => 'El nombre debe tener un mínimo de 4 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 30 caracteres',
                'zone.required' => 'La zona es requerida',
                'zone.exists' => 'La zona seleccionada no existe',
                'latitude.regex' => 'El formato de la latitud ingresada no es válido',
                'longitude.regex' => 'El formato de la longitud ingresada no es válido',
                'active.required' => 'Debe indicar si el patio se encuentra activo o inactivo',
                'active.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el patio se encuentra activo o no (true, false, 0, 1, "0" o "1")',
            ];
        }
    }
?>