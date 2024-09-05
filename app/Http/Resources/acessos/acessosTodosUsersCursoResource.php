<?php

namespace App\Http\Resources\acessos;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class acessosTodosUsersCursoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $mimService = app(MimService::class);

        return [
            'id_utilizador' => $this['id'],
            'nome_completo' => $this['fullname'],
            'UCs' => array_map(function ($uc) use ($mimService) {
                $info_dias_calculados = $mimService->calcular_dias_ultimo_acesso($uc['lastcourseaccess']);
                $info_dias_calculados_v2 = $mimService->calcular_dias_ultimo_acesso($uc['lastaccess']);

                return [
                    'nome_uc' => $uc['uc_name'],
                    'ultimo_acesso_uc' => $info_dias_calculados['dataFormatada'],
                    'dias_desde_ultimo_acesso' => $info_dias_calculados['diasDesdeUltimoAcesso'],
                    'ultimo_acesso_uc_plataforma' => $info_dias_calculados_v2['dataFormatada'],
                    'dias_desde_ultimo_acesso_plataforma' => $info_dias_calculados_v2['diasDesdeUltimoAcesso'],
                ];
            }, $this['ucs']),
        ];


    }
}
