<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Notificaciones por Telegram') }}</h2>
            <x-close-link :href="route('admin.dashboard')" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.dashboard') }}'">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-panel border border-steel rounded-lg p-6">
                <div class="text-sm text-dim mb-6 space-y-2">
                    <p>{{ __('Recibe un mensaje en Telegram cada vez que entra un pedido nuevo (demo o de pago). Es gratis, sin límites de mensajes.') }}</p>
                    <p class="font-semibold text-paper">{{ __('Cómo obtener tus datos:') }}</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>{{ __('En Telegram, busca a') }} <span class="font-mono text-brand-400">@BotFather</span> {{ __('y envía') }} <span class="font-mono text-brand-400">/newbot</span>{{ __(', sigue los pasos y copia el token que te da.') }}</li>
                        <li>{{ __('Busca a') }} <span class="font-mono text-brand-400">@userinfobot</span> {{ __('y envíale cualquier mensaje: te responderá con tu Chat ID.') }}</li>
                        <li>{{ __('Escríbele primero un mensaje a tu propio bot (así puede enviarte a ti).') }}</li>
                    </ol>
                </div>

                <form method="POST" action="{{ route('admin.telegram.update') }}" x-data="{ enabled: {{ old('enabled', $settings->enabled) ? 'true' : 'false' }} }">
                    @csrf
                    @method('PUT')

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="enabled" value="1" x-model="enabled"
                               class="rounded border-steel bg-panel text-brand-500 shadow-sm focus:ring-brand-500">
                        <span class="text-sm text-paper">{{ __('Activar notificaciones por Telegram') }}</span>
                    </label>

                    <div class="mt-4" x-show="enabled" x-cloak>
                        <x-input-label for="bot_token" value="{{ __('Bot Token') }}" />
                        <x-text-input id="bot_token" name="bot_token" type="password" class="mt-1 block w-full"
                                      placeholder="{{ $settings->bot_token ? '••••••••••• ('.__('dejar en blanco para no cambiar').')' : '123456:ABC-DEF...' }}" />
                        <x-input-error :messages="$errors->get('bot_token')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="enabled" x-cloak>
                        <x-input-label for="chat_id" value="{{ __('Chat ID') }}" />
                        <x-text-input id="chat_id" name="chat_id" type="text" class="mt-1 block w-full"
                                      placeholder="123456789"
                                      value="{{ old('chat_id', $settings->chat_id) }}" />
                        <x-input-error :messages="$errors->get('chat_id')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                        <button type="submit" name="send_test" value="1" class="text-sm text-dim underline">
                            {{ __('Guardar y enviar mensaje de prueba') }}
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
