<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Xui\TrialActivator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $users = User::where('role', 'customer')
            ->withCount(['orders', 'lines'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(User $user)
    {
        abort_if($user->isAdmin(), 404);

        $orders = $user->orders()->with('package')->latest()->get();
        $lines = $user->lines()->with('order.package')->latest('expires_at')->get();

        return view('admin.users.show', compact('user', 'orders', 'lines'));
    }

    public function edit(User $user)
    {
        abort_if($user->isAdmin(), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_country_code' => ['nullable', 'string', 'max:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'state' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'postal_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'country' => ['nullable', 'string', Rule::in(collect(config('countries'))->pluck('name'))],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)->with('status', 'Datos del cliente actualizados.');
    }

    public function admins(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $admins = User::where('role', 'admin')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.admins', compact('admins', 'search'));
    }

    public function create(Request $request)
    {
        $defaultRole = $request->query('role') === 'admin' ? 'admin' : 'customer';

        return view('admin.users.create', compact('defaultRole'));
    }

    public function store(Request $request)
    {
        $isAdmin = $request->input('role') === 'admin';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [Rule::requiredIf(! $isAdmin), 'nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => [Rule::requiredIf($isAdmin), 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        // Un admin se identifica por "username" (ver App\Http\Requests\Admin\LoginRequest), no por
        // correo — la columna `email` sigue siendo obligatoria/unica a nivel de esquema, así que se
        // rellena con un valor interno no entregable que nunca se usa para enviar nada.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $isAdmin ? "{$validated['username']}@admin.local" : $validated['email'],
            'username' => $isAdmin ? $validated['username'] : null,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $user->markEmailAsVerified();

        $label = $isAdmin ? $user->username : $user->email;

        return redirect()->route($isAdmin ? 'admin.users.admins' : 'admin.users.index')
            ->with('status', "Usuario {$label} creado correctamente.");
    }

    public function toggleBlock(User $user)
    {
        abort_if($user->isAdmin(), 403, 'No se puede bloquear a un administrador.');

        $user->update(['is_blocked' => ! $user->is_blocked]);

        $status = $user->is_blocked ? 'bloqueado' : 'desbloqueado';

        return back()->with('status', "Usuario {$user->email} {$status} correctamente.");
    }

    public function verify(User $user, TrialActivator $trialActivator)
    {
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $trialActivator->activatePendingFor($user);

        return back()->with('status', "Correo de {$user->email} verificado manualmente.");
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'No puedes eliminar tu propia cuenta.');

        $label = $user->isAdmin() ? $user->username : $user->email;
        $user->delete();

        return back()->with('status', "Usuario {$label} eliminado junto con todos sus pedidos y líneas.");
    }
}
