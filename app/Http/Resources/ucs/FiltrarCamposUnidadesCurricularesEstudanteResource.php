<?php

namespace App\Http\Resources\ucs;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiltrarCamposUnidadesCurricularesEstudanteResource extends JsonResource
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

        $info_dias_calculados_data_inicio = $mimService->calcular_dias_ultimo_acesso($this->startdate);

        $info_dias_calculados_data_fim = $mimService->calcular_dias_ultimo_acesso($this->enddate);

        return [
            'id' => $this->id,
            'nome_completo' => $this->fullname,
            'nome_exibicao' => $this->displayname,
            'numero_inscritos_uc' => $this->enrolledusercount,
            'numero_id' => $this->idnumber,
            'resumo' => $this->summary,
            'id_categoria' => $this->category,
            'data_inicio' => $info_dias_calculados_data_inicio['dataFormatada'],//informação transformada
            'dias_desde_inicio' => $info_dias_calculados_data_inicio['diasDesdeUltimoAcesso'],//informação adicionada
            'data_fim' => $info_dias_calculados_data_fim['dataFormatada'],//informação transformada
            'dias_desde_fim' => $info_dias_calculados_data_fim['diasDesdeUltimoAcesso'],//informação adicionada
            'ultimo_acesso_uc' => $info_dias_calculados_ultimo_acesso['dataFormatada'],//informação transformada
            'dias_desde_ultimo_acesso' => $info_dias_calculados_ultimo_acesso['diasDesdeUltimoAcesso'],//informação adicionada
            'oculto' => $this->hidden,
        ];


    }
}
