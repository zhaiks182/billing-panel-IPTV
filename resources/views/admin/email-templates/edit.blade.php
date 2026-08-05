<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Editar plantilla') }}: {{ $template->name }}</h2>
            <x-close-link :href="route('admin.email-templates.index')" />
        </div>
    </x-slot>

    <div class="py-12" x-data="{
            subject: @js($template->subject),
            html: @js($template->html_body),
            text: @js($template->text_body),
            focused: 'html',
            testing: false,
            testResult: null,
            testTo: '',
            testFromAddress: '',
            testFromName: '',
            insertVariable(name) {
                const field = this.$refs[this.focused];
                const placeholder = '{' + '{' + name + '}' + '}';
                const start = field.selectionStart;
                const end = field.selectionEnd;
                const value = field.value;
                field.value = value.slice(0, start) + placeholder + value.slice(end);
                this[this.focused] = field.value;
                field.focus();
                field.selectionStart = field.selectionEnd = start + placeholder.length;
            },
            generateTextFromHtml() {
                let source = this.html.replace(/<a\s[^>]*href=\x22([^\x22]*)\x22[^>]*>(.*?)<\/a>/gis, '$2 ($1)');
                const div = document.createElement('div');
                div.innerHTML = source;
                this.text = div.innerText.replace(/\n{3,}/g, '\n\n').trim();
            },
            testTemplate() {
                this.testing = true;
                this.testResult = null;

                fetch('{{ route('admin.email-templates.test', $template) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        to: this.testTo,
                        from_address: this.testFromAddress,
                        from_name: this.testFromName,
                        subject: this.subject,
                        html_body: this.html,
                        text_body: this.text,
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => { this.testResult = data; })
                    .catch(() => { this.testResult = { success: false, message: '{{ __('Error de red al enviar la prueba.') }}' }; })
                    .finally(() => { this.testing = false; });
            },
        }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" class="lg:col-span-3 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <x-input-label for="subject" value="{{ __('Asunto') }}" />
                        <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" required
                                      x-model="subject"
                                      value="{{ old('subject', $template->subject) }}" />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <x-input-label for="html_body" value="{{ __('Diseño HTML') }}" />
                        </div>
                        <textarea id="html_body" name="html_body" rows="16" x-ref="html" x-model="html"
                                  @focus="focused = 'html'"
                                  class="mt-1 block w-full rounded-md border-steel bg-panel text-paper font-mono text-xs shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('html_body')" class="mt-2" />

                        <p class="mt-3 text-xs text-dim-2">{{ __('Vista previa:') }}</p>
                        <iframe :srcdoc="html" class="mt-1 w-full h-80 bg-white rounded-md border border-steel"></iframe>
                    </div>

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <x-input-label for="text_body" value="{{ __('Versión en texto plano') }}" />
                            <button type="button" @click="generateTextFromHtml()" class="text-xs text-dim underline hover:text-paper">
                                {{ __('Generar desde el HTML') }}
                            </button>
                        </div>
                        <textarea id="text_body" name="text_body" rows="10" x-ref="text" x-model="text"
                                  @focus="focused = 'text'"
                                  class="mt-1 block w-full rounded-md border-steel bg-panel text-paper font-mono text-xs shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('text_body')" class="mt-2" />
                        <p class="mt-2 text-xs text-dim-2">
                            {{ __('Es lo que ven los clientes de correo que no muestran HTML. Si cambias el diseño HTML, usa "Generar desde el HTML" para mantenerlo igual y luego ajusta a mano si hace falta.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                        <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
                    </div>
                </form>

                <div class="bg-panel border border-steel rounded-lg p-6 lg:col-span-3">
                    <p class="text-sm font-semibold text-paper mb-1">{{ __('Probar esta plantilla') }}</p>
                    <p class="text-xs text-dim-2 mb-4">
                        {{ __('Envía un correo de prueba con el asunto, HTML y texto que tienes escritos ahora mismo (aunque no hayas guardado), reemplazando las variables con datos de ejemplo.') }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="test_to" value="{{ __('Enviar a') }}" />
                            <input id="test_to" type="email" x-model="testTo" placeholder="tu-correo@ejemplo.com"
                                   class="mt-1 block w-full rounded-md border-steel bg-panel text-paper placeholder-dim-2 shadow-sm text-sm">
                        </div>
                        <div>
                            <x-input-label for="test_from_address" value="{{ __('Remitente (opcional)') }}" />
                            <input id="test_from_address" type="email" x-model="testFromAddress"
                                   placeholder="{{ __('usar el de Configuración de correo') }}"
                                   class="mt-1 block w-full rounded-md border-steel bg-panel text-paper placeholder-dim-2 shadow-sm text-sm">
                        </div>
                        <div>
                            <x-input-label for="test_from_name" value="{{ __('Nombre del remitente (opcional)') }}" />
                            <input id="test_from_name" type="text" x-model="testFromName"
                                   placeholder="{{ config('app.name') }}"
                                   class="mt-1 block w-full rounded-md border-steel bg-panel text-paper placeholder-dim-2 shadow-sm text-sm">
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-dim-2">
                        {{ __('Si tu proveedor SMTP no permite cambiar el remitente, el envío puede fallar — el mensaje de error te lo va a indicar.') }}
                    </p>

                    <div class="mt-4">
                        <x-secondary-button type="button" @click="testTemplate()" ::disabled="testing || !testTo">
                            <span x-show="!testing">{{ __('Enviar correo de prueba') }}</span>
                            <span x-show="testing" x-cloak>{{ __('Enviando…') }}</span>
                        </x-secondary-button>
                        <p class="mt-2 text-sm"
                           x-show="testResult"
                           x-cloak
                           :class="testResult && testResult.success ? 'text-brand-400' : 'text-danger'"
                           x-text="testResult && testResult.message"></p>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-panel border border-steel rounded-lg p-4 sticky top-6">
                        <p class="text-sm font-semibold text-paper mb-1">{{ __('Variables disponibles') }}</p>
                        <p class="text-xs text-dim-2 mb-3">{{ __('Haz clic para insertar en el campo que tenías activo (HTML o texto).') }}</p>
                        <div class="space-y-1">
                            @foreach ($variables as $name => $description)
                                @php($placeholder = '{'.'{'.$name.'}'.'}')
                                <button type="button" @click="insertVariable('{{ $name }}')"
                                        class="w-full text-left px-2 py-1.5 rounded-md hover:bg-panel-alt">
                                    <span class="block font-mono text-xs text-brand-400">{{ $placeholder }}</span>
                                    <span class="block text-xs text-dim-2">{{ $description }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
