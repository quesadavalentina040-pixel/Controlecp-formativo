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
     * Boot the application events.
     */
    public function boot(): void
    {
        parent::boot();

        // Registra las vistas con ambos namespaces (con y sin mayúsculas) para evitar fallos
        $path = module_path($this->name, 'Resources/views');
        
        $this->loadViewsFrom($path, 'controlecp');
        $this->loadViewsFrom($path, 'controlECP');
    }
}