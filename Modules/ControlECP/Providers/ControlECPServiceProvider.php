<?php

namespace Modules\ControlECP\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ControlECPServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'ControlECP';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'controlecp';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\ControlECP\Console\RevisarProgramacionesPoeCommand::class,
                \Modules\ControlECP\Console\NotificarSesionesAcompanamientoCommand::class,
                \Modules\ControlECP\Console\NotificarAsesoriasCommand::class,
                \Modules\ControlECP\Console\NotificarCronogramasCommand::class,
            ]);
        }
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        parent::boot();

        // Registra las vistas con ambos namespaces (con y sin mayúsculas) para evitar fallos
        $path = module_path($this->name, 'Resources/views');
        
        $this->loadViewsFrom($path, 'controlecp');
        $this->loadViewsFrom($path, 'controlECP');

        // Programa la revisión diaria de POE en el Scheduler de Laravel
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('poe:revisar-programaciones')->daily();
            $schedule->command('controlecp:avisar-sesiones-acompanamiento')->dailyAt('08:00');
            $schedule->command('controlecp:avisar-asesorias-dos-dias')->dailyAt('08:00')->withoutOverlapping();
            $schedule->command('controlecp:avisar-cronogramas-dos-dias')->dailyAt('08:00')->withoutOverlapping();
        });
    }
}