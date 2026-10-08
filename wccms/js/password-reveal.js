(() => {
    'use strict';
    let nextId = 0;
    function enhance(root) {
        const fields = root.querySelectorAll('input[type="password"]');
        fields.forEach(field => {
            if (field.dataset.passwordRevealReady === 'true') return;
            field.dataset.passwordRevealReady = 'true';
            const wrapper = document.createElement('div');
            wrapper.className = 'wccms-password-field';
            field.before(wrapper);
            wrapper.append(field);
            if (!field.id || document.querySelectorAll('[id="' + CSS.escape(field.id) + '"]').length > 1) {
                do { field.id = 'wccms-password-' + (++nextId); } while (document.getElementById(field.id) !== field);
            }
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'wccms-password-reveal';
            const icon = document.createElement('span');
            icon.className = 'wccms-password-eye';
            icon.setAttribute('aria-hidden', 'true');
            button.append(icon);
            button.title = 'Show password';
            button.setAttribute('aria-label', 'Show password');
            button.setAttribute('aria-pressed', 'false');
            button.setAttribute('aria-controls', field.id);
            button.addEventListener('click', () => {
                const show = field.type === 'password';
                field.type = show ? 'text' : 'password';
                icon.classList.toggle('wccms-password-eye-slash', show);
                button.title = show ? 'Hide password' : 'Show password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', String(show));
            });
            wrapper.append(button);
        });
    }
    function start() {
        enhance(document);
        new MutationObserver(records => {
            if (records.some(record => record.addedNodes.length)) enhance(document);
        }).observe(document.body, {childList: true, subtree: true});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true});
    else start();
})();
