<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public $table = "files";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'url',
        'model_id',
        'model_name',
        'status',
        'type',
        'extension',
        'observation',
        'registered_by',
        'registered_date',
        'reviewed_by',
        'reviewed_date',
    ];
}
