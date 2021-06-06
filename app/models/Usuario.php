<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'id';
    protected $table = 'usuarios';

    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_baja';

    protected $fillable = [
        'usuario', 'clave', 'tipo', 'rol', 'fecha_baja'
    ];

}