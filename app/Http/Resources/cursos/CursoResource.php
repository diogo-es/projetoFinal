<?php

namespace App\Http\Resources\cursos;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mimService = app(MimService::class);
        $info_cursos_extraida= $mimService->extract_designacao_curso_codigo($this->name);

        return [
            'curso_id' => $this->id,
            'codigo' =>  $info_cursos_extraida['codigoCurso'],
            'designacao_curso' =>  $info_cursos_extraida['designacaoCurso'],
            'descricao' => $this->description,
            'grau_de_ensino' => $this->parent,
            'quantidade_de_ucs' => $this->coursecount,
            'escola' => $this->escola,
            'profundidade' => $this->depth,
            'caminho' => $this->path,

        ];
    }
}
