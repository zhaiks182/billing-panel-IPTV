<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Abrir ticket de soporte') }}
            </h2>
            <x-close-link :href="route('home')" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-danger/10 border border-danger text-danger px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @guest
                    <div class="bg-panel border border-steel rounded-lg p-6 space-y-6">
                        <h3 class="text-base font-semibold text-paper">{{ __('Tus datos') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="guest_name" :value="__('Nombre')" />
                                <x-text-input id="guest_name" class="block mt-1 w-full" type="text" name="guest_name" :value="old('guest_name')" required autofocus />
                                <x-input-error :messages="$errors->get('guest_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="guest_email" :value="__('Dirección de e-mail')" />
                                <x-text-input id="guest_email" class="block mt-1 w-full" type="email" name="guest_email" :value="old('guest_email')" required />
                                <x-input-error :messages="$errors->get('guest_email')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                @endguest

                <div class="bg-panel border border-steel rounded-lg p-6 space-y-6">
                    <h3 class="text-base font-semibold text-paper">{{ __('Detalle de la consulta') }}</h3>

                    <div>
                        <x-input-label for="subject" :value="__('Asunto')" />
                        <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category" :value="__('Categoría')" />
                            <select id="category" name="category" required
                                    class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                @foreach ([
                                    'installation' => 'Instalación',
                                    'credentials' => 'Credenciales',
                                    'payment' => 'Pago',
                                    'renewal' => 'Renovación',
                                    'connection_limit' => 'Límite de conexiones',
                                    'intermittent_service' => 'Servicio intermitente',
                                    'channels_content' => 'Canales o contenido',
                                    'other' => 'Otro',
                                ] as $value => $label)
                                    <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="priority" :value="__('Prioridad')" />
                            <select id="priority" name="priority" required
                                    class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                @foreach (['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                        </div>
                    </div>

                    @auth
                        <div>
                            <x-input-label for="order_id" :value="__('Pedido relacionado')" />
                            <select id="order_id" name="order_id"
                                    class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">{{ __('Ninguno') }}</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}" {{ (string) old('order_id') === (string) $order->id ? 'selected' : '' }}>
                                        #{{ $order->id }} — {{ $order->package->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endauth

                    <div>
                        <x-input-label for="message" :value="__('Mensaje')" />
                        <textarea id="message" name="message" rows="6" required
                                  class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="attachments" :value="__('Adjuntos')" />
                        <input id="attachments" name="attachments[]" type="file" multiple
                               accept=".jpg,.jpeg,.gif,.png,.txt,.pdf"
                               class="mt-1 block w-full text-sm text-dim">
                        <p class="mt-1 text-xs text-dim-2">{{ __('Extensiones permitidas: jpg, gif, png, txt, pdf. Máximo 5MB por archivo.') }}</p>
                        <x-input-error :messages="$errors->get('attachments.0')" class="mt-2" />
                    </div>

                    <x-turnstile-widget :site-key="$turnstileSiteKey" />

                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Enviar ticket') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
