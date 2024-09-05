<?php

namespace App\Services;

use App\Http\Controllers\api\CursoController;
use App\Models\AgregadoraAgregada;
use App\Models\Curso;
use App\Models\UnidadeCurricular;
use App\Models\UnidadeCurricularAgregadora;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use League\Csv\Reader;


class MimService
{

    const URL = 'http://localhost/webservice/rest/server.php';
    const MOODLE_URL = '';

    const TOKEN_COORDINATOR = '';
    const TOKEN_DOCENTE = '';
    const TOKEN_DOCENTE_3 = '';
    const TOKEN_DOCENTE_5 = '';

    const TOKEN = '';
    const ADMIN = '';


    public function make_request($function, $format, $optionalParams = [])
    {
        try {
            $token = session('moodle_token', self::TOKEN_DOCENTE_5);

            if (!$token) {
                throw new \RuntimeException('Token não definido na sessão.');
            }

            // parametros obrigatórios para fazer um pedido à api do moodle
            $params = [
                'wstoken' => $token,
                'wsfunction' => $function,
                'moodlewsrestformat' => $format,
            ];
            $allParams = array_merge($params, $optionalParams);

            $response = Http::get(self::MOODLE_URL, $allParams)->throw();
            return $response->object();

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to make the request: ' . $e->getMessage());
        }

    }

    public function extract_designacao_curso_codigo($campo)
    {
        // Separar os valores através do delimitador '::'
        $designacao = explode('::', $campo);

        // Garantir que a divisão ocorreu corretamente
        $codigoCurso = isset($designacao[0]) ? trim($designacao[0]) : null;
        $designacaoCurso = isset($designacao[1]) ? trim($designacao[1]) : null;

        return [
            'codigoCurso' => $codigoCurso,
            'designacaoCurso' => $designacaoCurso,
        ];

    }

    public function calcular_dias_ultimo_acesso($lastcourseaccess)
    {
        date_default_timezone_set('Europe/Lisbon');

        $tempoAtual = time();
        if ($lastcourseaccess == 0) {
            $dataFormatada = -1;
            $diasDesdeUltimoAcesso = -1;
        } else {
            $dataFormatada = date('d/m/Y H:i:s', $lastcourseaccess);
            $diasDesdeUltimoAcesso = floor(($tempoAtual - $lastcourseaccess) / (60 * 60 * 24));
        }

        return [
            'dataFormatada' => $dataFormatada,
            'diasDesdeUltimoAcesso' => $diasDesdeUltimoAcesso,
        ];

    }

    public function verificar_curso_atraves_codigo($codigo_curso)
    {
        $curso = Curso::where('codigo', $codigo_curso)->first();
        if ($curso) {
            return $curso->moodle_id;
        }
        return null;

    }

    public function verificar_uc_atraves_shortname($uc_shortname)
    {
        $uc = UnidadeCurricular::where('shortname', $uc_shortname)->first();
        if ($uc) {
            return $uc->moodle_id;
        } else {
            throw new \Exception('Unidade Curricular não encontrada');
        }

    }


    public function extract_info_from_field($field, $separator, $desired_info_index = null)
    {
        // Separar os valores através do delimitador $separator
        $field_parts = explode($separator, $field);

        // Garantir que a divisão ocorreu corretamente
        $desired_info = isset($field_parts[$desired_info_index]) ? trim($field_parts[$desired_info_index]) : null;
        if ($desired_info == null) {
            return $field_parts;
        }
        return $desired_info;

    }

    public function correspondencia_codigo_curso_uc_shortname($uc_shortname, $curso_moodle_id)
    {

        /*
            Verificar se a UC pertence ao curso, quando a UC é agregada.
        */

        $curso = Curso::where('moodle_id', $curso_moodle_id)->first();


        $uc = UnidadeCurricular::where('shortname', $uc_shortname)
            ->where('curso_id', $curso->id)
            ->first();

        if ($uc) {
            return $uc->moodle_id;

        } else {

            /*
                Verificar se a UC pertence ao curso, quando a UC é agregadora.

                Neste caso, como a UC agregadora não tem um curso associado, temos de verificar se a UC agregada desta UC agregadora pertence ao curso.
            */

            // obter moodle_id da UC agregadora através do shortname
            $moodle_id = UnidadeCurricularAgregadora::Where('shortname', $uc_shortname)->value('moodle_id');

            // obter id da UC agregadora através do moodle_id
            $id_bd = UnidadeCurricularAgregadora::where('moodle_id', $moodle_id)->value('id');

            // obter filhas da UC agregadora
            $filhas = AgregadoraAgregada::where('id_agregadora', $id_bd)->get();

            $filhas_ids = [];
            // obter o moodle_id do/os curso/os das filhas da UC agregadora
            foreach ($filhas as $filha) {
                $curso_id = UnidadeCurricular::where('id', $filha->id_agregada)->value('curso_id');
                $curso_moodleId_filha = Curso::where('id', $curso_id)->value('moodle_id');
                $filhas_ids[] = $curso_moodleId_filha;
            }

            // ver se a UC pertence ao curso
            if (in_array($curso_moodle_id, $filhas_ids)) {
                return $moodle_id;
            } else {
                return null;
            }

        }

    }


