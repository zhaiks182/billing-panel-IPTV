<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Iniciar sesión') }}
        </h2>
    </x-slot>

    @if ($turnstileSiteKey)
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif

    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
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

                <p class="mt-6 text-sm text-dim-2 text-center">
                    {{ __('¿No tienes cuenta?') }}
                    <a href="{{ route('register') }}" class="text-paper underline">{{ __('Regístrate') }}</a>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
