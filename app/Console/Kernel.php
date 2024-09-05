<?php

namespace App\Console;

use App\Console\Commands\ObterPeriodicamenteCursos;
use App\Http\Controllers\api\CursoController;
use App\Models\Curso;
use App\Models\UnidadeCurricular;
use App\Services\MimService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\ObterPeriodicamenteCursos::class,
        \App\Console\Commands\importar_ucs_agregadas_com_csv::class,
        \App\Console\Commands\Obter_todas_ucs::class,

    ];


    protected function schedule(Schedule $schedule): void //php artisan schedule:run
    {
        $schedule->command('app:obter-periodicamente-cursos')->weeklyOn(Schedule::FRIDAY, '04:00')->timezone('Europe/Lisbon');

        // Executa diariamente de setembro a fevereiro - período onde há mais alterações nas UCS
        $schedule->command('app:importar_ucs')->dailyAt('05:00')->timezone('Europe/Lisbon')
            ->when(function () {
                $currentMonth = now()->month;
                return $currentMonth >= 9 || $currentMonth <= 2;
            });

        // Executa mensalmente de março a agosto - período onde há menos alterações nas UCS
        $schedule->command('app:importar_ucs')->weeklyOn(Schedule::FRIDAY, '05:00')->timezone('Europe/Lisbon')
            ->when(function () {
                $currentMonth = now()->month;
                return $currentMonth >= 3 && $currentMonth <= 8;
            });

        // Executa diariamente de setembro a fevereiro - período onde há mais alterações nas UCS
        $schedule->command('app:obter_todas_ucs')->dailyAt('06:00')->timezone('Europe/Lisbon')
            ->when(function () {
                $currentMonth = now()->month;
                return $currentMonth >= 9 || $currentMonth <= 2;
            });

        // Executa mensalmente de março a agosto - período onde há menos alterações nas UCS
        $schedule->command('app:obter_todas_ucs')->weeklyOn(Schedule::FRIDAY, '06:00')->timezone('Europe/Lisbon')
            ->when(function () {
                $currentMonth = now()->month;
                return $currentMonth >= 3 && $currentMonth <= 8;
            });

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');

    }
}
