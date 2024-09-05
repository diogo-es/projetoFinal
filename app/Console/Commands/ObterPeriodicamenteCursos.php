<?php

namespace App\Console\Commands;

use App\Services\MimService;
use Illuminate\Console\Command;


class ObterPeriodicamenteCursos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:obter-periodicamente-cursos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtem os cursos do moodle do IPL periodicamente.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mimService = new MimService();
        $mimService->obter_periodicamente_cursos();
    }


}
