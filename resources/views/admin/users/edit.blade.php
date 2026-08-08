<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Editar cliente') }}</h2>
            <x-close-link :href="route('admin.users.show', $user)" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.users.show', $user) }}'">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="{{ __('Nombre') }}" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                                      value="{{ old('name', $user->name) }}" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" value="{{ __('Correo electrónico') }}" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required
                                      value="{{ old('email', $user->email) }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="phone_country_code" value="{{ __('Código') }}" />
                            <x-text-input id="phone_country_code" name="phone_country_code" type="text" class="mt-1 block w-full"
                                          value="{{ old('phone_country_code', $user->phone_country_code) }}" />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="phone" value="{{ __('Teléfono') }}" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                          value="{{ old('phone', $user->phone) }}" />
                        </div>
                        <x-input-error :messages="$errors->get('phone_country_code')" class="col-span-3 -mt-2" />
                        <x-input-error :messages="$errors->get('phone')" class="col-span-3" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="company" value="{{ __('Empresa (opcional)') }}" />
                        <x-text-input id="company" name="company" type="text" class="mt-1 block w-full"
                                      value="{{ old('company', $user->company) }}" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="address_line_1" value="{{ __('Dirección') }}" />
                        <x-text-input id="address_line_1" name="address_line_1" type="text" class="mt-1 block w-full"
                                      value="{{ old('address_line_1', $user->address_line_1) }}" />
                        <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="address_line_2" value="{{ __('Dirección (línea 2, opcional)') }}" />
                        <x-text-input id="address_line_2" name="address_line_2" type="text" class="mt-1 block w-full"
                                      value="{{ old('address_line_2', $user->address_line_2) }}" />
                        <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="city" value="{{ __('Ciudad') }}" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                          value="{{ old('city', $user->city) }}" />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="state" value="{{ __('Estado / Provincia') }}" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                          value="{{ old('state', $user->state) }}" />
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="postal_code" value="{{ __('Código Postal') }}" />
                            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full"
                                          value="{{ old('postal_code', $user->postal_code) }}" />
                            <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="country" value="{{ __('País') }}" />
                            <select id="country" name="country" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm">
                                <option value="">{{ __('— Sin especificar —') }}</option>
                                @foreach (config('countries') as $c)
                                    <option value="{{ $c['name'] }}" {{ old('country', $user->country) === $c['name'] ? 'selected' : '' }}>
                                        {{ $c['flag'] }} {{ $c['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('country')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
