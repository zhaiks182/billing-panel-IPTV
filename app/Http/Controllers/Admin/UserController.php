<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Xui\TrialActivator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $users = User::withCount(['orders', 'lines'])
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
