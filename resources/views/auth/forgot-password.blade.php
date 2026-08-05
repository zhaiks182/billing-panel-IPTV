<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Recuperar contraseña') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <div class="mb-4 text-sm text-dim">
                    {{ __('¿Olvidaste tu contraseña? No hay problema. Indícanos tu correo electrónico y te enviaremos un enlace para restablecerla.') }}
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Correo electrónico')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button>
                            {{ __('Enviar enlace de recuperación') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
