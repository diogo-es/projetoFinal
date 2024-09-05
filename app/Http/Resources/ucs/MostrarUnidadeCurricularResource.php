<?php

namespace App\Http\Resources\ucs;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MostrarUnidadeCurricularResource extends JsonResource
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
            'ordem_organizacao' => $this->sortorder ?? '',
            'resumo' => $this->summary ?? '',
            'formato_resumo' => $this->summaryformat ?? '',
            'ficheiros_resumo' => $this->summaryfiles ?? '',
            'ficheiros_visao_geral' => array_map(function ($file) {
                return [
                    'nome_ficheiro' => $file->filename ?? '',
                    'caminho_ficheiro' => $file->filepath ?? '',
                    'tamanho_ficheiro' => $file->filesize ?? '',
                    'url_ficheiro' => $file->fileurl ?? '',
                    'modificacao' => $file->timemodified ?? '',
                    'tipo_mime' => $file->mimetype ?? '',
                ];
            }, $this->overviewfiles ?? []),
            'mostrar_atividades' => $this->showactivitydates ?? '',
            'mostrar_condicoes_conclusao' => $this->showcompletionconditions ?? '',
            'lista_docentes' => array_map(function ($contact) {
                return [
                    'id' => $contact->id ?? '',
                    'nome_completo' => $contact->fullname ?? '',
                ];
            }, $this->contacts ?? []),
            'tipo_inscricoes' => $this->enrollmentmethods   ?? '',
            'semestre' => $mimService->get_semestre($this->shortname) ?? '',
        ];
    }



}
