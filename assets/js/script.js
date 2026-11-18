/**
 * Click 2 Action — Interacciones del sitio público.
 * Sin dependencias. Cada módulo se activa solo si su elemento existe en la página.
 */
(function () {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Menú móvil --------------------------------------------------------- */
    function initNav() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const nav = document.querySelector('[data-nav]');
        if (!toggle || !nav) return;

        const setOpen = (open) => {
            toggle.setAttribute('aria-expanded', String(open));
            toggle.querySelector('.visually-hidden').textContent = open ? 'Cerrar menú' : 'Abrir menú';
            nav.classList.toggle('is-open', open);
            document.body.classList.toggle('nav-open', open);
        };

        toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
        nav.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
        });
        window.matchMedia('(min-width: 1024px)').addEventListener('change', (mq) => { if (mq.matches) setOpen(false); });
    }

    /* Cabecera con sombra al hacer scroll -------------------------------- */
    function initHeader() {
        const header = document.querySelector('[data-header]');
        if (!header) return;
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* Aparición progresiva de secciones ---------------------------------- */
    function initReveal() {
        const items = document.querySelectorAll('.reveal');
        if (!items.length) return;
        if (reduceMotion || !('IntersectionObserver' in window)) {
            items.forEach((el) => el.classList.add('is-visible'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        items.forEach((el) => io.observe(el));
    }

    /* Trazo animado de los bocadillos del logo --------------------------- */
    function initDraw() {
        document.querySelectorAll('.draw path').forEach((path) => {
            const len = Math.ceil(path.getTotalLength());
            path.style.setProperty('--len', len);
        });
    }

    /* Marquesina infinita: duplica la pista para un bucle sin cortes ----- */
    function initMarquee() {
        document.querySelectorAll('[data-marquee]').forEach((marquee) => {
            const track = marquee.querySelector('.marquee__track');
            if (!track || reduceMotion) return;
            const clone = track.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.querySelectorAll('a, img').forEach((el) => {
                el.setAttribute('tabindex', '-1');
                if (el.tagName === 'IMG') el.alt = '';
            });
            marquee.appendChild(clone);
        });
    }

    /* Compositor de WhatsApp --------------------------------------------- */
    function initComposer() {
        const composer = document.querySelector('[data-composer]');
        if (!composer) return;

        const number = composer.dataset.number;
        const message = composer.querySelector('[data-composer-message]');
        const send = composer.querySelector('[data-composer-send]');
        const chips = composer.querySelectorAll('[data-msg]');

        const update = (text) => {
            message.textContent = text;
            send.href = 'https://wa.me/' + number + '?text=' + encodeURIComponent(text);
            if (!reduceMotion) {
                message.parentElement.classList.remove('is-updating');
                void message.parentElement.offsetWidth; // reinicia la animación
                message.parentElement.classList.add('is-updating');
            }
        };

        chips.forEach((chip) => {
            chip.addEventListener('click', () => {
                chips.forEach((c) => c.setAttribute('aria-pressed', 'false'));
                chip.setAttribute('aria-pressed', 'true');
                update(chip.dataset.msg);
            });
        });
    }

    /* Formulario de contacto --------------------------------------------- */
    function initContactForm() {
        const form = document.querySelector('[data-contact-form]');
        if (!form) return;

        const status = form.querySelector('[data-form-status]');
        const submit = form.querySelector('[type="submit"]');
        const submitLabel = submit.innerHTML;

        const messages = {
            nombre: 'Escribe tu nombre completo.',
            email: 'Escribe un correo válido, por ejemplo nombre@empresa.com.',
            telefono: 'Escribe un número de celular de al menos 7 dígitos.',
            motivo: 'Elige el motivo de tu mensaje.',
            mensaje: 'Cuéntanos un poco más (mínimo 10 caracteres).',
            acepto: 'Debes aceptar la política de tratamiento de datos para enviar el formulario.',
        };

        const setError = (name, text) => {
            const field = form.querySelector('[name="' + name + '"]');
            if (!field) return;
            const wrap = field.closest('.field, .check');
            const out = form.querySelector('[data-error-for="' + name + '"]');
            if (wrap) wrap.classList.toggle('has-error', Boolean(text));
            field.setAttribute('aria-invalid', text ? 'true' : 'false');
            if (out) out.textContent = text || '';
        };

        const validate = () => {
            let firstInvalid = null;
            Object.keys(messages).forEach((name) => {
                const field = form.querySelector('[name="' + name + '"]');
                if (!field) return;
                let ok = field.type === 'checkbox' ? field.checked : field.checkValidity();
                if (name === 'telefono') ok = field.value.replace(/\D/g, '').length >= 7;
                setError(name, ok ? '' : messages[name]);
                if (!ok && !firstInvalid) firstInvalid = field;
            });
            if (firstInvalid) firstInvalid.focus();
            return !firstInvalid;
        };

        form.querySelectorAll('input, select, textarea').forEach((field) => {
            field.addEventListener('blur', () => {
                if (field.getAttribute('aria-invalid') === 'true') validate();
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            status.className = 'alert';
            status.textContent = '';
            if (!validate()) return;

            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            submit.textContent = 'Enviando…';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'fetch' },
                });
                const data = await res.json();
                if (data.ok) {
                    form.reset();
                    status.className = 'alert alert--success';
                    status.textContent = data.message;
                    if (window.gtag) window.gtag('event', 'generate_lead', { method: 'formulario' });
                } else {
                    if (data.errors) Object.entries(data.errors).forEach(([k, v]) => setError(k, v));
                    status.className = 'alert alert--error';
                    status.textContent = data.message || 'No pudimos enviar tu mensaje. Revisa los campos marcados.';
                }
            } catch (err) {
                status.className = 'alert alert--error';
                status.textContent = 'No pudimos conectar con el servidor. Inténtalo de nuevo o escríbenos por WhatsApp.';
            } finally {
                submit.disabled = false;
                submit.removeAttribute('aria-busy');
                submit.innerHTML = submitLabel;
                status.focus();
            }
        });
    }

    /* Medición de clics a WhatsApp (si GA4 está activo) ------------------ */
    function initTracking() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('[data-track]');
            if (link && window.gtag) {
                window.gtag('event', 'contact_whatsapp', { location: link.dataset.track });
            }
        });
    }

    /* Copiar enlace del artículo ----------------------------------------- */
    function initShare() {
        const btn = document.querySelector('[data-copy-link]');
        if (!btn) return;
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await navigator.clipboard.writeText(window.location.href);
                btn.setAttribute('aria-label', 'Enlace copiado');
                btn.title = 'Enlace copiado';
            } catch (err) { /* el navegador no permite copiar */ }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initNav();
        initHeader();
        initDraw();
        initReveal();
        initMarquee();
        initComposer();
        initContactForm();
        initTracking();
        initShare();
    });
})();
