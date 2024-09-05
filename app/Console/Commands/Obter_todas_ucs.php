<?php

namespace App\Console\Commands;

use App\Services\MimService;
use Illuminate\Console\Command;

class obter_todas_ucs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:obter_todas_ucs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mimService = new MimService();
        $mimService->obter_ucs_todos_cursos();
    }
}
