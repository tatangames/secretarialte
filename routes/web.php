<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Sistema\LoginController;
use App\Http\Controllers\Sistema\ControlController;
use App\Http\Controllers\Sistema\RolesController;
use App\Http\Controllers\Sistema\PerfilController;
use App\Http\Controllers\Sistema\PermisoController;
use App\Http\Controllers\Sistema\ConfiguracionController;
use App\Http\Controllers\Sistema\CalendarioController;
use App\Http\Controllers\Sistema\ReportesController;





Route::get('/', [LoginController::class,'vistaLoginForm'])->name('login.admin');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- REDIRECCIONAMIENTO A OTROS SISTEMAS ---
Route::get('/redireccionamiento/sistemas', [LoginController::class,'vistaRedireccionOtrosSistemas'])->name('admin.redireccionamiento.index');


Route::middleware('auth:admin')->group(function () {

    // --- ROLES ----.
    Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
    Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
    Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
    Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
    Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
    Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
    Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
    Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
    Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

    // --- PERMISOS ---
    Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
    Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
    Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
    Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
    Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
    Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
    Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
    Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);

    // --- PERFIL ---
    Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
    Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

    Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');

    // --- CONTROL WEB ---
    Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');


    // --- LUGARES ---
    Route::get('/admin/lugares/index', [ConfiguracionController::class,'indexLugares'])->name('admin.lugares.index');
    Route::get('/admin/lugares/tabla/index', [ConfiguracionController::class,'tablaLugares']);
    Route::post('/admin/lugares/nuevo', [ConfiguracionController::class, 'nuevaLugares']);
    Route::post('/admin/lugares/informacion', [ConfiguracionController::class, 'informacionLugares']);
    Route::post('/admin/lugares/editar', [ConfiguracionController::class, 'editarLugares']);

    // --- CALENDARIO ---
    Route::get('/admin/calendario/index', [CalendarioController::class,'indexCalendario'])->name('admin.calendario.index');
    Route::get('/admin/calendario/informacion', [CalendarioController::class, 'registrosPorDia']);

    // --- RESERVA ---
    Route::post('/admin/calendario/reservas-por-dia',  [CalendarioController::class, 'reservasPorFecha']);
    Route::post('/admin/calendario/informacion',       [CalendarioController::class, 'informacion']);
    Route::post('/admin/calendario/editar',            [CalendarioController::class, 'editar']);
    Route::post('/admin/calendario/eliminar',          [CalendarioController::class, 'eliminar']);
    Route::post('/admin/calendario/nuevo', [CalendarioController::class, 'nuevo']);

    // --- REPORTES ---
    Route::get('/admin/reportes/index', [ReportesController::class,'indexReportes'])->name('admin.reportes.index');
    Route::get('/admin/reportes/reserva/pdf', [ReportesController::class, 'reportePdfPorFecha']);







}); // end auth





