<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Comprar') }}
            </h2>
            <x-close-link :href="route('home')" />
        </div>
    </x-slot>

    <div class="py-8" x-data @keydown.escape.window="window.location = '{{ route('home') }}'">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-amber/10 border border-amber text-amber px-4 py-3 rounded-lg mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <nav class="flex items-center gap-2 text-sm text-dim-2 mb-6">
                <a href="{{ route('home') }}" class="hover:text-paper">{{ __('Inicio') }}</a>
                <span>&rsaquo;</span>
                @if ($package->category)
                    <a href="{{ route('packages.category', $package->category) }}" class="hover:text-paper">{{ $package->category->name }}</a>
                    <span>&rsaquo;</span>
                @endif
                <span class="text-paper">{{ $package->name }}</span>
            </nav>

            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-dim">{{ __('Por favor, introduce tus datos personales y los datos de facturación para comprar.') }}</p>
                @guest
                    <a href="{{ route('login', ['redirect' => route('orders.create', $package)]) }}"
                       class="shrink-0 ml-4 inline-flex items-center px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                        {{ __('¿Ya Registrado?') }}
                    </a>
                @endguest
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">
                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h1 class="text-xl font-display font-bold text-paper mb-4">{{ $package->name }}</h1>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                @if ($package->description)
                                    <p class="text-sm text-dim mb-3">{{ $package->description }}</p>
                                @endif

                                @if ($package->featureList())
                                    <ul class="space-y-2 text-sm text-dim">
                                        @foreach ($package->featureList() as $feature)
                                            <li class="flex items-start gap-2">
                                                <svg class="h-4 w-4 shrink-0 text-brand-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="rounded-lg bg-gradient-to-br from-brand-900 via-panel-alt to-ink flex items-center justify-center min-h-[10rem]">
                                <svg class="h-16 w-16 text-brand-500/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 14.25v-9zM9 21h6M12 17.25V21" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    @if ($package->is_trial && $trialAlreadyUsed)
                        <div class="bg-panel border border-steel rounded-lg p-6">
                            <h3 class="text-base font-semibold text-paper mb-2">{{ __('Prueba gratuita') }}</h3>
                            <p class="text-sm text-dim mb-4">
                                {{ __('Ya usaste tu prueba gratuita. Cada cuenta tiene derecho a una sola demo. Elige uno de nuestros planes de pago para seguir disfrutando del servicio.') }}
                            </p>
                            <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-full py-3 rounded-md bg-steel text-paper font-semibold hover:bg-steel/80">
                                {{ __('Ver planes de pago') }}
                            </a>
                        </div>
                    @else
                        <div @if ($needsVerificationGate) x-data="trialGateForm()" @endif>
                        <form method="POST" action="{{ route('orders.store', $package) }}" enctype="multipart/form-data" class="space-y-6"
                              @if ($needsVerificationGate) @submit.prevent="submit" @endif>
                            @csrf

                            @guest
                                <div class="bg-panel border border-steel rounded-lg p-6 space-y-6">
                                    <div class="flex items-center gap-3">
                                        <span class="h-px flex-1 bg-steel"></span>
                                        <h3 class="text-sm font-semibold text-brand-400 uppercase tracking-wide">{{ __('Información Personal') }}</h3>
                                        <span class="h-px flex-1 bg-steel"></span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="first_name" :value="__('Nombre')" />
                                            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autocomplete="given-name" />
                                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="last_name" :value="__('Apellido')" />
                                            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="email" :value="__('Dirección de e-mail')" />
                                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="phone" :value="__('Número de teléfono')" />
                                            <div class="mt-1 flex gap-2"
                                                 x-data="{
                                                    open: false,
                                                    countries: @js($countries),
                                                    selected: null,
                                                    init() {
                                                        const dial = @js(old('phone_country_code'));
                                                        this.selected = this.countries.find(c => c.dial === dial) ?? this.countries.find(c => c.name === 'Ecuador');
                                                    }
                                                 }"
                                                 @click.outside="open = false">
                                                <div class="relative shrink-0">
                                                    <button type="button" @click="open = !open"
                                                            class="flex items-center gap-1.5 h-full px-3 py-2 bg-panel border border-steel rounded-md text-paper hover:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                                        <span class="text-sm" x-text="selected?.dial"></span>
                                                        <svg class="h-3.5 w-3.5 text-dim-2" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <input type="hidden" name="phone_country_code" :value="selected?.dial">

                                                    <div x-show="open" x-cloak x-transition
                                                         class="absolute z-20 mt-1 w-72 max-h-64 overflow-y-auto bg-ink border border-steel rounded-md shadow-lg py-1">
                                                        <template x-for="c in countries" :key="c.name">
                                                            <button type="button" @click="selected = c; open = false"
                                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-dim hover:bg-panel hover:text-paper text-left">
                                                                <span class="flex-1 truncate" x-text="c.name"></span>
                                                                <span class="text-dim-2" x-text="c.dial"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-text-input id="phone" class="block w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="987654321" />
                                            </div>
                                            <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2" />
                                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 pt-2">
                                        <span class="h-px flex-1 bg-steel"></span>
                                        <h3 class="text-sm font-semibold text-brand-400 uppercase tracking-wide">{{ __('Dirección de Facturación') }}</h3>
                                        <span class="h-px flex-1 bg-steel"></span>
                                    </div>

                                    <div>
                                        <x-input-label for="address_line_1" :value="__('Dirección 1')" />
                                        <x-text-input id="address_line_1" class="block mt-1 w-full" type="text" name="address_line_1" :value="old('address_line_1')" required autocomplete="address-line1" />
                                        <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label for="city" :value="__('Ciudad')" />
                                            <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required autocomplete="address-level2" />
                                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="state" :value="__('Estado / Provincia')" />
                                            <x-text-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state')" required autocomplete="address-level1" />
                                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="postal_code" :value="__('Código Postal')" />
                                            <x-text-input id="postal_code" class="block mt-1 w-full" type="text" inputmode="numeric" pattern="[0-9]*"
                                                          name="postal_code" :value="old('postal_code')" required autocomplete="postal-code"
                                                          x-data @input="$el.value = $el.value.replace(/[^0-9]/g, '')" />
                                            <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="country" :value="__('País')" />
                                        <div class="mt-1"
                                             x-data="{
                                                open: false,
                                                countries: @js($countries),
                                                selected: null,
                                                init() {
                                                    const name = @js(old('country'));
                                                    this.selected = this.countries.find(c => c.name === name) ?? this.countries.find(c => c.name === 'Ecuador');
                                                }
                                             }"
                                             @click.outside="open = false">
                                            <div class="relative">
                                                <button type="button" @click="open = !open"
                                                        class="flex items-center justify-between w-full px-3 py-2 bg-panel border border-steel rounded-md text-paper hover:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                                    <span x-text="selected?.name"></span>
                                                    <svg class="h-3.5 w-3.5 text-dim-2 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <input type="hidden" name="country" :value="selected?.name">

                                                <div x-show="open" x-cloak x-transition
                                                     class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto bg-ink border border-steel rounded-md shadow-lg py-1">
                                                    <template x-for="c in countries" :key="c.name">
                                                        <button type="button" @click="selected = c; open = false"
                                                                class="w-full flex items-center px-3 py-2 text-sm text-dim hover:bg-panel hover:text-paper text-left">
                                                            <span x-text="c.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <x-input-error :messages="$errors->get('country')" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-3 pt-2">
                                        <span class="h-px flex-1 bg-steel"></span>
                                        <h3 class="text-sm font-semibold text-brand-400 uppercase tracking-wide">{{ __('Seguridad de la Cuenta') }}</h3>
                                        <span class="h-px flex-1 bg-steel"></span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                         x-data="{
                                            password: '',
                                            get score() {
                                                let s = 0;
                                                if (this.password.length >= 8) s++;
                                                if (this.password.length >= 12) s++;
                                                if (/[a-z]/.test(this.password)) s++;
                                                if (/[A-Z]/.test(this.password)) s++;
                                                if (/[0-9]/.test(this.password)) s++;
                                                if (/[^A-Za-z0-9]/.test(this.password)) s++;
                                                return s;
                                            },
                                            get level() {
                                                if (this.password.length === 0) return null;
                                                if (this.score <= 2) return 'bajo';
                                                if (this.score <= 4) return 'medio';
                                                return 'alto';
                                            },
                                         }">
                                        <div>
                                            <x-input-label for="password" :value="__('Contraseña')" />
                                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                                                          autocomplete="new-password" x-model="password" />
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />

                                            <div class="mt-2">
                                                <div class="flex gap-1 h-1.5">
                                                    <span class="flex-1 rounded-full" :class="score >= 1 ? (level === 'bajo' ? 'bg-red-500' : level === 'medio' ? 'bg-yellow-500' : 'bg-brand-500') : 'bg-steel'"></span>
                                                    <span class="flex-1 rounded-full" :class="score >= 3 ? (level === 'medio' ? 'bg-yellow-500' : level === 'alto' ? 'bg-brand-500' : 'bg-steel') : 'bg-steel'"></span>
                                                    <span class="flex-1 rounded-full" :class="level === 'alto' ? 'bg-brand-500' : 'bg-steel'"></span>
                                                </div>
                                                <p class="mt-1 text-xs"
                                                   :class="level === 'bajo' ? 'text-red-500' : level === 'medio' ? 'text-yellow-500' : level === 'alto' ? 'text-brand-400' : 'text-dim-2'">
                                                    {{ __('Seguridad:') }}
                                                    <span x-text="level === 'bajo' ? '{{ __('Baja') }}' : level === 'medio' ? '{{ __('Media') }}' : level === 'alto' ? '{{ __('Alta') }}' : '{{ __('—') }}'"></span>
                                                </p>
                                            </div>
                                            <p class="mt-1 text-xs text-dim-2">{{ __('Mínimo 8 caracteres, con mayúsculas, minúsculas, números y un carácter especial.') }}</p>
                                        </div>
                                        <div>
                                            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            @endguest

                            @if ($package->is_trial)
                                <div class="bg-panel border border-steel rounded-lg p-6">
                                    <h3 class="text-base font-semibold text-paper mb-2">{{ __('Prueba gratuita') }}</h3>
                                    @if ($needsVerificationGate)
                                        <p class="text-sm text-dim mb-4">
                                            {{ __('No requiere pago. Para evitar abusos, tu línea se activará automáticamente en cuanto verifiques tu correo electrónico.') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-dim mb-4">
                                            {{ __('Esta es una línea de demostración. No requiere pago: se activa al instante en cuanto confirmes.') }}
                                        </p>
                                    @endif

                                    @if ($needsVerificationGate)
                                        <x-primary-button class="w-full justify-center py-3 gap-2" x-bind:disabled="submitting">
                                            <span x-show="!submitting">{{ __('Activar prueba gratis') }}</span>
                                            <span x-show="submitting" x-cloak>{{ __('Enviando...') }}</span>
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </x-primary-button>
                                    @else
                                        <x-primary-button class="w-full justify-center py-3 gap-2">
                                            {{ __('Activar prueba gratis') }}
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </x-primary-button>
                                    @endif
                                </div>
                            @else
                                <div class="bg-panel border border-steel rounded-lg p-6">
                                    <h3 class="text-base font-semibold text-paper mb-4">{{ __('Detalles de pago') }}</h3>

                                    @if ($paymentMethods->isEmpty())
                                        <p class="text-dim-2">{{ __('Aún no hay métodos de pago disponibles. Contacta al administrador.') }}</p>
                                    @else
                                        <div class="space-y-6">
                                            <div>
                                                <x-input-label value="{{ __('Seleccione su método de pago preferido') }}" />
                                                <div class="mt-2 space-y-3" x-data="{ selected: null }">
                                                    @foreach ($paymentMethods as $method)
                                                        <label class="block border border-steel rounded-md p-4 cursor-pointer hover:border-brand-600">
                                                            <input type="radio" name="payment_method_id" value="{{ $method->id }}"
                                                                   x-on:change="selected = {{ $method->id }}"
                                                                   {{ old('payment_method_id') == $method->id ? 'checked' : '' }}
                                                                   required class="mr-2">
                                                            <span class="font-medium text-paper">{{ $method->name }}</span>
                                                            <div x-show="selected === {{ $method->id }}" x-cloak class="mt-2 text-sm text-dim whitespace-pre-line">
                                                                {{ $method->instructions }}
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                                            </div>

                                            <div>
                                                <x-input-label for="proof" value="{{ __('Comprobante de pago (imagen o PDF)') }}" />
                                                <input id="proof" name="proof" type="file" accept="image/*,application/pdf" required
                                                       class="mt-1 block w-full text-sm text-dim">
                                                <x-input-error :messages="$errors->get('proof')" class="mt-2" />
                                            </div>

                                            <div>
                                                <x-input-label for="customer_note" value="{{ __('Notas adicionales (opcional)') }}" />
                                                <textarea id="customer_note" name="customer_note" rows="3"
                                                          class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">{{ old('customer_note') }}</textarea>
                                                <x-input-error :messages="$errors->get('customer_note')" class="mt-2" />
                                            </div>

                                            <x-primary-button class="w-full justify-center py-3 gap-2">
                                                {{ __('Completar Pedido') }}
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </x-primary-button>

                                            <p class="text-xs text-dim-2 text-center">
                                                {{ __('Tu pedido quedará en revisión hasta que confirmemos el pago.') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </form>

                        @if ($needsVerificationGate)
                            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
                                <div class="bg-panel border border-steel rounded-lg p-8 max-w-sm w-full text-center">
                                    <template x-if="state === 'waiting'">
                                        <div>
                                            <svg class="animate-spin h-10 w-10 text-brand-500 mx-auto mb-4" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <h3 class="text-lg font-semibold text-paper mb-2">{{ __('Verifica tu correo') }}</h3>
                                            <p class="text-sm text-dim mb-1">
                                                {{ __('Te enviamos un enlace de verificación a') }}
                                                <span x-text="email" class="text-paper font-medium"></span>.
                                            </p>
                                            <p class="text-sm text-dim">
                                                {{ __('Haz clic en el enlace del correo para activar tu línea de prueba automáticamente. Esta ventana se actualizará sola.') }}
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="state === 'ready'">
                                        <div>
                                            <svg class="h-10 w-10 text-brand-400 mx-auto mb-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" />
                                            </svg>
                                            <h3 class="text-lg font-semibold text-paper mb-4">{{ __('¡Tu línea de prueba está activa!') }}</h3>
                                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center w-full py-3 rounded-md bg-brand-500 text-ink font-semibold hover:brightness-110">
                                                {{ __('Ver mi línea') }}
                                            </a>
                                        </div>
                                    </template>
                                    <template x-if="state === 'error'">
                                        <div>
                                            <h3 class="text-lg font-semibold text-paper mb-2">{{ __('No pudimos activar tu línea') }}</h3>
                                            <p class="text-sm text-dim">{{ __('Un administrador la revisará y la activará manualmente en breve.') }}</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif
                        </div>
                    @endif
                </div>

                <div class="bg-panel border border-steel rounded-lg p-6 order-1 lg:order-2 lg:sticky lg:top-6">
                    <h3 class="text-base font-semibold text-paper mb-4">{{ __('Resumen del pedido') }}</h3>

                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-paper">{{ $package->name }}</span>
                        <span class="font-medium text-paper whitespace-nowrap ml-4">
                            {{ $package->is_trial ? __('Gratis') : '$'.number_format($package->price, 2) }}
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-steel space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Duración') }}</span>
                            <span class="text-dim-2">{{ $package->durationLabel() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Conexiones') }}</span>
                            <span class="text-dim-2">{{ $package->max_connections }}</span>
                        </div>
                        @foreach ($package->featureList() as $feature)
                            <div class="flex justify-between gap-4">
                                <span class="text-dim">{{ $feature }}</span>
                                <span class="text-dim-2 whitespace-nowrap">{{ __('Incluido') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-steel">
                        <span class="text-xs font-semibold text-dim uppercase tracking-wide">{{ __('Total a abonar') }}</span>
                        <p class="text-3xl font-display font-extrabold text-paper mt-1">
                            @if ($package->is_trial)
                                {{ __('$0.00') }}
                            @else
                                ${{ number_format($package->price, 2) }}
                                <span class="text-sm font-normal text-dim-2">USD</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
