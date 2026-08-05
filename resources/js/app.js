

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
        pollTimer: null,

        submit() {
            this.submitting = true;

            const form = this.$el.querySelector('form');

            fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => null);

                    if (!data) {
                        this.submitting = false;
                        alert('Ocurrió un error. Intenta de nuevo.');
                        return;
                    }

                    if (data.status === 'pending_verification') {
                        this.email = data.email;
                        this.state = 'waiting';
                        this.modalOpen = true;
                        this.pollStatus(form, data.order_id);
                    } else if (data.status === 'approved') {
                        this.state = 'ready';
                        this.modalOpen = true;
                        this.submitting = false;
                    } else {
                        this.state = 'error';
                        this.modalOpen = true;
                        this.submitting = false;
                    }
                })
                .catch(() => {
                    this.submitting = false;
                    alert('Ocurrió un error de red. Intenta de nuevo.');
                });
        },

        pollStatus(form, orderId) {
            const statusUrl = this.$el.dataset.statusUrlTemplate.replace('__ORDER_ID__', orderId);

            this.pollTimer = setInterval(() => {
                fetch(statusUrl, { headers: { Accept: 'application/json' } })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status === 'approved') {
                            clearInterval(this.pollTimer);
                            this.state = 'ready';
                            this.submitting = false;
                        } else if (data.status === 'error') {
                            clearInterval(this.pollTimer);
                            this.state = 'error';
                            this.submitting = false;
                        }
                    });
            }, 3000);
        },
    };
};

Alpine.start();
