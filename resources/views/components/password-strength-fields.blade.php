{{--
    Campos de "Contraseña" + "Confirmar contraseña" con medidor de fuerza (Baja/Media/Alta)
    e indicador de coincidencia en vivo. Compartido entre el registro/checkout de invitado
    (dentro de <x-guest-registration-fields />) y el formulario de "nueva contraseña" del
    reset por correo (auth/reset-password.blade.php) — antes este último no tenía ninguno
    de los dos, a pedido del usuario, 2026-08-05.
--}}
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
