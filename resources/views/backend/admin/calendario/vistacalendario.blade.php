@extends('adminlte::page')

@section('title', 'Calendario')

@section('content_header')
    <h1>Calendario de Reservas</h1>
@stop

@section('plugins.Sweetalert2', true)
@include('backend.urlglobal')

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js"></script>

    <li class="nav-item dropdown">
        <a href="#" class="nav-link" data-toggle="dropdown">
            <i class="fas fa-cogs"></i>
            <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->nombre }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.perfil') }}" class="dropdown-item">
                <i class="fas fa-user mr-2"></i> Editar Perfil
            </a>
        </div>
    </li>
    <li class="nav-item">
        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="nav-link btn btn-link border-0 bg-transparent">
                <i class="fas fa-sign-out-alt"></i>
                <span class="d-none d-md-inline">Cerrar Sesión</span>
            </button>
        </form>
    </li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    {{-- ══════════════ MODAL: RESERVAS DEL DÍA ══════════════ --}}
    <div class="modal fade" id="modalReservasDia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-day mr-2"></i>
                        Reservas del <span id="fechaModalTitulo"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="cuerpoReservasDia">
                    <p class="text-center text-muted">Cargando...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="abrirNuevo()">
                        <i class="fas fa-plus mr-1"></i> Nueva Reserva
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ MODAL: NUEVA RESERVA ══════════════ --}}
    <div class="modal fade" id="modalNuevo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Reserva — <span id="fechaNuevoTitulo"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Lugar <span class="text-danger">*</span></label>
                        <select id="id_lugares-nuevo" class="form-control select2-lugares-nuevo">
                            <option value="">-- Seleccione --</option>
                            @foreach(\App\Models\Lugar::orderBy('nombre')->get() as $lugar)
                                <option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" autocomplete="off" maxlength="100" id="nombre-nuevo" class="form-control" placeholder="Nombre del solicitante">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono-nuevo" maxlength="50" class="form-control" placeholder="Teléfono">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora inicio <span class="text-danger">*</span></label>
                                <input type="time" id="hora_inicio-nuevo" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora fin <span class="text-danger">*</span></label>
                                <input type="time" id="hora_fin-nuevo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="descripcion-nuevo" autocomplete="off" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Alerta de choque --}}
                    <div id="alertaChoque-nuevo" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span id="mensajeChoque-nuevo"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarNuevo()">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ MODAL: EDITAR RESERVA ══════════════ --}}
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id-editar">

                    <div class="form-group">
                        <label>Lugar <span class="text-danger">*</span></label>
                        <select id="id_lugares-editar" class="form-control select2-lugares-editar">
                            <option value="">-- Seleccione --</option>
                            @foreach(\App\Models\Lugar::orderBy('nombre')->get() as $lugar)
                                <option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="nombre-editar" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono-editar" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fecha-editar" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora inicio <span class="text-danger">*</span></label>
                                <input type="time" id="hora_inicio-editar" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Hora fin <span class="text-danger">*</span></label>
                                <input type="time" id="hora_fin-editar" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="descripcion-editar" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Alerta de choque --}}
                    <div id="alertaChoque-editar" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span id="mensajeChoque-editar"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="guardarEdicion()">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>

    <script>
        // ── Select2 ───────────────────────────────────────────────────
        $(document).ready(function () {
            $('.select2-lugares-nuevo').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNuevo')
            });
            $('.select2-lugares-editar').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditar')
            });
        });

        // ── Calendario ────────────────────────────────────────────────
        var fechaActual = '';

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'dayGridMonth,timeGridWeek,listWeek'
                },
                events: urlAdmin + '/admin/calendario/informacion',
                dateClick: function (info) {
                    abrirReservasDia(info.dateStr);
                }
            });

            calendar.render();
        });

        // ── Formatear fecha legible ───────────────────────────────────
        function formatearFecha(fecha) {
            var partes = fecha.split('-');
            var meses  = ['enero','febrero','marzo','abril','mayo','junio',
                'julio','agosto','septiembre','octubre','noviembre','diciembre'];
            return parseInt(partes[2]) + ' de ' + meses[parseInt(partes[1]) - 1] + ' de ' + partes[0];
        }

        // ── Abrir modal reservas del día ──────────────────────────────
        function abrirReservasDia(fecha) {
            fechaActual = fecha;
            document.getElementById('fechaModalTitulo').textContent = formatearFecha(fecha);
            document.getElementById('cuerpoReservasDia').innerHTML  =
                '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

            $('#modalReservasDia').modal('show');
            cargarReservasDia(fecha);
        }

        function cargarReservasDia(fecha) {
            var formData = new FormData();
            formData.append('fecha', fecha);

            axios.post(urlAdmin + '/admin/calendario/reservas-por-dia', formData)
                .then(function (response) {
                    if (response.data.success === 1) {
                        renderizarReservas(response.data.lista);
                    } else {
                        document.getElementById('cuerpoReservasDia').innerHTML =
                            '<p class="text-center text-muted">No hay reservas para este día.</p>';
                    }
                })
                .catch(function () {
                    document.getElementById('cuerpoReservasDia').innerHTML =
                        '<p class="text-center text-danger">Error al cargar las reservas.</p>';
                });
        }

        // ── Renderizar tarjetas ───────────────────────────────────────
        function renderizarReservas(lista) {
            if (lista.length === 0) {
                document.getElementById('cuerpoReservasDia').innerHTML =
                    '<p class="text-center text-muted">No hay reservas para este día.</p>';
                return;
            }

            var html = '';
            lista.forEach(function (r) {
                html += `
                <div class="card mb-2 border-left-primary shadow-sm" id="card-reserva-${r.id}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 font-weight-bold">
                                    <i class="fas fa-map-marker-alt text-primary mr-1"></i>
                                    ${r.lugar}
                                </h6>
                                <p class="mb-1">
                                    <i class="fas fa-user text-secondary mr-1"></i> ${r.nombre}
                                    ${r.telefono ? `<span class="ml-2"><i class="fas fa-phone text-secondary mr-1"></i>${r.telefono}</span>` : ''}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock text-success mr-1"></i>
                                    <strong>${r.hora_inicio}</strong>
                                    <span class="mx-1">—</span>
                                    <strong>${r.hora_fin}</strong>
                                </p>
                                ${r.descripcion
                    ? `<p class="mb-0 text-muted small mt-1"><i class="fas fa-info-circle mr-1"></i>${r.descripcion}</p>`
                    : ''}
                            </div>
                            <div class="d-flex flex-column" style="gap:6px">
                                <button class="btn btn-warning btn-sm" onclick="abrirEditar(${r.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="confirmarEliminar(${r.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            document.getElementById('cuerpoReservasDia').innerHTML = html;
        }

        // ── Abrir modal NUEVO ─────────────────────────────────────────
        function abrirNuevo() {
            // Limpiar campos
            $('#id_lugares-nuevo').val('').trigger('change');
            $('#nombre-nuevo').val('');
            $('#telefono-nuevo').val('');
            $('#hora_inicio-nuevo').val('');
            $('#hora_fin-nuevo').val('');
            $('#descripcion-nuevo').val('');
            ocultarAlerta('nuevo');

            document.getElementById('fechaNuevoTitulo').textContent = formatearFecha(fechaActual);
            $('#modalNuevo').modal('show');
        }

        // ── Guardar nueva reserva ─────────────────────────────────────
        function guardarNuevo() {
            var id_lugares  = $('#id_lugares-nuevo').val();
            var nombre      = $('#nombre-nuevo').val().trim();
            var telefono    = $('#telefono-nuevo').val().trim();
            var hora_inicio = $('#hora_inicio-nuevo').val();
            var hora_fin    = $('#hora_fin-nuevo').val();
            var descripcion = $('#descripcion-nuevo').val().trim();

            ocultarAlerta('nuevo');

            if (!id_lugares)  { toastr.error('Lugar es requerido'); return; }
            if (!nombre)      { toastr.error('Nombre es requerido'); return; }
            if (!hora_inicio) { toastr.error('Hora inicio es requerida'); return; }
            if (!hora_fin)    { toastr.error('Hora fin es requerida'); return; }
            if (hora_fin <= hora_inicio) { toastr.error('La hora fin debe ser mayor a la hora inicio'); return; }

            openLoading();
            var formData = new FormData();
            formData.append('id_lugares',  id_lugares);
            formData.append('nombre',      nombre);
            formData.append('telefono',    telefono);
            formData.append('fecha',       fechaActual);
            formData.append('hora_inicio', hora_inicio);
            formData.append('hora_fin',    hora_fin);
            formData.append('descripcion', descripcion);

            axios.post(urlAdmin + '/admin/calendario/nuevo', formData)
                .then(function (response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Reserva registrada correctamente');
                        $('#modalNuevo').modal('hide');
                        cargarReservasDia(fechaActual); // refrescar lista
                        calendar.refetchEvents(); // <-- recarga el calendario
                    } else if (response.data.success === 3) {
                        mostrarAlerta('nuevo', response.data.mensaje);
                    } else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al registrar'); });
        }

        // ── Abrir modal EDITAR ────────────────────────────────────────
        function abrirEditar(id) {
            ocultarAlerta('editar');

            axios.post(urlAdmin + '/admin/calendario/informacion', { id: id })
                .then(function (response) {
                    if (response.data.success === 1) {
                        var info = response.data.info;
                        $('#id-editar').val(info.id);
                        $('#id_lugares-editar').val(info.id_lugares).trigger('change');
                        $('#nombre-editar').val(info.nombre);
                        $('#telefono-editar').val(info.telefono ?? '');
                        $('#fecha-editar').val(info.fecha);
                        $('#hora_inicio-editar').val(info.hora_inicio);
                        $('#hora_fin-editar').val(info.hora_fin);
                        $('#descripcion-editar').val(info.descripcion ?? '');
                        $('#modalEditar').modal('show');
                    } else {
                        toastr.error('No se encontró la reserva');
                    }
                })
                .catch(function () { toastr.error('Error al cargar la reserva'); });
        }

        // ── Guardar edición ───────────────────────────────────────────
        function guardarEdicion() {
            var id          = $('#id-editar').val();
            var id_lugares  = $('#id_lugares-editar').val();
            var nombre      = $('#nombre-editar').val().trim();
            var telefono    = $('#telefono-editar').val().trim();
            var fecha       = $('#fecha-editar').val();
            var hora_inicio = $('#hora_inicio-editar').val();
            var hora_fin    = $('#hora_fin-editar').val();
            var descripcion = $('#descripcion-editar').val().trim();

            ocultarAlerta('editar');

            if (!id_lugares)  { toastr.error('Lugar es requerido'); return; }
            if (!nombre)      { toastr.error('Nombre es requerido'); return; }
            if (!fecha)       { toastr.error('Fecha es requerida'); return; }
            if (!hora_inicio) { toastr.error('Hora inicio es requerida'); return; }
            if (!hora_fin)    { toastr.error('Hora fin es requerida'); return; }
            if (hora_fin <= hora_inicio) { toastr.error('La hora fin debe ser mayor a la hora inicio'); return; }

            openLoading();
            var formData = new FormData();
            formData.append('id',          id);
            formData.append('id_lugares',  id_lugares);
            formData.append('nombre',      nombre);
            formData.append('telefono',    telefono);
            formData.append('fecha',       fecha);
            formData.append('hora_inicio', hora_inicio);
            formData.append('hora_fin',    hora_fin);
            formData.append('descripcion', descripcion);

            axios.post(urlAdmin + '/admin/calendario/editar', formData)
                .then(function (response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Reserva actualizada');
                        $('#modalEditar').modal('hide');
                        cargarReservasDia(fechaActual);
                        calendar.refetchEvents(); // <-- recarga el calendario
                    } else if (response.data.success === 3) {
                        mostrarAlerta('editar', response.data.mensaje);
                    } else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al actualizar'); });
        }

        // ── Eliminar ──────────────────────────────────────────────────
        function confirmarEliminar(id) {

            Swal.fire({
                title: '¿Eliminar reserva?',
                text: 'Esta acción no se puede deshacer.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    eliminarReserva(id)
                }
            });
        }

        function eliminarReserva(id){
            openLoading();
            axios.post(urlAdmin + '/admin/calendario/eliminar', { id: id })
                .then(function (response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Reserva eliminada');
                        // Quitar tarjeta sin cerrar el modal
                        var card = document.getElementById('card-reserva-' + id);
                        if (card) card.remove();

                        // Si no quedan tarjetas, mostrar mensaje vacío
                        var cuerpo = document.getElementById('cuerpoReservasDia');
                        if (cuerpo && cuerpo.querySelectorAll('.card').length === 0) {
                            cuerpo.innerHTML = '<p class="text-center text-muted">No hay reservas para este día.</p>';
                        }

                        calendar.refetchEvents(); // <-- recarga el calendario
                    } else {
                        toastr.error('Error al eliminar');
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al eliminar'); });
        }

        // ── Helpers alerta choque ─────────────────────────────────────
        function mostrarAlerta(tipo, mensaje) {
            document.getElementById('mensajeChoque-' + tipo).textContent = mensaje;
            document.getElementById('alertaChoque-'  + tipo).classList.remove('d-none');
        }

        function ocultarAlerta(tipo) {
            document.getElementById('alertaChoque-' + tipo).classList.add('d-none');
            document.getElementById('mensajeChoque-' + tipo).textContent = '';
        }
    </script>
@endsection
