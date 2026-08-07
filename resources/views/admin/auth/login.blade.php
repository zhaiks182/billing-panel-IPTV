<x-admin-guest-layout>
    @if ($turnstileSiteKey)
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif

    <h2 class="text-lg font-semibold text-paper mb-4">{{ __('Acceso al panel') }}</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-steel bg-panel text-brand-500 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-dim">{{ __('Recuérdame') }}</span>
            </label>
        </div>

        @if ($turnstileSiteKey)
            <div class="mt-4 flex flex-col items-center">
                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="dark"></div>
                <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
            </div>
        @endif

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-dim hover:text-paper rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Iniciar sesión') }}
            </x-primary-button>
        </div>
    </form>
</x-admin-guest-layout>
