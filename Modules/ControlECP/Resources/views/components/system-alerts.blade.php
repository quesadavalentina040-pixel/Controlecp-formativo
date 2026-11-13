<style>
    .swal2-popup:not(.swal2-toast) {
        border-radius: 20px;
        padding: 2rem 1.75rem;
        box-shadow: 0 18px 55px rgba(0, 19, 30, 0.22);
        font-family: 'Poppins', 'Source Sans Pro', sans-serif;
    }

    .swal2-popup:not(.swal2-toast) .swal2-title {
        color: #17212b;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .swal2-popup:not(.swal2-toast) .swal2-html-container {
        color: #53616d;
        font-size: 1rem;
        line-height: 1.6;
        white-space: pre-line;
    }

    .swal2-popup:not(.swal2-toast) .swal2-confirm {
        border-radius: 10px;
        background: #39a900 !important;
        padding: 0.7rem 1.8rem;
        font-weight: 600;
        box-shadow: none !important;
    }

    .swal2-popup:not(.swal2-toast) .swal2-confirm:hover {
        background: #2b8000 !important;
    }

    .swal2-popup:not(.swal2-toast) .swal2-cancel {
        border-radius: 10px;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
    }
</style>

<script>
    (() => {
        const titles = {
            success: '¡Éxito!',
            error: 'No se pudo completar',
            warning: 'Atención',
            info: 'Información'
        };
        const priorities = { success: 1, info: 2, warning: 3, error: 4 };
        const alertSelector = '.alert, .alert-success, .alert-danger, .alert-warning, .alert-info, [role="alert"]';
        let alertQueue = Promise.resolve();

        const normalizeType = type => {
            if (type === 'danger') return 'error';
            return Object.prototype.hasOwnProperty.call(titles, type) ? type : 'info';
        };

        window.showSystemAlert = (message, type = 'info', title) => {
            const text = String(message ?? '').trim();
            if (!text) return Promise.resolve();
            if (!window.Swal || typeof window.Swal.fire !== 'function') {
                window.alert(text);
                return Promise.resolve();
            }

            const icon = normalizeType(type);
            const alertPromise = alertQueue.catch(() => {}).then(() => window.Swal.fire({
                icon,
                title: title || titles[icon],
                text,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#39a900'
            }));

            alertQueue = alertPromise;
            return alertPromise;
        };

        window.patchSystemAlertsToastr = () => {
            window.toastr = window.toastr || {};
            ['success', 'error', 'warning', 'info'].forEach(type => {
                window.toastr[type] = (message, title) => window.showSystemAlert(message, type, title);
            });
        };
        window.patchSystemAlertsToastr();

        const convertInlineAlerts = () => {
            if (!window.Swal || typeof window.Swal.fire !== 'function') return;

            const alerts = Array.from(document.querySelectorAll(alertSelector))
                .filter(element => !element.hasAttribute('data-system-alert-ignore'));

            const entries = alerts.map(element => {
                const clone = element.cloneNode(true);
                clone.querySelectorAll('button[data-bs-dismiss="alert"], .btn-close, i, svg').forEach(child => child.remove());
                const hasActions = Array.from(clone.querySelectorAll('a, button, input, select, textarea'))
                    .some(child => !child.matches('button[data-bs-dismiss="alert"], .btn-close'));
                if (hasActions) return null;

                const type = element.classList.contains('alert-danger') ? 'error'
                    : element.classList.contains('alert-warning') ? 'warning'
                    : element.classList.contains('alert-info') ? 'info'
                    : element.classList.contains('alert-success') ? 'success'
                    : 'info';
                const headingElement = clone.querySelector('.alert-heading, strong');
                const heading = headingElement ? headingElement.innerText.trim() : '';
                let message = (clone.innerText || clone.textContent || '').trim();
                if (heading && message.startsWith(heading)) {
                    message = message.slice(heading.length).trim();
                }

                return message ? { element, type, heading, message } : null;
            }).filter(Boolean);

            if (!entries.length) return;

            const uniqueEntries = entries.filter((entry, index) =>
                entries.findIndex(candidate => candidate.message === entry.message) === index
            );
            const type = uniqueEntries.reduce((highest, entry) =>
                priorities[entry.type] > priorities[highest] ? entry.type : highest,
                'success'
            );
            const heading = uniqueEntries.length === 1 ? uniqueEntries[0].heading : '';
            const message = uniqueEntries.map(entry => entry.message).join('\n');

            entries.forEach(entry => entry.element.remove());
            window.showSystemAlert(message, type, heading || titles[type]);
        };

        const initializeInlineAlerts = () => {
            convertInlineAlerts();

            if (!window.MutationObserver || !document.body) return;
            const observer = new MutationObserver(records => {
                const hasNewAlert = records.some(record => {
                    const target = record.target.nodeType === 1 ? record.target : record.target.parentElement;
                    return Array.from(record.addedNodes).some(node =>
                        node.nodeType === 1 && (node.matches(alertSelector) || node.querySelector(alertSelector))
                    ) || Boolean(target && target.closest(alertSelector));
                });
                if (hasNewAlert) convertInlineAlerts();
            });
            observer.observe(document.body, { childList: true, subtree: true });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeInlineAlerts, { once: true });
        } else {
            initializeInlineAlerts();
        }
    })();
</script>
