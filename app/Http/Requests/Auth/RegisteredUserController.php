<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom_usu' => ['required', 'string', 'max:100'],
            'ape_usu' => ['required', 'string', 'max:100'],
            'cii_usu' => ['required', 'string', 'max:20', 'unique:usuarios,cii_usu'],
            'cor_usu' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:usuarios,cor_usu'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $rol = Role::where('nom_rol', 'ADMINISTRADOR')->first();

        $ultimoId = User::max('cod_usuarios') + 1;
        $codigo = 'USR-' . str_pad($ultimoId, 3, '0', STR_PAD_LEFT);

        $user = User::create([
            'ide_usu' => $codigo,
            'nom_usu' => $request->nom_usu,
            'ape_usu' => $request->ape_usu,
            'cii_usu' => $request->cii_usu,
            'cor_usu' => $request->cor_usu,
            'con_usu' => Hash::make($request->password),
            'cod_roles_usu' => $rol?->cod_roles ?? 1,
            'est_usu' => 'ACTIVO',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}