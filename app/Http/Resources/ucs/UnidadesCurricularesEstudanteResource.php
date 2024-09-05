<?php

namespace App\Http\Resources\ucs;

use App\Services\MimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnidadesCurricularesEstudanteResource extends JsonResource
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

        $info_ultima_modificacao = $mimService->calcular_dias_ultimo_acesso($this->timemodified);

        return [
            'id' => $this->id,
            'nome_completo' => $this->fullname,
            'nome_exibicao' => $this->displayname,
            'numero_inscritos_uc' => $this->enrolledusercount,
            'numero_id' => $this->idnumber,
            'visivel' => $this->visible,
            'resumo' => $this->summary,
            'formato_resumo' => $this->summaryformat,
            'formato' => $this->format,
            'mostrar_notas' => $this->showgrades,
            'linguagem' => $this->lang,
            'ativar_conclusao' => $this->enablecompletion,
            'criterio_conclusao' => $this->completionhascriteria,
            'conlusao_utilizador_rastreio' => $this->completionusertracked,
            'id_categoria' => $this->category,
            'progresso' => $this->progress,
            'completo' => $this->completed,
            'data_inicio' => $info_dias_calculados_data_inicio['dataFormatada'],//informação transformada
            'dias_desde_inicio' => $info_dias_calculados_data_inicio['diasDesdeUltimoAcesso'],//informação adicionada
            'data_fim' => $info_dias_calculados_data_fim['dataFormatada'],//informação transformada
            'dias_desde_fim' => $info_dias_calculados_data_fim['diasDesdeUltimoAcesso'],//informação adicionada
            'marcador' => $this->marker,
            'ultimo_acesso_uc' => $info_dias_calculados_ultimo_acesso['dataFormatada'],//informação transformada
            'dias_desde_ultimo_acesso' => $info_dias_calculados_ultimo_acesso['diasDesdeUltimoAcesso'],//informação adicionada
            'favorito' => $this->isfavourite,
            'oculto' => $this->hidden,
            'ficheiros_visao_geral' => array_map(function ($file) {
                return [
                    'nome_ficheiro' => $file->filename,
                    'caminho_ficheiro' => $file->filepath,
                    'tamanho_ficheiro' => $file->filesize,
                    'url_ficheiro' => $file->fileurl,
                    'modificacao' => $file->timemodified,
                    'tipo_mime' => $file->mimetype,
                ];
            }, $this->overviewfiles),
            'mostrar_atividades' => $this->showactivitydates,
            'mostrar_condicoes_conclusao' => $this->showcompletionconditions,
            'data_modificacao' => $info_ultima_modificacao['dataFormatada'],
        ];
    }
}

