<?php

namespace Modules\Direccion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Direccion\Entities\Politica;
use Modules\SICA\Entities\Bloque;
use Modules\SICA\Entities\ProductiveUnit;

class DireccionController extends Controller
{
    /**
     * Página de Bienvenida / Landing Institucional del Módulo de Dirección.
     * Vista pública con información estratégica, métricas e inicio de sesión.
     */
    public function welcome()
    {
        $totalPoliticas = Politica::count();
        $activas = Politica::where('estado', 'Activa')->count();
        $enRevision = Politica::where('estado', 'En Revisión')->count();
        $inactivas = Politica::where('estado', 'Inactiva')->count();

        // Políticas destacadas para el landing
        $politicasDestacadas = Politica::orderBy('created_at', 'desc')->take(4)->get();

        return view('direccion::welcome', compact(
            'totalPoliticas',
            'activas',
            'enRevision',
            'inactivas',
            'politicasDestacadas'
        ));
    }

    /**
     * Dashboard / Tablero de Control Ejecutivo de Dirección.
     * Panel privado con métricas de gestión, desglose por tipo y accesos rápidos.
     */
    public function dashboard()
    {
        $totalPoliticas = Politica::count();
        $activas = Politica::where('estado', 'Activa')->count();
        $enRevision = Politica::where('estado', 'En Revisión')->count();
        $inactivas = Politica::where('estado', 'Inactiva')->count();

        // Desglose por tipo
        $porTipo = [
            'Estratégica' => Politica::where('tipo', 'Estratégica')->count(),
            'Calidad' => Politica::where('tipo', 'Calidad')->count(),
            'Seguridad' => Politica::where('tipo', 'Seguridad')->count(),
            'Operativa' => Politica::where('tipo', 'Operativa')->count(),
        ];

        // Últimas políticas creadas
        $ultimasPoliticas = Politica::orderBy('created_at', 'desc')->take(5)->get();

        return view('direccion::dashboard', compact(
            'totalPoliticas',
            'activas',
            'enRevision',
            'inactivas',
            'porTipo',
            'ultimasPoliticas'
        ));
    }
}
