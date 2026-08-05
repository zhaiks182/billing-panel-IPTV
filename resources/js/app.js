

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Usado por el formulario de compra de una prueba gratuita cuando el cliente
 * todavía no verificó su correo (orders/create.blade.php, sección "Prueba gratuita").
 * Envía el pedido por AJAX y muestra el modal de espera mientras se activa la
 * línea automáticamente al verificar el correo (ver TrialActivator).
 */
window.trialGateForm = function () {
    return {
        submitting: false,
        modalOpen: false,
        state: 'waiting',
        email: '',
        errorMessage: '',
        pollTimer: null,
        rootEl: null,

        init() {
            // $el varía según qué directiva lo evalúa (en @submit apunta al <form>,
            // no al div raíz), así que se guarda una referencia estable acá.
            this.rootEl = this.$el;
        },

        submit(event) {
            this.submitting = true;
            this.errorMessage = '';

            const form = event.target;
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 20000);

            fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
                signal: controller.signal,
            })
                .then(async (response) => {
                    clearTimeout(timeout);
                    const data = await response.json().catch(() => null);

                    if (!data) {
                        console.error('trialGateForm: respuesta no es JSON', response.status);
                        this.submitting = false;
                        this.errorMessage = `Ocurrió un error (código ${response.status}). Intenta de nuevo.`;
                        return;
                    }

                    if (data.status === 'pending_verification') {
                        this.email = data.email;
                        this.state = 'waiting';
                        this.modalOpen = true;
                        this.submitting = false;
                        this.pollStatus(data.order_id);
                    } else if (data.status === 'approved') {
                        this.state = 'ready';
                        this.modalOpen = true;
                        this.submitting = false;
                    } else if (response.ok) {
                        this.state = 'error';
                        this.modalOpen = true;
                        this.submitting = false;
                    } else {
                        console.error('trialGateForm: error de validacion', data);
                        this.submitting = false;
                        this.errorMessage = data.message
                            || Object.values(data.errors || {}).flat()[0]
                            || 'No se pudo enviar el formulario. Revisa los datos e intenta de nuevo.';
                    }
                })
                .catch((error) => {
                    clearTimeout(timeout);
                    console.error('trialGateForm: fallo el envio', error);
                    this.submitting = false;
                    this.errorMessage = error.name === 'AbortError'
                        ? 'El servidor tardó demasiado en responder. Intenta de nuevo.'
                        : 'Ocurrió un error de red. Revisa tu conexión e intenta de nuevo.';
                });
        },

        pollStatus(orderId) {
            const statusUrl = this.rootEl.dataset.statusUrlTemplate.replace('__ORDER_ID__', orderId);
            const pollIntervalMs = 3000;
            const maxWaitMs = 10 * 60 * 1000; // 10 minutos: si no verifica el correo en ese tiempo, se cierra el aviso solo.
            let elapsedMs = 0;

            this.pollTimer = setInterval(() => {
                elapsedMs += pollIntervalMs;

                if (elapsedMs >= maxWaitMs) {
                    clearInterval(this.pollTimer);
                    this.state = 'timeout';
                    return;
                }

                fetch(statusUrl, { headers: { Accept: 'application/json' } })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status === 'approved') {
                            clearInterval(this.pollTimer);
                            this.state = 'ready';
                        } else if (data.status === 'error') {
                            clearInterval(this.pollTimer);
                            this.state = 'error';
                        }
                    })
                    .catch((error) => console.error('trialGateForm: fallo el sondeo de estado', error));
            }, pollIntervalMs);
        },

        closeModal() {
            clearInterval(this.pollTimer);
            this.modalOpen = false;
        },
    };
};

Alpine.start();
