<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Departamentos;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\InformacionGeneral;
use App\Models\Materiales;
use App\Models\Reserva;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\SalidasDetalleEntregas;
use App\Models\TipoProyecto;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportesController extends Controller
{

    public function indexReportes()
    {

        return view('backend.admin.reportes.vistareportegenerales');
    }



    public function reportePdfPorFecha(Request $request)
    {
        $desde    = $request->input('fecha');
        $idLugar  = $request->input('id_lugar');
        $desdeFormat = date("d-m-Y", strtotime($desde));

        $query = Reserva::with('lugar')->where('fecha', $desde);

        if ($idLugar) {
            $query->where('id_lugares', $idLugar);
        }

        $reservas = $query->orderBy('hora_inicio', 'ASC')->get();

        $nombreLugar = $idLugar
            ? (\App\Models\Lugar::find($idLugar)->nombre ?? 'Sin lugar')
            : 'Todos';

        $mostrarLugar = !$idLugar; // true = Todos, false = lugar específico

        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Reporte de Reservas');

        $tabla = "
<h3 style='text-align:center;'>Reporte de Reservas</h3>
<p style='text-align:center;'>Fecha: {$desdeFormat} &nbsp;|&nbsp; Lugar: {$nombreLugar}</p>";

        $tabla .= "
<table width='100%' border='1' cellspacing='0' cellpadding='5' style='border-collapse: collapse; font-size: 11px;'>
    <thead>
        <tr style='background-color:#f2f2f2;'>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            " . ($mostrarLugar ? "<th>Lugar</th>" : "") . "
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>";

        foreach ($reservas as $dato) {
            $fecha       = date('d-m-Y', strtotime($dato->fecha));
            $lugar       = $dato->lugar ? $dato->lugar->nombre : 'Sin lugar';
            $horaInicio  = date('h:i A', strtotime($dato->hora_inicio));
            $horaFin     = date('h:i A', strtotime($dato->hora_fin));
            $descripcion = $dato->descripcion ?? '';

            $tabla .= "
    <tr>
        <td>{$fecha}</td>
        <td>{$dato->nombre}</td>
        <td>{$dato->telefono}</td>
        " . ($mostrarLugar ? "<td>{$lugar}</td>" : "") . "
        <td>{$horaInicio}</td>
        <td>{$horaFin}</td>
        <td>{$descripcion}</td>
    </tr>";
        }

        $tabla .= "
    </tbody>
</table>";

        $mpdf->WriteHTML($tabla);
        $mpdf->Output('Reporte_Reservas.pdf', 'I');
    }




}
