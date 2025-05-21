<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Mostrar info del usuario autenticado
    public function show(Request $request)
    {
        $user = $request->user();  // Obtener usuario autenticado
        return response()->json($user);
    }

    // Actualizar datos del usuario (nombre, email, teléfono)
    public function update(Request $request)
    {
        $user = $request->user();

        // Validar datos entrantes
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Actualizar datos permitidos
        $user->fill($request->only(['name', 'email', 'phone']));
        $user->save();

        return response()->json($user);
    }

    // Subir y actualizar avatar del usuario
    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Eliminar avatar anterior si existe
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

        // Guardar nuevo avatar
        $path = $request->file('avatar')->store('avatars');

        $user->avatar = $path;
        $user->save();

        return response()->json(['avatar_url' => Storage::url($path)]);
    }
}
