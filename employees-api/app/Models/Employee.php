<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public $timestamps = true;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    protected $table = 'employees';

    protected $fillable = [
        'nomor',
        'nama',
        'jabatan',
        'talahir',
        'photo_upload_path',
        'created_by',
        'updated_by',
        'deleted_on',
    ];

    protected $casts = [
        'talahir' => 'date',
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
    ];
}


