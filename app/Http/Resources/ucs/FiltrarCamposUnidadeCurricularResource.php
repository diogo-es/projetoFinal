<?php

namespace App\Http\Resources\ucs;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiltrarCamposUnidadeCurricularResource extends JsonResource
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
            'id' => !empty($this->fullname) ? $this->id : $this->moodle_id,
            'nome_completo' => $this->fullname ?? $this->nome ?? '',
            'nome_exibicao' => $this->displayname ?? '',
            'nome_curto' => $this->shortname,
            'id_categoria' => $this->categoryid ?? $this->extra_data ?? '',
            'nome_categoria' => $this->categoryname ?? '',
            'resumo' => $this->summary ?? '',
            'ficheiros_resumo' => $this->summaryfiles ?? '',
            'mostrar_atividades' => $this->showactivitydates ?? '',
            'mostrar_condicoes_conclusao' => $this->showcompletionconditions ?? '',
            'lista_docentes' => array_map(function ($contact) {
                return [
                    'id' => $contact->id ?? '',
                    'nome_completo' => $contact->fullname ?? '',
                ];
            }, $this->contacts ?? []),
            'tipo_inscricoes' => $this->enrollmentmethods ?? '',
            'semestre' => $mimService->get_semestre($this->shortname) ?? '',
        ];
    }

}
