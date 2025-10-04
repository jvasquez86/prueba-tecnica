<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Listar todos los usuarios",
     *     @OA\Response(
     *         response=200,
     *         description="Listado de usuarios",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function index()
    {
        return User::all();
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Crear un usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Juan Perez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@mail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="saldo_inicial", type="number", format="float", example=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'saldo_inicial' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            return User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'saldo_inicial' => $request->saldo_inicial,
            ]);

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'user' => $user
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // En caso de que falle por duplicado
            return response()->json([
                'message' => 'El email ya está registrado.'
            ], 409); // 409 Conflict
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Obtener un usuario por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Actualizar un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Juan Perez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@mail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="saldo_inicial", type="number", format="float", example=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'string|max:255',
            'email'         => 'string|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:6',
            'saldo_inicial' => 'numeric'
        ]);

        $user->update([
            'name'          => $request->name ?? $user->name,
            'email'         => $request->email ?? $user->email,
            'saldo_inicial' => $request->saldo_inicial ?? $user->saldo_inicial,
            'password'      => $request->password ? Hash::make($request->password) : $user->password
        ]);

        return $user;
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Eliminar un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Usuario eliminado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
