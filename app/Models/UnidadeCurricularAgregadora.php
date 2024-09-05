<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeCurricularAgregadora extends Model
{
    use HasFactory;

    protected $table = 'unidades_curriculares_agregadoras';

    protected $fillable = [
        'nome',
        'shortname',
        'moodle_id'
    ];




}
