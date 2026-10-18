<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Modules\ControlECP\Entities\Material;
use Modules\ControlECP\Entities\Pqr;

class ControlECPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('controlecp::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('controlecp::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('controlecp::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('controlecp::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    /**
     * Muestra el inicio del rol Administrador.
     * Protegido: solo usuarios con rol controlecp.admin (o superadmin) pueden entrar,
     * aunque conozcan la URL directamente.
     *
     * Las consultas de PQR y Materiales se protegen con Schema::hasTable()
     * para que el panel cargue aunque esas tablas no existan en la base de datos local.
     */
    public function inicioAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Administrador de Control ECP.');
        }

        // Solicitudes (PQR) pendientes
        $solicitudesPendientes = Schema::hasTable((new Pqr)->getTable())
            ? Pqr::where('estado', 'Pendiente')->count()
            : 0;

        // Materiales críticos (agotados o en stock mínimo)
        if (Schema::hasTable((new Material)->getTable())) {
            $materialesCriticos = Material::where('estado', 'Agotado')
                ->orWhereColumn('cantidad', '<=', 'stock_minimo')
                ->get();
            $recursosCriticos = $materialesCriticos->count();
        } else {
            $materialesCriticos = collect();
            $recursosCriticos   = 0;
        }

        return view('controlecp::administrador.inicio', compact(
            'solicitudesPendientes',
            'recursosCriticos',
            'materialesCriticos'
        ));
    }


    /**
     * Muestra el inicio del rol Instructor.
     */
    public function inicioInstructor()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.instructor')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Instructor de Control ECP.');
        }

        return view('controlecp::instructor.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Instructor
     */
    private function verificarAccesoInstructor()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.instructor')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Instructor.');
        }
        return null;
    }

    public function cronogramaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.cronograma');
    }

    public function momentosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.momentos');
    }

    public function actividadesInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.actividades');
    }

    public function asistenciaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.asistencia');
    }

    public function asesoriasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.asesorias');
    }

    public function certificadosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.certificados');
    }

    public function poeInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.poe');
    }

    public function acompaniamientoInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.acompaniamiento');
    }

    /**
     * Muestra el inicio del rol Aprendiz.
     */
    public function inicioAprendiz()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.apprentice')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Aprendiz de Control ECP.');
        }

        return view('controlecp::aprendiz.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Aprendiz
     */
    private function verificarAccesoAprendiz()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.apprentice')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Aprendiz.');
        }
        return null;
    }

    public function cronogramaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.cronograma');
    }

    public function encuestaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.encuesta');
    }

    public function asesoriasAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.asesorias');
    }

    public function pqrAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.pqr');
    }
}