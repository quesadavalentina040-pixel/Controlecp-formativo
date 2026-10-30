<?php

namespace Modules\ControlECP\Console;

use Illuminate\Console\Command;
use Modules\ControlECP\Services\PoeService;

class RevisarProgramacionesPoeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poe:revisar-programaciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa programaciones de POE vencidas (atrasadas) y próximas a vencer (3 días) y genera avisos automáticos.';

    protected PoeService $poeService;

    public function __construct(PoeService $poeService)
    {
        parent::__construct();
        $this->poeService = $poeService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando revisión diaria de programaciones de POE...');

        $actualizadas = $this->poeService->actualizarEstadosAtrasadas();

        $this->info("Revisión completada exitosamente. Programaciones actualizadas a Atrasada: {$actualizadas}.");

        return Command::SUCCESS;
    }
}
