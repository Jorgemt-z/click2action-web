/**
 * Click 2 Action — Interacciones del panel de administración.
 */
(function () {
    'use strict';

    const slugify = (text) => text
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    /* Confirmación antes de eliminar */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    /* Vista previa del logo de cliente antes de guardar */
    const logoInput = document.querySelector('[data-logo-input]');
    const logoPreview = document.querySelector('[data-logo-preview]');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', () => {
            const file = logoInput.files && logoInput.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const img = document.createElement('img');
            img.alt = 'Vista previa del nuevo logo';
            img.src = URL.createObjectURL(file);
            logoPreview.replaceChildren(img);
        });
    }

    const editor = document.querySelector('[data-editor]');
    if (!editor) return;

    const title = editor.querySelector('[data-title]');
    const slug = editor.querySelector('[data-slug]');
    const content = editor.querySelector('[data-content]');
    const metaTitle = editor.querySelector('[name="meta_title"]');
    const metaDesc = editor.querySelector('[name="meta_description"]');
    const excerpt = editor.querySelector('[name="excerpt"]');
    const serpTitle = editor.querySelector('[data-serp-title]');
    const serpDesc = editor.querySelector('[data-serp-desc]');
    let slugTouched = slug.value.trim() !== '';
    let dirty = false;

    /* URL automática a partir del título */
    const updateSlugPreview = () => {
        editor.querySelectorAll('[data-slug-preview]').forEach((el) => { el.textContent = slug.value || slugify(title.value); });
    };
    title.addEventListener('input', () => {
        if (!slugTouched) slug.value = slugify(title.value);
        updateSlugPreview();
        updateSerp();
    });
    slug.addEventListener('input', () => { slugTouched = slug.value.trim() !== ''; updateSlugPreview(); });
    slug.addEventListener('blur', () => { slug.value = slugify(slug.value); updateSlugPreview(); });

    /* Vista previa en Google */
    const updateSerp = () => {
        serpTitle.textContent = metaTitle.value || title.value || 'Título de la entrada';
        serpDesc.textContent = metaDesc.value || excerpt.value || 'La descripción aparecerá aquí.';
    };
    [metaTitle, metaDesc, excerpt].forEach((el) => el.addEventListener('input', updateSerp));

    /* Contadores de caracteres recomendados */
    editor.querySelectorAll('[data-count]').forEach((field) => {
        const limit = Number(field.dataset.count);
        const counter = document.createElement('span');
        counter.className = 'counter';
        field.insertAdjacentElement('afterend', counter);
        const render = () => {
            const n = field.value.length;
            counter.textContent = n + ' / ' + limit + ' recomendados';
            counter.classList.toggle('is-over', n > limit);
        };
        field.addEventListener('input', render);
        render();
    });

    /* Barra de formato: envuelve la selección en etiquetas HTML */
    const insert = (before, after, placeholder) => {
        const start = content.selectionStart;
        const end = content.selectionEnd;
        const selected = content.value.slice(start, end) || placeholder;
        content.setRangeText(before + selected + after, start, end, 'end');
        content.focus();
        content.dispatchEvent(new Event('input'));
    };

    editor.querySelectorAll('[data-wrap]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tag = btn.dataset.wrap;
            const block = ['h2', 'h3', 'p', 'blockquote'].includes(tag);
            insert((block ? '\n' : '') + '<' + tag + '>', '</' + tag + '>' + (block ? '\n' : ''), 'texto');
        });
    });

    editor.querySelectorAll('[data-list]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tag = btn.dataset.list;
            const start = content.selectionStart;
            const end = content.selectionEnd;
            const lines = (content.value.slice(start, end) || 'Elemento 1\nElemento 2').split('\n').filter((l) => l.trim());
            const html = '\n<' + tag + '>\n' + lines.map((l) => '<li>' + l.trim() + '</li>').join('\n') + '\n</' + tag + '>\n';
            content.setRangeText(html, start, end, 'end');
            content.focus();
            content.dispatchEvent(new Event('input'));
        });
    });

    const linkBtn = editor.querySelector('[data-link]');
    linkBtn.addEventListener('click', () => {
        const href = window.prompt('Dirección del enlace (https://...)', 'https://');
        if (!href || href === 'https://') return;
        insert('<a href="' + href.replace(/"/g, '&quot;') + '">', '</a>', 'texto del enlace');
    });

    /* Vista previa del contenido */
    const previewBtn = editor.querySelector('[data-preview]');
    const pane = editor.querySelector('[data-preview-pane]');
    previewBtn.addEventListener('click', () => {
        const on = previewBtn.getAttribute('aria-pressed') !== 'true';
        previewBtn.setAttribute('aria-pressed', String(on));
        if (on) {
            // Vista previa sin scripts: se parsea como documento inerte
            const doc = new DOMParser().parseFromString(content.value, 'text/html');
            doc.querySelectorAll('script, style, iframe, object, embed').forEach((n) => n.remove());
            doc.querySelectorAll('*').forEach((n) => [...n.attributes].forEach((a) => { if (/^on/i.test(a.name)) n.removeAttribute(a.name); }));
            pane.innerHTML = doc.body.innerHTML;
        }
        pane.hidden = !on;
        content.hidden = on;
    });

    /* Aviso si sales sin guardar */
    editor.addEventListener('input', () => { dirty = true; });
    editor.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (e) => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    updateSlugPreview();
})();
