<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class SettlementAditionalInformationValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'invoice' => 'nullable|max:50|required_with:invoiceDate',
                'invoiceDate' => 'nullable|required_with:invoice|date',
                'internalDocument' => 'nullable|max:50',
            ];
        }

        private function messages(){
            return [
                'invoice.max' => 'La factura debe tener un máximo de 50 caracteres',
                'invoice.required_with' => 'Si ingresa una fecha de factura, debe agregar la factura',
                'invoiceDate.date' => 'La fecha de factura ingresada no es una fecha válida',
                'invoiceDate.required_with' => 'Si ingresa una factura, debe ingresar la fecha de la factura',
                'internalDocument.max' => 'El documento interno debe tener un máximo de 50 caracteres'
            ];
        }
    }
?>