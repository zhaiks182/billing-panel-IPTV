@php
    $countries = config('countries');
@endphp

{{--
    Campos de "Información Personal" + "Dirección de Facturación" + "Seguridad de la Cuenta"
    compartidos entre auth/register.blade.php y orders/create.blade.php (checkout de invitado).
    Antes estaban duplicados en los dos archivos — cualquier cambio a estos campos se hacía dos
    veces a mano y ya se olvidó replicar más de una vez. Ver CLAUDE.md, sección "Registro de
    usuarios", para el detalle de las reglas de validación que corresponden a estos campos.
--}}

<div class="flex items-center gap-3">
    <span class="h-px flex-1 bg-steel"></span>
    <h3 class="text-sm font-semibold text-brand-400 uppercase tracking-wide">{{ __('Información Personal') }}</h3>
    <span class="h-px flex-1 bg-steel"></span>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="first_name" :value="__('Nombre')" />
        <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
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
                     class="absolute z-20 mt-1 w-72 max-h-64 overflow-y-auto scrollbar-dark bg-ink border border-steel rounded-md shadow-lg py-1">
                    <template x-for="c in countries" :key="c.name">
                        <button type="button" @click="selected = c; open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-dim hover:bg-panel hover:text-paper text-left">
                            <span class="flex-1 truncate" x-text="c.name"></span>
                            <span class="text-dim-2" x-text="c.dial"></span>
                        </button>
                    </template>
                </div>
            </div>
            <x-text-input id="phone" class="block w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" />
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
                 class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto scrollbar-dark bg-ink border border-steel rounded-md shadow-lg py-1">
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
        passwordConfirmation: '',
        get match() {
            if (this.passwordConfirmation.length === 0) return null;
            return this.password === this.passwordConfirmation;
        },
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
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required
                      autocomplete="new-password" x-model="passwordConfirmation" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

        <p class="mt-2 text-xs" x-show="match !== null" x-cloak
           :class="match ? 'text-brand-400' : 'text-red-500'">
            <span x-text="match ? '✓ {{ __('Las contraseñas coinciden') }}' : '✕ {{ __('Las contraseñas no coinciden') }}'"></span>
        </p>
    </div>
</div>
