<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    public $table = "news";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'document_number',
        'name',
        'phone',
        'address',
        'address_house',
        'address_work',
        'site_visit',
        'sector',
        'district',
        'occupation',
        'observation',
        'status',
        'user_send',
        'client_id',
        'attempts',
        'family_reference_document_number',
        'family_reference_name',
        'family_reference_address',
        'family_reference_phone',
        'family_reference_relationship',
        'family2_reference_document_number',
        'family2_reference_name',
        'family2_reference_address',
        'family2_reference_phone',
        'family2_reference_relationship',
        'guarantor_document_number',
        'guarantor_name',
        'guarantor_address',
        'guarantor_phone',
        'guarantor_relationship',
        'facebook',
        'quantity',
        'type_house',
        'type_work',
        'period',
        'lent_by',
        'approved_by',
        'approved_date',
        'made_by',
        'visit_start_date',
    ];
}
