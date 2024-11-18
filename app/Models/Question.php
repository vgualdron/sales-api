<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public $table = "questions";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'model_id',
        'model_name',
        'type',
        'status',
        'observation',
        'value',
        'area_id',
        'registered_by',
        'answered_date',
        'created_at',
        'updated_at',
    ];
}
