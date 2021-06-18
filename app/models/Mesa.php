<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'mesas';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'estado_actual', 'codigo'
    ];

    public static function CambiarEstado($id, $nuevo_estado)
    {
        $mesa = new Mesa();
        $mesa_encontrada = $mesa->find($id);
        
        if($mesa_encontrada == null){
            throw new Exception("No existe una mesa con el ID: ". $id, 1);
        }
        $mesa_encontrada->update(['estado_actual' => $nuevo_estado]);
    }
}
