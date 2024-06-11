<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class UserValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[                             
                'documentNumber' => 'required|min:5|max:15|unique:users,document_number,'.$this->data['id'],
                'name' => 'required|min:5|max:50',
                'phone' => 'required|min:5|max:15',
                'yard' => 'nullable|exists:yards,id',
                'roles.*' => 'nullable|exists:roles,id',
                'active' => 'required|boolean',
                'changeYard' => 'required|boolean',
                'password' => 'nullable|min:5|max:20|required_with:confirmPassword',
                'confirmPassword' => 'nullable|same:password|required_with:password'
            ];
        }

        private function messages(){
            return [
                'documentNumber.required' => 'El número de documento es requerido',
                'documentNumber.unique' => 'El número de documento "'.$this->data['documentNumber'].'" ya se encuentra registrado',
                'documentNumber.min' => 'El número de documento debe tener un mínimo de 5 caracteres',
                'documentNumber.max' => 'El número de documento debe tener un máximo de 15 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.min' => 'El nombre debe tener un mínimo de 5 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 50 caracteres',
                'phone.required' => 'El número de teléfono es requerido',
                'phone.min' => 'El número de teléfono debe tener un mínimo de 5 caracteres',
                'phone.max' => 'El número de teléfono debe tener un máximo de 15 caracteres',
                'yard.exists' => 'El patio seleccionado no existe',
                'active.required' => 'Debe indicar si el usuario se encuentra activo o inactivo',
                'active.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el usuario se encuentra activo o no (true, false, 0, 1, "0" o "1")',
                'changeYard.required' => 'Debe indicar si el usuario puede cambiar patios en tiquetes o no',
                'changeYard.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el usuario puede cambiar patios en tiquetes o no (true, false, 0, 1, "0" o "1")',
                'password.min' => 'La contraseña debe tener un mínimo de 5 caracteres',
                'password.max' => 'La contraseña debe tener un máximo de 20 caracteres',
                'password.required_with' => 'Debe ingresar la contraseña',
                'confirmPassword.same' => 'La contraseña y su confirmación no coinciden',
                'confirmPassword.required_with' => 'Debe ingresar la confirmación de contraseña',
                'roles.*.exists' => 'Uno o mas de los roles ingresados, no existe',
            ];
        }
    }
?>