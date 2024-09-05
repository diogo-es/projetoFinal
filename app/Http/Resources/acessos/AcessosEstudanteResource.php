<?php

namespace App\Http\Resources\acessos;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcessosEstudanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        $mimService = app(MimService::class);

        $info_dias_calculados_ultimo_acesso = $mimService->calcular_dias_ultimo_acesso($this->lastaccess);

        return [
            'id' => $this->id,
            'nome_completo' => $this->fullname,
            'nome_exibicao' => $this->displayname,
            'ultimo_acesso_uc' => $info_dias_calculados_ultimo_acesso['dataFormatada'],
            'dias_desde_ultimo_acesso' => $info_dias_calculados_ultimo_acesso['diasDesdeUltimoAcesso'],
        ];
    }
}

