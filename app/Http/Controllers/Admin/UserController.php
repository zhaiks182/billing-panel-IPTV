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

        $users = User::withCount(['orders', 'lines'])
            ->with(['orders' => fn ($query) => $query->with('package:id,name')->latest(), 'lines' => fn ($query) => $query->latest()])
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

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $user->markEmailAsVerified();

        return redirect()->route('admin.users.index')->with('status', "Usuario {$user->email} creado correctamente.");
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

        $email = $user->email;
        $user->delete();

        return back()->with('status', "Usuario {$email} eliminado junto con todos sus pedidos y líneas.");
    }
}
