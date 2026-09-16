<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     */
    public function inicioAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Administrador de Control ECP.');
        }

        return view('controlecp::administrador.inicio');
    }

    /**
     * Muestra la vista de gestión de Usuarios del Administrador (Integración vía API).
     */
    public function usuariosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de usuarios de Control ECP.');
        }

        return view('controlecp::administrador.usuario');
    }

    /**
     * Muestra la vista de consulta de Fichas del Administrador (Integración vía API).
     */
    public function fichasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de fichas de Control ECP.');
        }

        return view('controlecp::administrador.fichas');
    }

    /**
     * Muestra la vista de consulta y estado de Asistencia del Administrador (Flujo dependiente del Rol Instructor).
     */
    public function asistenciaAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de asistencia de Control ECP.');
        }

        return view('controlecp::administrador.asistencia');
    }

    /**
     * Muestra la vista de consulta y gestión de Asesorías del Administrador.
     */
    public function asesoriasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de asesorías de Control ECP.');
        }

        return view('controlecp::administrador.asesorias');
    }

    /**
     * Muestra la vista de consulta y emisión de Certificados del Administrador.
     */
    public function certificadosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de certificados de Control ECP.');
        }

        return view('controlecp::administrador.certificados');
    }


        /**
     * Muestra la vista de Historias dentro del Repositorio del Administrador.
     */
    public function repositorioHistoria()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.historia');
    }

    /**
     * Muestra la vista de Encuestas dentro del Repositorio del Administrador.
     */
    public function repositorioEncuesta()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.encuesta');
    }

    /**
     * Muestra la vista de Tabulación dentro del Repositorio del Administrador.
     */
    public function repositorioTabulacion()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.tabulacion');
    }

    /**
     * Muestra la vista de POE dentro del Repositorio del Administrador.
     */
    public function repositorioPoe()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.poe');
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
}