    public function get_semestre($shortname)
    {
        if (count(explode('_', $shortname)) == 3) {
            // No caso de ser uma UC agregada ou normal
            return $this->extract_info_from_field($shortname, '_', 2);
        }
        // No caso de ser uma UC agregadora
        return $this->extract_info_from_field($shortname, '_', 4);
    }


    public function importar_ucs_agregadas_com_csv(): void
    {
        // Caminho para o ficheiro CSV
        $filePath = storage_path('app/public/UC_agregadas.csv');

        $csv = Reader::createFromPath($filePath, 'r');

        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $count = 0;

        DB::transaction(function () use ($records, &$count) {
            foreach ($records as $record) {
                $count++;

                $ucs = $this->extract_info_from_field($record['uc agregadas'], ';');

                foreach ($ucs as $uc) {
                    $uc = trim($uc);
                    // Processamento das UCs agregadas

                    // 1º passo: obter shortname, nome, moodle_id e id_curso da UC agregada (filha)
                    $startPos = strpos($uc, "-");
                    $endPos = strpos($uc, ':');
                    $agregada_uc_shortname = trim(substr($uc, $startPos + 1, $endPos - $startPos - 1));

                    $endPos = strpos($uc, ' ');
                    $agregada_uc_id = substr($uc, 0, $endPos);

                    $start = strpos($uc, ':: ') + 3;
                    $end = strpos($uc, ' @', $start);
                    $nomeAgregada = substr($uc, $start, $end - $start);

                    $position = strpos($uc, '@');
                    $cod_curso = substr($uc, $position + 2, 4);


                    $id_curso = Curso::where('codigo', $cod_curso)->value('id');

                    // Verificar se a UC já existe na base de dados
                    $existingUc = UnidadeCurricular::where('shortname', $agregada_uc_shortname)->first();

                    if (!$existingUc) {
                        // 2º passo: guardar na base de dados a UC agregada(filha)
                        $newUc = UnidadeCurricular::firstOrCreate([
                            'nome' => $nomeAgregada,
                            'shortname' => $agregada_uc_shortname,
                            'moodle_id' => $agregada_uc_id,
                            'curso_id' => $id_curso,
                        ]);
                        $uc_id = $newUc->id;
                    } else {
                        $uc_id = $existingUc->id;
                    }


                    // Processamento das UCs agregadoras

                    // 3º passo: obter shortname, moodle_id, nome, e id_agregada da UC agregadora (pai)
                    $uc_shortname_pai = $record['código'];
                    $moodle_id_pai = $record['id'];

                    $start = strpos($record['uc agregadora'], ':: ') + 3;
                    $nome = substr($record['uc agregadora'], $start);

                    // 4º passo: guardar na base de dados a UC agregadora (pai)
                    $uc_agregadora = UnidadeCurricularAgregadora::firstOrCreate([
                        'nome' => $nome,
                        'shortname' => $uc_shortname_pai,
                        'moodle_id' => $moodle_id_pai,
                    ]);

                    // 5º passo: guardar na base de dados na tabela agregadora_agregada (id_agregadora, id_agregada)
                    AgregadoraAgregada::firstOrCreate([
                        'id_agregadora' => $uc_agregadora->id,
                        'id_agregada' => $uc_id,
                    ]);


                }


            }
        });

    }


