<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Configuración XUI ONE') }}</h2>
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
                <form method="POST" action="{{ route('admin.xui.update') }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="panel_url" value="{{ __('URL del panel (API)') }}" />
                        <x-text-input id="panel_url" name="panel_url" type="url" class="mt-1 block w-full" required
                                      placeholder="https://tu-panel.com"
                                      value="{{ old('panel_url', $settings->panel_url) }}" />
                        <x-input-error :messages="$errors->get('panel_url')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="access_code" value="{{ __('Código de acceso (Access Code)') }}" />
                        <x-text-input id="access_code" name="access_code" type="text" class="mt-1 block w-full" required
                                      value="{{ old('access_code', $settings->access_code) }}" />
                        <x-input-error :messages="$errors->get('access_code')" class="mt-2" />
                        <p class="mt-1 text-xs text-dim-2">{{ __('Generado por el admin del panel XUI para la API de reseller.') }}</p>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="api_token" value="{{ __('API Key') }}" />
                        <x-text-input id="api_token" name="api_token" type="password" class="mt-1 block w-full"
                                      placeholder="{{ $settings->api_token ? '••••••••••• ('.__('dejar en blanco para no cambiar').')' : '' }}" />
                        <x-input-error :messages="$errors->get('api_token')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="stream_url" value="{{ __('URL de streaming (para armar el link M3U)') }}" />
                        <x-text-input id="stream_url" name="stream_url" type="url" class="mt-1 block w-full"
                                      placeholder="http://tu-panel.com:2082"
                                      value="{{ old('stream_url', $settings->stream_url) }}" />
                        <x-input-error :messages="$errors->get('stream_url')" class="mt-2" />
                        <p class="mt-1 text-xs text-dim-2">{{ __('Se usa para armar el enlace M3U del cliente: {url}/playlist/usuario/clave/m3u_plus') }}</p>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="server_url" value="{{ __('Servidor (se muestra al cliente para configurar su reproductor)') }}" />
                        <x-text-input id="server_url" name="server_url" type="url" class="mt-1 block w-full"
                                      placeholder="https://tu-panel.com:2082"
                                      value="{{ old('server_url', $settings->server_url) }}" />
                        <x-input-error :messages="$errors->get('server_url')" class="mt-2" />
                        <p class="mt-1 text-xs text-dim-2">{{ __('Dirección que el cliente debe ingresar como "Servidor" en apps IPTV que piden Servidor/Usuario/Contraseña por separado.') }}</p>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                        <button type="submit" name="test_connection" value="1"
                                class="text-sm text-dim underline">
                            {{ __('Guardar y probar conexión') }}
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
