<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    protected $connection = 'mysql';
    public $table = "news";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'status',
        'document_number',
        'first_lastname',
        'second_lastname',
        'name',
        'type_document',
        'date_issue',
        'city_issue',
        'gender',
        'marital_status',
        'birthdate',
        'country',
        'city_id',
        'type_house',
        'person_charge_adults',
        'person_charge_minors',
        'head_of_family',
        'stratum',
        'department_house',
        'city_house',
        'district_house',
        'address_house',
        'phone_house',
        'phone',
        'email',
        'academic_level',
        'contribution',
        'observation',
        'facebook',
        'instagram',
        'tiktok',
        'approved_by',
        'approved_date',
        'updated_at',
        'created_at',
    ];
}
