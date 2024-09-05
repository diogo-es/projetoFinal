<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\cursos\CursoResource;
use App\Services\MimService;
use Illuminate\Support\Facades\Log;

class CursoController extends Controller
{


    /**
     * Obter todos os cursos do moodle.
     */
    public function obter_cursos(MimService $mimService)
    {
        try {
            $response = $mimService->make_request('core_course_get_categories', 'json');
            $courses = $response;

            $filteredCourses = [];
            foreach ($courses as $course) {
                if ($course->depth == 3) {
                    $course->parent = self::obter_nome_do_grau_academico($courses, $course->parent);

                    // Divide a string por /. E cria um array com os valores.
                    $id_escola = explode('/', $course->path)[1];
                    $course->escola = self::obter_nome_da_escola($courses, $id_escola);

                    $filteredCourses[] = $course;

                }
            }

            return CursoResource::collection($filteredCourses);
        } catch (\Exception $e) {
            // Logar o erro e a resposta da API do Moodle
            Log::error('Erro ao obter os Cursos', [
                'exception' => $e->getMessage(),
                'moodle-response' => $response ?? 'No response'
            ]);

            return response()->json([
                'message' => 'Falha a obter os cursos. Por favor tente mais tarde.',
                'details' => $e->getMessage() // Atenção: tirar isto quando for para produção
            ], 500);
        }


    }


    public static function obter_nome_do_grau_academico($courses, $id)
    {
        foreach ($courses as $course) {
            if ($course->id == $id) {
                return $course->name;
            }
        }
        return null;
    }

    public static function obter_nome_da_escola($courses, $escola_id)
    {
        foreach ($courses as $course) {
            if ($course->id == $escola_id) {
                return $course->name;
            }
        }
        return null;
    }

}
