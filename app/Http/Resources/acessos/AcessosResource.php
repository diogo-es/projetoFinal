<?php

namespace App\Http\Resources\acessos;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcessosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $mimService = app(MimService::class);
        $info_dias_calculados = $mimService->calcular_dias_ultimo_acesso($this->lastcourseaccess);

        return [
            'id_utilizador' => $this->id,
            'nome_uc' => $this->uc_name,
            'nome_completo' => $this->user_fullname,
            'username' => $this->user_username,
            'email' => $this->user_email,
            'ultimo_acesso_uc' => $info_dias_calculados['dataFormatada'],
            'dias_desde_ultimo_acesso' => $info_dias_calculados['diasDesdeUltimoAcesso'],
            'ultimo_acesso_plataforma' => $this->ultimo_acesso_site,
            'dias_desde_ultimo_acesso_plataforma' => $this->dias_desde_ultimo_acesso_site,
        ];

    }
}
