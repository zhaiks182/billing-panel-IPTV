<x-admin-layout>
    @php $backRoute = $defaultRole === 'admin' ? route('admin.users.admins') : route('admin.users.index'); @endphp
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Nuevo usuario') }}</h2>
            <x-close-link :href="$backRoute" />
        </div>
    </x-slot>

    <div class="py-12" x-data="{ role: '{{ old('role', $defaultRole) }}' }" @keydown.escape.window="window.location = '{{ $backRoute }}'">
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
                        <x-input-label for="role" value="{{ __('Tipo de usuario') }}" />
                        <select id="role" name="role" x-model="role" required
                                class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm">
                            <option value="customer" {{ old('role', $defaultRole) === 'customer' ? 'selected' : '' }}>{{ __('Cliente') }}</option>
                            <option value="admin" {{ old('role', $defaultRole) === 'admin' ? 'selected' : '' }}>{{ __('Administrador') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="role === 'customer'" x-cloak>
                        <x-input-label for="email" value="{{ __('Correo electrónico') }}" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      ::required="role === 'customer'" value="{{ old('email') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="role === 'admin'" x-cloak>
                        <x-input-label for="username" value="{{ __('Usuario') }}" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                                      ::required="role === 'admin'" value="{{ old('username') }}" autocomplete="off" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        <p class="mt-1 text-xs text-dim-2">{{ __('Solo letras, números, puntos, guiones y guion bajo. Sin @, no es un correo — así inicia sesión en el panel admin.') }}</p>
                    </div>

                    <div class="mt-4" x-show="role === 'admin'" x-cloak>
                        <x-input-label for="admin_role" value="{{ __('Nivel de acceso') }}" />
                        <select id="admin_role" name="admin_role" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm">
                            <option value="support" {{ old('admin_role', 'support') === 'support' ? 'selected' : '' }}>{{ __('Soporte (sin Configuración ni Paquetes/Cupones)') }}</option>
                            <option value="super_admin" {{ old('admin_role') === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin (acceso total)') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('admin_role')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="phone" value="{{ __('Teléfono (opcional)') }}" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                      value="{{ old('phone') }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <x-password-strength-fields />

                    <p class="mt-4 text-xs text-dim-2" x-show="role === 'customer'">
                        {{ __('El usuario quedará con el correo verificado automáticamente y podrá iniciar sesión de inmediato con esta contraseña.') }}
                    </p>
                    <p class="mt-4 text-xs text-dim-2" x-show="role === 'admin'" x-cloak>
                        {{ __('El administrador podrá iniciar sesión de inmediato en /adm_4livepro con este usuario y contraseña.') }}
                    </p>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Crear usuario') }}</x-primary-button>
                        <a href="{{ $backRoute }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
