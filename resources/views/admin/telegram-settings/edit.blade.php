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
                    <p class="font-semibold text-paper">{{ __('Cómo configurar (paso a paso)') }}</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>{{ __('Abre Telegram y busca') }} <span class="font-mono text-brand-400">@BotFather</span> {{ __('(el bot oficial de Telegram para crear bots).') }}</li>
                        <li>{{ __('Envíale el comando') }} <span class="font-mono text-brand-400">/newbot</span> {{ __('y sigue sus instrucciones: te pedirá un nombre para mostrar y un usuario único que debe terminar en "bot" (ej.') }} <span class="font-mono text-brand-400">MiPanelWatchBot</span>{{ __(').') }}</li>
                        <li>{{ __('BotFather te entrega un') }} <span class="font-semibold text-paper">{{ __('Token') }}</span> {{ __('(se ve así:') }} <span class="font-mono text-brand-400">123456789:AAExxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>{{ __('). Cópialo y pégalo abajo en «Bot Token» — es la contraseña de tu bot, no la compartas.') }}</li>
                        <li>
                            {{ __('Decide a dónde quieres que lleguen los avisos:') }}
                            <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                <li>{{ __('A tu chat personal: busca tu bot por su usuario dentro de Telegram y envíale cualquier mensaje (ej. "hola") para "activar" la conversación.') }}</li>
                                <li>{{ __('A un grupo: agrega el bot al grupo como un miembro más.') }}</li>
                            </ul>
                        </li>
                        <li>{{ __('Para obtener el') }} <span class="font-semibold text-paper">{{ __('Chat ID') }}</span>{{ __(': escríbele a') }} <span class="font-mono text-brand-400">@userinfobot</span> {{ __('en Telegram y te devuelve tu chat_id personal; si es un grupo, agrega a') }} <span class="font-mono text-brand-400">@getidsbot</span> {{ __('al grupo y te dará el id (empieza con "-"). Pega ese número abajo en «Chat ID».') }}</li>
                        <li>{{ __('Pulsa') }} <span class="font-semibold text-paper">«{{ __('Probar conexión') }}»</span>{{ __(': si todo está bien, tu bot te enviará un mensaje de prueba a ese chat. Solo si eso funciona, pulsa') }} <span class="font-semibold text-paper">«{{ __('Guardar') }}»</span>{{ __('.') }}</li>
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
