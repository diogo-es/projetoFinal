<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgregadoraAgregada extends Model
{
    use HasFactory;

    protected $table = 'agregadora_agregada';
    protected $fillable = ['id_agregadora', 'id_agregada'];


    public function unidadeCurricular()
    {
        return $this->belongsTo(UnidadeCurricular::class, 'id_agregada');
    }


}
