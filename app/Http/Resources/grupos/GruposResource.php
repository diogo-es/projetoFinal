<?php

namespace App\Http\Resources\grupos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GruposResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_grupo' => $this->id,
            'uc_id' => $this->courseid,
            'nome_grupo' => $this->name,
            'descricao' => $this->description,
            'formato_descricao' => $this->descriptionformat,
            'chave_inscricao' => $this->enrolmentkey,
            'numero_id' => $this->idnumber,
            'elementos_grupo' => array_map(function ($user) {
                return [
                    'id' => $user['id'],
                    'nome_completo' => $user['name'][0]->fullname,
                    'email' => $user['name'][0]->email,
                    'imagem_perfil' => $user['name'][0]->profileimageurl,
                    'imagem_perfil_pequena' => $user['name'][0]->profileimageurlsmall,
                ];
            }, $this->users),
        ];
    }
}