    public function unir_arrays($array1, $array2)
    {
        $mergedArray = [];

        // Unir ambos os arrays (pode haver duplicados)
        $combinedArray = array_merge($array1, $array2);

        // Converter todos os itens para arrays
        $combinedArray = array_map(function ($item) {
            return (array)$item;
        }, $combinedArray);

        // Garantir que não há duplicados
        foreach ($combinedArray as $item) {
            $shortname = $item['shortname'];

            if (!isset($mergedArray[$shortname])) {
                // Se o shortname ainda não está no array combinado, adicione-o
                $mergedArray[$shortname] = $item;
            } else {
                // Se o shortname já está no array combinado, mantenha o item com mais dados
                $currentItem = $mergedArray[$shortname];
                $currentItemFieldCount = count(array_filter($currentItem, function ($value) {
                    return $value !== null && $value !== '';
                }));
                $newItemFieldCount = count(array_filter($item, function ($value) {
                    return $value !== null && $value !== '';
                }));

                if ($newItemFieldCount > $currentItemFieldCount) {
                    $mergedArray[$shortname] = $item;
                }
            }
        }

        // Converter de volta para array indexado
        return array_values($mergedArray);
    }


    public function encontrar_nome_uc_atraves_id($id_uc)
    {
        // TODO: Verificar se esta a ser usada em algum lado.
        $uc = UnidadeCurricular::where('moodle_id', $id_uc)->first();
        if ($uc) {
            return $uc->shortname;
        } else {
            throw new \Exception('Curso não encontrado');
        }
    }

    //Tarefas Agendadas
    public function obter_periodicamente_cursos()
    {
        // TODO: Testar isto!! depois daquelas duas linhas comentadas
        $mimService = new MimService();
        $controller = new CursoController();
        $response = $mimService->make_request('core_course_get_categories', 'json');
        $courses = $response;

        //$filteredCourses = [];
        foreach ($courses as $course) {
            if ($course->depth == 3) {
                $course->parent = $controller->obter_nome_do_grau_academico($courses, $course->parent);
                //$filteredCourses[] = $course;
                $info_cursos_extraida = $mimService->extract_designacao_curso_codigo($course->name);

                //DB::transaction(function () use ($info_cursos_extraida, $course) {
                Curso::firstOrCreate([
                    'nome' => $info_cursos_extraida['designacaoCurso'],
                    'codigo' => $info_cursos_extraida['codigoCurso'],
                    'moodle_id' => $course->id,
                ]);
                //});

            }
        }

    }


    //Tarefas Agendadasv2
    public function obter_ucs_todos_cursos()
    {

        $mimService = new MimService();

        $response = $mimService->make_request('core_course_get_categories', 'json');
        $courses = $response;

        $ucs_mim = $this->obter_ucs_todos_cursos_aux(255, $mimService);//Curso com id 255 é o curso "EI - Projeto - MIM"

        foreach ($ucs_mim as $uc) {

            $curso_id = Curso::where('codigo', 9999)->value('id');

            UnidadeCurricular::firstOrCreate([
                'nome' => $uc->fullname,
                'shortname' => $uc->shortname,
                'moodle_id' => $uc->id,
                'curso_id' => $curso_id
            ]);
        }

        foreach ($courses as &$course) {
            if ($course->depth == 3) {
                $this->obter_ucs_todos_cursos_aux($course->id, $mimService);
            }
        }

    }

    public function obter_ucs_todos_cursos_aux($curso_moodleId, MimService $mimService)
    {

        $ucs = $mimService->make_request('core_course_get_courses_by_field', 'json', ['field' => 'category',
            'value' => $curso_moodleId]);
        $ucsNaoAgregadoras = $ucs->courses;


        foreach ($ucsNaoAgregadoras as $uc) {

            // Check if curso_id exists in cursos table -> o corrige o problema de poder haver algum curso que não exista na base de dados, por causa do token
            $cursoExists = Curso::where('id', $curso_moodleId)->exists();
            if (!$cursoExists) {
                //dd($cursoExists);
                continue; // Skip this record and continue with the next one
            }

            $ucExists = UnidadeCurricular::where('shortname', $uc->shortname)->exists();
            if ($ucExists) {
                // saltar esta UC e continuar com a próxima
                continue;
            }
            $id_bd = Curso::where('moodle_id', $curso_moodleId)->value('id');

            UnidadeCurricular::firstOrCreate([
                'nome' => $uc->fullname,
                'shortname' => $uc->shortname,
                'moodle_id' => $uc->id,
                'curso_id' => $id_bd
            ]);
        }
        return $ucsNaoAgregadoras;
    }
}
