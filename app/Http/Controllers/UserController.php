<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string|min:6',
            'saldo_inicial' => 'required|numeric'
        ]);

        return User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'saldo_inicial' => $request->saldo_inicial,
        ]);
    }

    public function show(User $user)
    {
        return $user;
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'string|max:255',
            'email'         => 'string|email|unique:users,email,'.$user->id,
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

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
