<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TurnstileSetting;
use App\Models\User;
use App\Rules\ValidTurnstile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $turnstileSiteKey = TurnstileSetting::current()->isActive()
            ? TurnstileSetting::current()->site_key
            : null;

        return view('auth.register', compact('turnstileSiteKey'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_country_code' => ['required', 'string', 'max:6'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'state' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
            'postal_code' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'country' => ['required', 'string', Rule::in(collect(config('countries'))->pluck('name'))],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'cf-turnstile-response' => [new ValidTurnstile],
        ], [
            'city.regex' => 'La ciudad solo puede contener letras.',
            'state.regex' => 'El estado/provincia solo puede contener letras.',
            'postal_code.regex' => 'El código postal solo puede contener números.',
            'country.in' => 'Selecciona un país válido de la lista.',
        ]);

        $user = User::create([
            'name' => trim($request->first_name.' '.$request->last_name),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_country_code' => $request->phone_country_code,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
