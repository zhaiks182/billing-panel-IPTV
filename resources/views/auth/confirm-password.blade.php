<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Confirmar contraseña') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <div class="mb-4 text-sm text-dim">
                    {{ __('Esta es un área segura de la aplicación. Por favor confirma tu contraseña antes de continuar.') }}
                </div>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Contraseña')" />

                        <x-text-input id="password" class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-primary-button>
                            {{ __('Confirmar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
