<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Cloudflare Turnstile') }}</h2>
            <x-close-link :href="route('admin.dashboard')" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.dashboard') }}'">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-panel border border-steel rounded-lg p-6">
                <p class="text-sm text-dim mb-6">
                    {{ __('Turnstile protege el inicio de sesión contra bots, sin captchas molestos para tus clientes. Crea un sitio en') }}
                    <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener" class="text-brand-400 underline">dash.cloudflare.com/turnstile</a>
                    {{ __('y copia aquí la Site Key y la Secret Key.') }}
                </p>

                <form method="POST" action="{{ route('admin.turnstile.update') }}">
                    @csrf
                    @method('PUT')

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', $settings->enabled) ? 'checked' : '' }}
                               class="rounded border-steel bg-panel text-brand-500 shadow-sm focus:ring-brand-500">
                        <span class="text-sm text-paper">{{ __('Activar Turnstile en el inicio de sesión') }}</span>
                    </label>

                    <div class="mt-4">
                        <x-input-label for="site_key" value="{{ __('Site Key') }}" />
                        <x-text-input id="site_key" name="site_key" type="text" class="mt-1 block w-full"
                                      placeholder="0x4AAAAAAA..."
                                      value="{{ old('site_key', $settings->site_key) }}" />
                        <x-input-error :messages="$errors->get('site_key')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="secret_key" value="{{ __('Secret Key') }}" />
                        <x-text-input id="secret_key" name="secret_key" type="password" class="mt-1 block w-full"
                                      placeholder="{{ $settings->secret_key ? '••••••••••• ('.__('dejar en blanco para no cambiar').')' : '0x4AAAAAAA...' }}" />
                        <x-input-error :messages="$errors->get('secret_key')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="theme" value="{{ __('Color del widget') }}" />
                        <select id="theme" name="theme" class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
                            <option value="dark" {{ old('theme', $settings->theme) === 'dark' ? 'selected' : '' }}>{{ __('Oscuro') }}</option>
                            <option value="light" {{ old('theme', $settings->theme) === 'light' ? 'selected' : '' }}>{{ __('Claro (blanco)') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('theme')" class="mt-2" />
                        <p class="mt-2 text-xs text-dim-2">
                            {{ __('Aplica a los 3 formularios que muestran el widget: login del panel admin, login de clientes, y registro/checkout/tickets.') }}
                        </p>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
