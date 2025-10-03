<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transacciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use OpenApi\Annotations as OA;


class TransaccionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/transacciones",
     *     tags={"Transacciones"},
     *     summary="Listar todas las transacciones",
     *     @OA\Response(
     *         response=200,
     *         description="Listado de transacciones",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Transacciones"))
     *     )
     * )
     */
    public function index()
    {
        return Transacciones::with('user')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/transacciones",
     *     tags={"Transacciones"},
     *     summary="Crear una transacción",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="monto", type="number", format="float", example=150.5),
     *             @OA\Property(property="fecha", type="string", format="date-time", example="2025-10-03 15:30:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transacción creada",
     *         @OA\JsonContent(ref="#/components/schemas/Transacciones")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/transacciones/{id}",
     *     tags={"Transacciones"},
     *     summary="Obtener una transacción por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la transacción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transacción encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Transacciones")
     *     ),
     *     @OA\Response(response=404, description="Transacción no encontrada")
     * )
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
