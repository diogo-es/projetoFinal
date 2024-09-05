<?php

namespace App\Console\Commands;

use App\Services\MimService;
use Illuminate\Console\Command;

class importar_ucs_agregadas_com_csv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:importar_ucs';

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
        $mimService->importar_ucs_agregadas_com_csv();
    }
}
