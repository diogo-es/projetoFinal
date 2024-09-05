<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\acessos\AcessosEstudanteResource;
use App\Http\Resources\acessos\AcessosResource;
use App\Http\Resources\acessos\acessosTodosUsersCursoResource;
use App\Http\Resources\grupos\FiltrarCamposGruposResource;
use App\Http\Resources\grupos\GruposEstudanteResource;
use App\Http\Resources\grupos\GruposResource;
use App\Http\Resources\ucs\FiltrarCamposUnidadeCurricularResource;
use App\Http\Resources\ucs\FiltrarCamposUnidadesCurricularesEstudanteResource;
use App\Http\Resources\ucs\MostrarUnidadeCurricularResource;
use App\Http\Resources\ucs\UnidadesCurricularesEstudanteResource;
use App\Models\Curso;
use App\Models\UnidadeCurricular;
use App\Models\UnidadeCurricularAgregadora;
use App\Services\MimService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnidadeCurricularController extends Controller
{

    public function teste(MimService $mimService)
    {
        $mimService->obter_ucs_todos_cursos();
        return response()->json(['message' => 'Teste']);
    }


    /**
     * @throws Exception
     */
    public function obter_ucs_curso($cod_curso, Request $request, MimService $mimService)
    {
        try {
            // Validar o codigo do curso da URL
            $moodleId = $mimService->verificar_curso_atraves_codigo($cod_curso);
            if ($moodleId === null) {
                Log::error('O código do curso não foi encontrado na base de dados.', ['codigo' => $cod_curso]);
                return response()->json(['error' => 'O Curso não foi encontrado'], 404);
            }

            $response = $mimService->make_request('core_course_get_courses_by_field', 'json', ['field' => 'category',
                'value' => $moodleId]);
            $ucsNaoAgregadoras = $response->courses;


            /*
                Ucs do endpoint + ucs do ficheiro de importação. AGREGADAS
            */

            //obter todas as ucs que pertencem ao curso com o código $cod_curso
            $curso = Curso::where('codigo', $cod_curso)->first();
            $unidadesCurriculares = UnidadeCurricular::where('curso_id', $curso->id)->get();

            $ucsNaoAgregadoras = $mimService->unir_arrays($ucsNaoAgregadoras, $unidadesCurriculares->toArray());


            /*
                Obter as ucs de um dado curso, que são as AGREGADORAS(csv apenas).
            */

            // obter o curso atraves do codigo
            $curso = Curso::where('codigo', $cod_curso)->first();

            $results = UnidadeCurricularAgregadora::select('unidades_curriculares_agregadoras.*')
                ->join('agregadora_agregada', 'unidades_curriculares_agregadoras.id', '=', 'agregadora_agregada.id_agregadora')
                ->join('unidades_curriculares', 'unidades_curriculares.id', '=', 'agregadora_agregada.id_agregada')
                ->where('unidades_curriculares.curso_id', $curso->id)
                ->get();

            $ucsAgregadoras = $results->toArray();


            /*
                Unir UCs agregadoras e não agregadoras.
            */

            // juntar as ucs que não são agregadoras com as que são
            $ucs = $mimService->unir_arrays($ucsNaoAgregadoras, $ucsAgregadoras);
            $ucs = json_decode(json_encode($ucs));

            // Para se poder obter o id do curso do moodle das ucs que vem do ficheiro de importação
            array_map(function ($uc) use ($moodleId) {
                $uc->extra_data = $moodleId;
            }, $ucs);


            $param1 = $request->query('semestre');
            foreach ($ucs as $key => $uc) {
                if ($mimService->get_semestre($uc->shortname) != strtoupper($param1) && $param1 != null) {
                    unset($ucs[$key]);
                }
            }


            $param2 = filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN);
            if ($param2) {
                return MostrarUnidadeCurricularResource::collection($ucs);
            }

            return FiltrarCamposUnidadeCurricularResource::collection($ucs);


        } catch (Exception $e) {
            // Logar o erro e a resposta da API, se disponível
            Log::error('Erro ao obter as UCs do Curso', [
                'exception' => $e->getMessage(),
                'moodle-response' => $response ?? 'No response'
            ]);

            // Retornar uma resposta de erro padronizada ao cliente
            return response()->json([
                'message' => 'Falha a obter as UCs do Curso. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Opcional: você pode omitir detalhes específicos em produção
            ], 500);
        }

    }


    public function obter_uc_curso($cod_curso, $uc_shortname, Request $request, MimService $mimService)
    {
        try {

            $moodleId = $mimService->verificar_curso_atraves_codigo($cod_curso);
            if ($moodleId === null) {
                return response()->json(['error' => 'O Curso não foi encontrado'], 404);
            }

            $id_uc_moodle = $mimService->correspondencia_codigo_curso_uc_shortname($uc_shortname, $moodleId);//verficar se o shortname, está associado ao curso

            if ($id_uc_moodle === null) {//se tiver associado, retorna o id do Moodle que lhe corresponde
                return response()->json(['error' => 'Esta Unidade Curricular não pertence a este Curso.'], 404);
            }

            //chamar a função que vai buscar as unidades curriculares de um dado curso
            $response = $mimService->make_request('core_course_get_courses_by_field', 'json', [
                'field' => 'shortname',
                'value' => $uc_shortname
            ]);
            $uc = $response->courses;

            // encontrar o id do curso no moodle atraves do codigo do curso
            $curso_moodleId = Curso::where('codigo', $cod_curso)->value('moodle_id');
            if ($curso_moodleId != $uc[0]->categoryid and $uc == []) {
                return response()->json(['error' => 'O Curso não possui nenhuma UC com esse shortname'], 404);
            }

            $param1 = filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN);
            if ($param1) {
                return MostrarUnidadeCurricularResource::collection($uc);
            }
            return FiltrarCamposUnidadeCurricularResource::collection($uc);

        } catch (Exception $e) {
            // Logar o erro e a resposta da API do Moodle
            Log::error('Erro ao obter a UC', [
                'exception' => $e->getMessage(),
                'moodle-response' => $response ?? 'No response'
            ]);


            // Retornar uma resposta de erro padronizada ao cliente
            return response()->json([
                'message' => 'Falha a obter a uc. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Atenção: tirar isto quando for para produção
            ], 500);
        }

    }


    public static function obter_uc_grupos($cod_curso, $uc_shortname, Request $request, MimService $mimService)
    {

        try {

            $id_curso_moodle = $mimService->verificar_curso_atraves_codigo($cod_curso);//verficar se o código do curso exite
            if ($id_curso_moodle === null) {//se o código do existir, retorna o id do Moodle que lhe corresponde
                return response()->json(['error' => 'O Curso não foi encontrado'], 404);
            }


            $id_uc_moodle = $mimService->correspondencia_codigo_curso_uc_shortname($uc_shortname, $id_curso_moodle);//verficar se o shortname, está associado ao curso

            if ($id_uc_moodle === null) {//se tiver associado, retorna o id do Moodle que lhe corresponde
                return response()->json(['error' => 'Esta Unidade Curricular não pertence a este Curso.'], 404);
            }

            $response = $mimService->make_request('core_group_get_course_groups', 'json', ['courseid' => $id_uc_moodle]);//obtem os grupos da UC
            $grupos = $response;

            if (empty($grupos)) {
                return response()->json(['error' => 'Não foi encontrado nenhum grupo na UC'], 404);
            }

            $params = [];
            foreach ($grupos as $index => $uc) {
                if (isset($uc->id) && is_numeric($uc->id)) {
                    $groupId = $uc->id;
                    $params["groupids[$index]"] = $groupId;
                }
            }

            $response = $mimService->make_request('core_group_get_group_members', 'json', $params);//obter os membros dos grupos
            $responseData = $response;

            if (gettype($responseData) == 'object') {
                // Logar o erro e a resposta da API do Moodle
                Log::error('Erro ao obter os grupos da UC', [
                    'moodle-response' => $response ?? 'No response'
                ]);
                return response()->json(['error' => 'Erro ao obter os grupos'], 404);
            }


            $gruposCollection = collect($grupos)->map(function ($item) use ($responseData, $mimService) {

                $item->users = [];

                // Procura pelos userids correspondentes ao groupid
                foreach ($responseData as $group) {
                    if (isset($group->groupid) && $group->groupid == $item->id) {
                        foreach ($group->userids as $userId) {
                            $userInfo = $mimService->make_request('core_user_get_users_by_field', 'json', ['field' => 'id', 'values[0]' => $userId]);//obter os dados de cada membro do grupo

                            $item->users[] = [
                                'id' => $userId,
                                'name' => $userInfo,
                            ];
                        }
                        break;
                    }
                }

                return $item;
            });

            $param1 = filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN);

            foreach ($gruposCollection as $key => $dado) {
                if ($param1) {
                    return GruposResource::collection($gruposCollection);
                }
            }
            return FiltrarCamposGruposResource::collection($gruposCollection);

        } catch (Exception $e) {
            // Logar o erro e a resposta da API do Moodle
            Log::error('Erro ao obter os grupos da UC', [
                'exception' => $e->getMessage(),
                'moodle-response' => $response ?? 'No response'
            ]);

            return response()->json([
                'message' => 'Falha a obter os grupos da UC. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Atenção: tirar isto quando for para produção
            ], 500);
        }


    }


    public static function obter_uc_acessos($cod_curso, $uc_shortname, MimService $mimService)
    {
        try {

            $id_bd = $mimService->verificar_curso_atraves_codigo($cod_curso);//verficar se o código do curso exite
            if ($id_bd === null) {//se o código do existir, retorna o id do Moodle que lhe corresponde
                return response()->json(['error' => 'O Curso não foi encontrado'], 404);
            }

            $res = $mimService->correspondencia_codigo_curso_uc_shortname($uc_shortname, $id_bd);//verficar se o shortname, está associado ao curso
            if ($res === null) {//se tiver associado, retorna o id do Moodle que lhe corresponde
                return response()->json(['error' => 'Esta Unidade Curricular não pertence a este Curso.'], 404);
            }

            $response = $mimService->make_request('core_course_get_courses_by_field', 'json',
                ['field' => 'shortname', 'value' => $uc_shortname]);

            if (!empty($response->courses)) {
                $course_id = $response->courses[0]->id; // obter o id da unidade curricular

                $nome_uc = $response->courses[0]->fullname;
            } else {
                throw new Exception('A UC não foi encontrada!');
            }

            //obtém os acessos dos utilizadores para aquela UC especifica
            $response = $mimService->make_request('core_enrol_get_enrolled_users', 'json', ['courseid' => $course_id,
                'options[0][name]' => 'userfields', 'options[0][value]' => 'lastcourseaccess']);
            $acessos = $response;

            if (empty($acessos)) {
                return response()->json(['error' => 'Não foi encontrado nenhum acesso na UC!'], 404);
            }


            $acessosCollection = collect($acessos)->map(function ($item) use ($mimService, $nome_uc) {
                $item = (object)$item; // Converte o array num objeto

                $userInfo = $mimService->make_request('core_user_get_users_by_field', 'json', ['field' => 'id', 'values[0]' => $item->id]);

                $item->user_fullname = !empty($userInfo) && isset($userInfo[0]->fullname) ? $userInfo[0]->fullname : '';
                $item->user_username = !empty($userInfo) && isset($userInfo[0]->username) ? $userInfo[0]->username : '';
                $item->user_email = !empty($userInfo) && isset($userInfo[0]->email) ? $userInfo[0]->email : '';
                $item->uc_name = $nome_uc;
                $item->ultimo_acesso_site = $mimService->calcular_dias_ultimo_acesso($userInfo[0]->lastaccess)['dataFormatada'];//transforma a informação do último acesso à plataforma
                $item->dias_desde_ultimo_acesso_site = $mimService->calcular_dias_ultimo_acesso($userInfo[0]->lastaccess)['diasDesdeUltimoAcesso'];

                return $item;
            });

            return AcessosResource::collection($acessosCollection);


        } catch (Exception $e) {
            // Logar o erro e a resposta da API do Moodle
            Log::error('Erro ao obter os acessos da UC', [
                'exception' => $e->getMessage(),
                'response' => $response ?? 'No response'
            ]);

            // Retornar uma resposta de erro padronizada ao cliente
            return response()->json([
                'message' => 'Falha a obter os acessos da UC. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Atenção: tirar isto quando for para produção
            ], 500);
        }

    }

    public static function obter_uc_acessos_de_um_curso($cod_curso, MimService $mimService)
    {

        try {

            $moodle_id = $mimService->verificar_curso_atraves_codigo($cod_curso);
            if ($moodle_id === null) {
                return response()->json(['error' => 'O Curso não foi encontrado'], 404);
            }

            $curso = Curso::where('codigo', $cod_curso)->first();

            //Obter as UCs agregadoras de um curso
            $results = UnidadeCurricularAgregadora::select('unidades_curriculares_agregadoras.*')
                ->join('agregadora_agregada', 'unidades_curriculares_agregadoras.id', '=', 'agregadora_agregada.id_agregadora')
                ->join('unidades_curriculares', 'unidades_curriculares.id', '=', 'agregadora_agregada.id_agregada')
                ->where('unidades_curriculares.curso_id', $curso->id)
                ->get();

            //Obter as UCs não agregadoras de um curso
            $results2 = UnidadeCurricular::select('unidades_curriculares.*')
                ->where('curso_id', $curso->id)
                ->get();

            $combinedResults = $results->merge($results2); // Unir os resultados das duas queries

            $ucs_de_um_curso = $combinedResults->toArray();

            $acessosUsuarios = [];

            foreach ($ucs_de_um_curso as $uc) {

                $response = $mimService->make_request('core_enrol_get_enrolled_users', 'json', [//obter os acessos dos utilizadores para aquela UC especifica
                    'courseid' => $uc['moodle_id'],
                    'options[0][name]' => 'userfields', 'options[0][value]' => 'lastcourseaccess',
                    'options[1][name]' => 'userfields', 'options[1][value]' => 'lastaccess'
                ]);
                $acessos = $response;


                foreach ($acessos as $acesso) {
                    if (!isset($acesso->id)) {
                        continue;
                    }
                    $userId = $acesso->id;
                    $fullname = $acesso->fullname;
                    $lastcourseaccess = $acesso->lastcourseaccess;
                    $lastaccess = $acesso->lastaccess;

                    if (!isset($acessosUsuarios[$userId])) {
                        $acessosUsuarios[$userId] = [
                            'id' => $userId,
                            'fullname' => $fullname,
                            'ucs' => []
                        ];
                    }

                    $acessosUsuarios[$userId]['ucs'][] = [
                        'uc_name' => $uc['nome'], // Adicionando o nome da UC
                        'lastcourseaccess' => $lastcourseaccess,
                        'lastaccess' => $lastaccess
                    ];
                }

            }

            if (empty($acessosUsuarios)) {
                // Logar o erro e a resposta da API do Moodle
                Log::error('Erro ao obter os acessos de um curso.', [
                    'moodle-response' => $response ?? 'No response'
                ]);
                return response()->json(['error' => 'Erro ao obter os acessos de um curso.'], 404);
            }


            return acessosTodosUsersCursoResource::collection($acessosUsuarios);


        } catch (Exception $e) {
            // Logar o erro e a resposta da API do Moodle
            Log::error('Erro ao obter os acessos da UC', [
                'exception' => $e->getMessage(),
                'moodle-response' => $response ?? 'No response'
            ]);

            return response()->json([
                'message' => 'Falha a obter os acessos da UC. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Atenção: tirar isto quando for para produção
            ], 500);
        }

    }

    // DEPRECATED
    public static function obter_ucs_user($email_estudante, MimService $mimService, Request $request)
    {
        $response1 = $mimService->make_request('core_user_get_users_by_field', 'json',
            ['field' => 'email', 'values[0]' => $email_estudante]);

        $dados_estudante = $response1;

        if (empty($dados_estudante)) {
            return response()->json(['error' => 'Não foi encontrado nenhum estudante com esse e-email'], 404);
        }

        $id_user = $dados_estudante[0]->id;

        $ucs_do_estudante = $mimService->make_request('core_enrol_get_users_courses', 'json', ['userid' => $id_user]);
        if (empty($ucs_do_estudante)) {
            return response()->json(['error' => 'O estudante não está inscrito em nenhum UC'], 404);
        }

        $param2 = filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN);

        if ($param2) {
            return UnidadesCurricularesEstudanteResource::collection($ucs_do_estudante);
        }

        return FiltrarCamposUnidadesCurricularesEstudanteResource::collection($ucs_do_estudante);
    }

    // DEPRECATED
    public static function obter_acessos_user($email_estudante, MimService $mimService, Request $request)
    {
        $response1 = $mimService->make_request('core_user_get_users_by_field', 'json',
            ['field' => 'email', 'values[0]' => $email_estudante]);

        $dados_estudante = $response1;

        if (empty($dados_estudante)) {
            return response()->json(['error' => 'Não foi encontrado nenhum estudante com esse e-email'], 404);
        }

        $id_user = $dados_estudante[0]->id;

        $acessos_do_estudante = $mimService->make_request('core_enrol_get_users_courses', 'json', ['userid' => $id_user]);
        if (empty($acessos_do_estudante)) {
            return response()->json(['error' => 'O estudante não está inscrito em nenhuma UC'], 404);
        }

        return AcessosEstudanteResource::collection($acessos_do_estudante);
    }

    // DEPRECATED
    public static function obter_acessos_user_v2($uc_shortname, $nr_estudante, MimService $mimService, Request $request)
    {
        $uc_id_moodle = $mimService->verificar_uc_atraves_shortname($uc_shortname);

        $response1 = $mimService->make_request('core_enrol_get_enrolled_users', 'json',
            ['courseid' => $uc_id_moodle]);

        if (empty($response1)) {
            return response()->json(['error' => 'Não foi encontrada informacao para essa Unidade Curricular'], 404);
        }

        // Variável para armazenar o ID do curso correspondente
        $course_id = null;

        // Percorrendo o array de usuários
        foreach ($response1 as $user) {
            if ($user->email === $nr_estudante) {
                $userid_moodle = $user->id;
                if ($userid_moodle) {
                    $acessos_do_estudante = $mimService->make_request('core_enrol_get_users_courses', 'json', ['userid' => $userid_moodle]);
                    if (empty($acessos_do_estudante)) {
                        return response()->json(['error' => 'O estudante não está inscrito nesta UC, nao e possivel obter os seus dados'], 404);
                    }
                    return AcessosEstudanteResource::collection($acessos_do_estudante);

                } else {
                    return response()->json(['error' => 'Não foi encontrado nenhum estudante com esse e-email!'], 404);
                }
            }
        }


    }

    // DEPRECATED
    public static function obter_grupos_user($email_estudante, MimService $mimService, Request $request)
    {
        $response1 = $mimService->make_request('core_user_get_users_by_field', 'json',
            ['field' => 'email', 'values[0]' => $email_estudante]);

        $dados_estudante = $response1;
        if (empty($dados_estudante)) {
            return response()->json(['error' => 'Não foi encontrado nenhum estudante com esse e-email'], 404);
        }

        $id_user = $dados_estudante[0]->id;

        $grupos_do_estudante = $mimService->make_request('core_group_get_course_user_groups', 'json', ['userid' => $id_user]);
        $grupos = $grupos_do_estudante->groups;


        return GruposEstudanteResource::collection($grupos);
    }

}


