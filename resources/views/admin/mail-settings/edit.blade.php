<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Configuración de correo') }}</h2>
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
                    {{ __('Esta configuración se usa para enviar el correo de verificación de cuenta y la confirmación de pedidos aprobados.') }}
                </p>

                <form method="POST" action="{{ route('admin.mail.update') }}" x-data="{ mailer: '{{ old('mailer', $settings->mailer) }}' }">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="mailer" value="{{ __('Modo de envío') }}" />
                        <select id="mailer" name="mailer" x-model="mailer"
                                class="mt-1 block w-full bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="log" {{ old('mailer', $settings->mailer) === 'log' ? 'selected' : '' }}>{{ __('Registro (log) — no envía correos reales, solo para pruebas') }}</option>
                            <option value="smtp" {{ old('mailer', $settings->mailer) === 'smtp' ? 'selected' : '' }}>{{ __('SMTP — envío real de correos') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('mailer')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="mailer === 'smtp'" x-cloak>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="host" value="{{ __('Servidor SMTP (Host)') }}" />
                                <x-text-input id="host" name="host" type="text" class="mt-1 block w-full"
                                              placeholder="smtp.gmail.com"
                                              value="{{ old('host', $settings->host) }}" />
                                <x-input-error :messages="$errors->get('host')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="port" value="{{ __('Puerto') }}" />
                                <x-text-input id="port" name="port" type="number" class="mt-1 block w-full"
                                              placeholder="587"
                                              value="{{ old('port', $settings->port) }}" />
                                <x-input-error :messages="$errors->get('port')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="username" value="{{ __('Usuario') }}" />
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                                          placeholder="tucorreo@gmail.com"
                                          value="{{ old('username', $settings->username) }}" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" value="{{ __('Contraseña') }}" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                          placeholder="{{ $settings->password ? '••••••••••• ('.__('dejar en blanco para no cambiar').')' : '' }}" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <p class="mt-1 text-xs text-dim-2">{{ __('Si usas Gmail, aquí va la "contraseña de aplicación" de 16 dígitos, no tu contraseña normal.') }}</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="encryption" value="{{ __('Cifrado') }}" />
                            <select id="encryption" name="encryption"
                                    class="mt-1 block w-full bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="tls" {{ old('encryption', $settings->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('encryption', $settings->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            <x-input-error :messages="$errors->get('encryption')" class="mt-2" />
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="from_address" value="{{ __('Correo remitente') }}" />
                                <x-text-input id="from_address" name="from_address" type="email" class="mt-1 block w-full"
                                              placeholder="soporte@4livepro.com"
                                              value="{{ old('from_address', $settings->from_address) }}" />
                                <x-input-error :messages="$errors->get('from_address')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="from_name" value="{{ __('Nombre remitente') }}" />
                                <x-text-input id="from_name" name="from_name" type="text" class="mt-1 block w-full"
                                              placeholder="4LivePro Latino"
                                              value="{{ old('from_name', $settings->from_name) }}" />
                                <x-input-error :messages="$errors->get('from_name')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="test_email" value="{{ __('Enviar correo de prueba a (opcional)') }}" />
                            <x-text-input id="test_email" name="test_email" type="email" class="mt-1 block w-full"
                                          placeholder="tu-correo-personal@ejemplo.com" />
                            <x-input-error :messages="$errors->get('test_email')" class="mt-2" />
                            <p class="mt-1 text-xs text-dim-2">{{ __('Si lo llenas, al guardar se enviará un correo de prueba a esta dirección usando estos datos.') }}</p>
                        </div>
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
