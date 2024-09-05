<?php

namespace App\Http\Resources\grupos;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GruposEstudanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {

        $mimService = app(MimService::class);

        $nome_uc = $mimService->encontrar_nome_uc_atraves_id($this->courseid);

        return [
            'id_grupo' => $this->id,
            'uc_id' => $this->courseid,
            'nome_uc' => $nome_uc ?? '',
            'nome_grupo' => $this->name,
            'descricao' => $this->description,
        ];
    }
}

