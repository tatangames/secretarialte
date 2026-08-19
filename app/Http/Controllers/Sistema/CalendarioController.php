<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CalendarioController extends Controller
{

    public function indexCalendario()
    {


        return view('backend.admin.calendario.vistacalendario');
    }

    public function registrosPorDia()
    {
        $reservas = Reserva::selectRaw('fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->get();

        $eventosReservas = $reservas->map(function ($registro) {
            return [
                'title' => $registro->total . ' Reserva' . ($registro->total > 1 ? 's' : ''),
                'start' => $registro->fecha,
            ];
        });

        return response()->json($eventosReservas);
    }

    public function reservasPorFecha(Request $request)
    {
        $regla = ['fecha' => 'required|date'];
        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) return ['success' => 0];

        $lista = Reserva::with('lugar')
            ->where('fecha', $request->fecha)
            ->orderBy('hora_inicio', 'ASC')
            ->get()
            ->map(function ($r) {
                return [
                    'id'          => $r->id,
                    'nombre'      => $r->nombre,
                    'telefono'    => $r->telefono,
                    'fecha'       => $r->fecha,
                    'hora_inicio' => date('h:i A', strtotime($r->hora_inicio)),
                    'hora_fin'    => date('h:i A', strtotime($r->hora_fin)),
                    'descripcion' => $r->descripcion,
                    'lugar'       => $r->lugar?->nombre ?? '—',
                    'id_lugares'  => $r->id_lugares,
                ];
            });

        return response()->json(['success' => 1, 'lista' => $lista]);
    }

    public function informacion(Request $request)
    {
        $regla = ['id' => 'required'];
        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) return ['success' => 0];

        if ($r = Reserva::with('lugar')->find($request->id)) {
            return ['success' => 1, 'info' => [
                'id'          => $r->id,
                'nombre'      => $r->nombre,
                'telefono'    => $r->telefono,
                'fecha'       => $r->fecha,
                'hora_inicio' => $r->hora_inicio,
                'hora_fin'    => $r->hora_fin,
                'descripcion' => $r->descripcion,
                'id_lugares'  => $r->id_lugares,
                'lugar'       => $r->lugar?->nombre ?? '—',
            ]];
        }

        return ['success' => 2];
    }

    public function editar(Request $request)
    {
        $regla = [
            'id'          => 'required',
            'id_lugares'  => 'required',
            'nombre'      => 'required',
            'fecha'       => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin'    => 'required',
        ];
        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) return ['success' => 0];

        // Verificar choque excluyendo la reserva actual
        $choque = $this->existeChoque(
            $request->id_lugares,
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin,
            $request->id  // excluir
        );

        if ($choque) {
            return ['success' => 3, 'mensaje' => 'El horario choca con una reserva existente: ' . $choque];
        }

        if ($r = Reserva::find($request->id)) {
            $r->update([
                'id_lugares'  => $request->id_lugares,
                'nombre'      => $request->nombre,
                'telefono'    => $request->telefono,
                'fecha'       => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin'    => $request->hora_fin,
                'descripcion' => $request->descripcion,
            ]);
            return ['success' => 1];
        }

        return ['success' => 2];
    }


    public function eliminar(Request $request)
    {
        $regla = ['id' => 'required'];
        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) return ['success' => 0];

        if ($r = Reserva::find($request->id)) {
            $r->delete();
            return ['success' => 1];
        }

        return ['success' => 2];
    }


    public function nuevo(Request $request)
    {
        $regla = [
            'id_lugares'  => 'required',
            'nombre'      => 'required',
            'fecha'       => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin'    => 'required',
        ];
        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) return ['success' => 0];

        // Verificar choque de horario
        $choque = $this->existeChoque(
            $request->id_lugares,
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin
        );

        if ($choque) {
            return ['success' => 3, 'mensaje' => 'El horario choca con una reserva existente: ' . $choque];
        }

        $dato = new Reserva();
        $dato->id_lugares  = $request->id_lugares;
        $dato->nombre      = $request->nombre;
        $dato->telefono    = $request->telefono;
        $dato->fecha       = $request->fecha;
        $dato->hora_inicio = $request->hora_inicio;
        $dato->hora_fin    = $request->hora_fin;
        $dato->descripcion = $request->descripcion;

        if ($dato->save()) {
            return ['success' => 1];
        }

        return ['success' => 2];
    }

// ── Método privado reutilizable ───────────────────────────────────────
    private function existeChoque($id_lugares, $fecha, $hora_inicio, $hora_fin, $excluir_id = null)
    {
        $query = Reserva::where('id_lugares', $id_lugares)
            ->where('fecha', $fecha)
            ->where(function ($q) use ($hora_inicio, $hora_fin) {
                // Detecta cualquier solapamiento de intervalos
                $q->where(function ($q2) use ($hora_inicio, $hora_fin) {
                    $q2->where('hora_inicio', '<', $hora_fin)
                        ->where('hora_fin',    '>', $hora_inicio);
                });
            });

        if ($excluir_id) {
            $query->where('id', '!=', $excluir_id);
        }

        $reserva = $query->first();

        if ($reserva) {
            $inicio = date('h:i A', strtotime($reserva->hora_inicio));
            $fin    = date('h:i A', strtotime($reserva->hora_fin));
            return "{$reserva->nombre} ({$inicio} — {$fin})";
        }

        return null;
    }






}
