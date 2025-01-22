<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class NovelValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());
        }

        private function rules(){
            return[
                'documentNumber' => 'unique:news,document_number,'.$this->data['id'],
                'name' => 'required|min:5|max:50',
                'phone' => 'required|min:5|max:15|unique:news,phone,'.$this->data['id'],
            ];
        }

        private function messages(){
            return [
                'documentNumber.unique' => 'El número de documento "'.$this->data['documentNumber'].'" ya se encuentra registrado',
                'documentNumber.min' => 'El número de documento debe tener un mínimo de 5 caracteres',
                'documentNumber.max' => 'El número de documento debe tener un máximo de 15 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.min' => 'El nombre debe tener un mínimo de 5 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 50 caracteres',
                'phone.required' => 'El número de teléfono es requerido',
                'phone.min' => 'El número de teléfono debe tener un mínimo de 5 caracteres',
                'phone.max' => 'El número de teléfono debe tener un máximo de 15 caracteres',
                'phone.unique' => 'El número de telefono "'.$this->data['phone'].'" ya se encuentra registrado',
            ];
        }
    }
?>
