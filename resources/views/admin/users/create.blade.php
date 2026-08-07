<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Nuevo usuario') }}</h2>
            <x-close-link :href="route('admin.users.index')" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.users.index') }}'">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="name" value="{{ __('Nombre') }}" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                                      value="{{ old('name') }}" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" value="{{ __('Correo electrónico') }}" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required
                                      value="{{ old('email') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="phone" value="{{ __('Teléfono (opcional)') }}" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                      value="{{ old('phone') }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" value="{{ __('Tipo de usuario') }}" />
                        <select id="role" name="role" required
                                class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm">
                            <option value="customer" {{ old('role', 'customer') === 'customer' ? 'selected' : '' }}>{{ __('Cliente') }}</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>{{ __('Administrador') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <x-password-strength-fields />

                    <p class="mt-4 text-xs text-dim-2">
                        {{ __('El usuario quedará con el correo verificado automáticamente y podrá iniciar sesión de inmediato con esta contraseña.') }}
                    </p>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Crear usuario') }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
