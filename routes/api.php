<?php

use App\Http\Controllers\api\CursoController;
use App\Http\Controllers\api\UnidadeCurricularController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware(['web'])->group(function () {
//CURSOS
    Route::get('cursos', [CursoController::class, 'obter_cursos'])->name('cursos');

    Route::get('testes', [UnidadeCurricularController::class, 'teste'])
        ->name('testes');


//UNIDADES CURRICULARES
//Obter todas as ucs de um dado curso
    Route::get('cursos/{cod_curso}/unidades-curriculares', [UnidadeCurricularController::class, 'obter_ucs_curso'])
        ->name('unidades-curriculares');

//Obter informação de uma uc em específico de um dado curso
    Route::get('cursos/{cod_curso}/unidades-curriculares/{uc_shortname}', [UnidadeCurricularController::class, 'obter_uc_curso'])
        ->name('unidade-curricular');


//GRUPOS
//obter informação de um grupo de uma UC de um dado Curso
    Route::get('cursos/{cod_curso}/unidades-curriculares/{uc_shortname}/grupos', [UnidadeCurricularController::class, 'obter_uc_grupos'])
        ->name('grupos');


//ACESSOS
//obter acessos de uma dada uc
    Route::get('cursos/{cod_curso}/unidades-curriculares/{uc_shortname}/acessos', [UnidadeCurricularController::class, 'obter_uc_acessos'])
        ->name('acessos');

//obter todos os acessos para um Curso
    Route::get('cursos/{cod_curso}/acessos', [UnidadeCurricularController::class, 'obter_uc_acessos_de_um_curso'])
        ->name('acessos-todos-users-curso');


//ENDPOINTS DEPRECIADOS
//--------------------------------------------------------------------
    /*Alguns detes endpoints utilizam, o e-mail como parâmetro de entrada, no entanto, não é o ideal,
    foi usados apenas como uma  possível solução para contornar problemas de privilégios*/

//Acessos de um dado estudante
    Route::get('estudantes/{email_estudante}/acessos', [UnidadeCurricularController::class, 'obter_acessos_user'])->name('acessos-uc-estudante');

    Route::get('uc-do-estudante/{uc_shortname}/estudantes/{nr_estudante}/acessos', [UnidadeCurricularController::class, 'obter_acessos_user_v2'])->name('acessos-uc-estudante-v2');//alternativa

//UNIDADES CURRICULARES de um dado estudante
    Route::get('estudantes/{email_estudante}/unidades-curriculares', [UnidadeCurricularController::class, 'obter_ucs_user'])->name('ucs-estudante');

//GRUPOS de um dado estudante
    Route::get('estudantes/{email_estudante}/grupos', [UnidadeCurricularController::class, 'obter_grupos_user'])->name('grupos-estudante');
//--------------------------------------------------------------------

});

