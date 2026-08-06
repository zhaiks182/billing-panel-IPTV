<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Crear cuenta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-dim">{{ __('Por favor, introduce tus datos personales y los datos de facturación para crear tu cuenta.') }}</p>
                    <a href="{{ route('login') }}" class="shrink-0 ml-4 inline-flex items-center px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                        {{ __('¿Ya registrado?') }}
                    </a>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <x-guest-registration-fields />

                    <x-turnstile-widget :site-key="$turnstileSiteKey" />

                    <div class="flex items-center justify-between pt-4 border-t border-steel">
                        <a class="underline text-sm text-dim hover:text-paper rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500" href="{{ route('login') }}">
                            {{ __('¿Ya tienes cuenta?') }}
                        </a>

                        <x-primary-button>
                            {{ __('Registrarme') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
