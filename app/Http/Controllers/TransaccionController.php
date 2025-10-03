<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transacciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TransaccionController extends Controller
{
    /**
     * Listar todas las transacciones
     */
    public function index()
    {
        return Transacciones::with('user')->get();
    }

    /**
     * Guardar una nueva transacción
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'monto'   => 'required|numeric|min:0.01',
            'fecha'   => 'required|date_format:Y-m-d H:i:s',
        ]);

        $userId = $request->user_id;
        $monto = $request->monto;
        $fecha = $request->fecha;

        // --- 1. Limite diario ---
        $fechaInicio = date('Y-m-d 00:00:00', strtotime($fecha));
        $fechaFin    = date('Y-m-d 23:59:59', strtotime($fecha));

        $totalHoy = Transacciones::where('user_id', $userId)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');

        if (($totalHoy + $monto) > 5000) {
            return response()->json([
                'message' => 'Límite diario de transferencia alcanzado (5.000 USD)'
            ], 400);
        }

        // --- 2. Evitar transacción duplicada ---
        $duplicada = Transacciones::where('user_id', $userId)
            ->where('monto', $monto)
            ->where('fecha', $fecha)
            ->first();

        if ($duplicada) {
            return response()->json([
                'message' => 'Transacción duplicada detectada'
            ], 400);
        }

        // --- Crear transacción ---
        $transaccion = Transacciones::create([
            'user_id' => $userId,
            'monto'   => $monto,
            'fecha'   => $fecha,
        ]);

        return response()->json([
            'message' => 'Transacción registrada con éxito',
            'data'    => $transaccion
        ], 201);
    }

    /**
     * Mostrar una transacción
     */
    public function show(Transacciones $transaccion)
    {
        return $transaccion->load('user');
    }

    /**
     * Eliminar una transacción
     */
    public function destroy(Transacciones $transaccion)
    {
        $transaccion->delete();

        return response()->json([
            'message' => 'Transacción eliminada correctamente'
        ]);
    }


    public function exportCsv()
    {
        $transacciones = Transacciones::with('user')->get();

        $filename = "transacciones_" . date('Ymd_His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transacciones) {
            $file = fopen('php://output', 'w');

            // Cabecera CSV
            fputcsv($file, ['ID', 'Usuario', 'Email', 'Monto', 'Fecha', 'Creado', 'Actualizado']);

            foreach ($transacciones as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->user?->name ?? 'Desconocido',
                    $t->user?->email ?? 'Desconocido',
                    $t->monto,
                    $t->fecha,
                    $t->created_at,
                    $t->updated_at,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function resumenPorUsuario()
    {
        $resumen = DB::table('transacciones')
            ->join('users', 'transacciones.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name as usuario',
                'users.email',
                DB::raw('SUM(transacciones.monto) as total_transferido'),
                DB::raw('AVG(transacciones.monto) as promedio_monto')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->get();

        return response()->json($resumen);
    }
}
