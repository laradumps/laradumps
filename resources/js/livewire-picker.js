(() => {
    if (window.__laradumpsLivewire) return;
    window.__laradumpsLivewire = true;

    const { endpoint, selected: picked } = document.currentScript?.dataset ?? {};
    const KEY = 'laradumps.livewire';
    const saved = (() => {
        try {
            return JSON.parse(localStorage.getItem(KEY)) || {};
        } catch {
            return {};
        }
    })();

    const selected = new Set(JSON.parse(picked || '[]'));
    let open = !!saved.open;
    let picking = false;
    let query = '';
    let hover = null;
    let rows = [];

    const save = () => {
        try {
            localStorage.setItem(KEY, JSON.stringify({ open }));
        } catch {}
    };

    const send = () =>
        endpoint &&
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ names: [...selected] })
        }).catch(() => {});

    const names = new Map();
    const nameOf = (el) => {
        const id = el.getAttribute('wire:id');
        if (!names.has(id)) {
            try {
                names.set(id, JSON.parse(el.getAttribute('wire:snapshot')).memo.name);
            } catch {
                return '?';
            }
        }
        return names.get(id);
    };

    const isVisible = (el) => {
        const opts = { checkOpacity: true, checkVisibilityCSS: true, opacityProperty: true, visibilityProperty: true };
        if (el.checkVisibility && !el.checkVisibility(opts)) return false;
        const r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0;
    };

    const scan = () =>
        [...document.querySelectorAll('[wire\\:id]')].map((el) => {
            let depth = 0;
            for (let p = el.parentElement?.closest('[wire\\:id]'); p; p = p.parentElement?.closest('[wire\\:id]')) depth++;
            return { el, name: nameOf(el), depth, visible: isVisible(el) };
        });

    const ICON = {
        bolt: '<path d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
        pick: '<path d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59"/>',
        close: '<path d="M6 18 18 6M6 6l12 12"/>'
    };
    const svg = (name) =>
        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON[name]}</svg>`;

    const host = document.createElement('laradumps-livewire');
    const root = host.attachShadow({ mode: 'open' });
    root.innerHTML = `<style>
        :host { all: initial; }
        * { box-sizing: border-box; margin: 0; }
        button { all: unset; cursor: pointer; }
        button:focus-visible, input:focus-visible { outline: 2px solid #fb70a9; outline-offset: 1px; }
        svg { width: 14px; height: 14px; flex-shrink: 0; }
        .layer { position: fixed; inset: 0; pointer-events: none; z-index: 2147483646; }
        .catcher { position: fixed; inset: 0; z-index: 2147483646; cursor: crosshair; }
        .box { position: fixed; border: 1.5px solid #fb70a9; border-radius: 3px; background: rgb(251 112 169 / .08); }
        .box.sel { border-style: dashed; background: none; }
        .box span { position: absolute; left: -1.5px; bottom: 100%; padding: 3px 6px; border-radius: 3px 3px 0 0; background: #fb70a9; color: #18181b; font: 500 11px/1 ui-monospace, SFMono-Regular, Menlo, monospace; white-space: nowrap; }
        .box[data-inside] span { bottom: auto; top: 0; border-radius: 0 0 3px 0; }
        .dock { position: fixed; right: 12px; bottom: 12px; z-index: 2147483647; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; font: 12px/1.4 system-ui, -apple-system, 'Segoe UI', sans-serif; color: #e4e4e7; color-scheme: dark; }
        .pill { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 999px; background: #18181b; border: 1px solid rgb(255 255 255 / .1); box-shadow: 0 4px 14px rgb(0 0 0 / .3); font-variant-numeric: tabular-nums; opacity: .85; }
        .pill:hover, .pill.open { opacity: 1; }
        .pill svg { color: #a1a1aa; }
        .pill.on svg { color: #fb70a9; }
        .panel { width: 300px; max-height: min(440px, 70vh); display: flex; flex-direction: column; background: #18181b; border: 1px solid rgb(255 255 255 / .1); border-radius: 10px; box-shadow: 0 12px 32px rgb(0 0 0 / .4); overflow: hidden; }
        .panel[hidden] { display: none; }
        .head { display: flex; align-items: center; gap: 4px; padding: 6px 6px 6px 10px; border-bottom: 1px solid rgb(255 255 255 / .08); }
        .head input { all: unset; flex: 1; min-width: 0; height: 24px; font: inherit; color: inherit; }
        .head input::placeholder { color: #71717a; }
        .icon { display: grid; place-items: center; width: 26px; height: 26px; border-radius: 6px; color: #a1a1aa; }
        .icon:hover { background: rgb(255 255 255 / .06); color: #e4e4e7; }
        .icon.on { color: #fb70a9; background: rgb(251 112 169 / .12); }
        .list { overflow: auto; padding: 4px; }
        .row { box-sizing: border-box; display: flex; align-items: center; gap: 8px; width: 100%; overflow: hidden; height: 26px; padding-right: 8px; border-radius: 6px; font: 12px/1 ui-monospace, SFMono-Regular, Menlo, monospace; color: #d4d4d8; }
        .row:hover { background: rgb(255 255 255 / .06); }
        .row i { width: 12px; height: 12px; border-radius: 3px; border: 1.5px solid #52525b; flex-shrink: 0; }
        .row[aria-selected='true'] i { background: #fb70a9; border-color: #fb70a9; }
        .row .name { display: flex; min-width: 0; white-space: nowrap; }
        .row .name b { min-width: 0; overflow: hidden; text-overflow: ellipsis; font-weight: inherit; color: #71717a; }
        .row em { flex-shrink: 0; margin-left: auto; padding: 2px 5px; border-radius: 4px; background: rgb(255 255 255 / .06); color: #a1a1aa; font: 10px/1 system-ui, sans-serif; font-style: normal; }
        .empty { padding: 14px 10px; color: #71717a; text-align: center; }
        .foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 10px; border-top: 1px solid rgb(255 255 255 / .08); color: #a1a1aa; font-size: 11px; }
        .foot button { color: #fb70a9; }
        .foot button[hidden] { display: none; }
    </style>
    <div class="layer"></div>
    <div class="dock">
        <div class="panel" role="dialog" aria-label="Livewire components">
            <div class="head">
                <input type="search" placeholder="Filter components" spellcheck="false" autocomplete="off">
                <button class="icon pick" title="Pick on page (Alt+Shift+L)" aria-label="Pick on page">${svg('pick')}</button>
                <button class="icon close" title="Close" aria-label="Close">${svg('close')}</button>
            </div>
            <div class="list" role="listbox" aria-multiselectable="true"></div>
            <div class="foot"><span class="hint"></span><button class="clear">Clear</button></div>
        </div>
        <button class="pill" title="LaraDumps · Livewire (Alt+Shift+L picks on page)">${svg('bolt')}<span class="count"></span></button>
    </div>`;

    const $ = (selector) => root.querySelector(selector);
    const layer = $('.layer');
    const panel = $('.panel');
    const list = $('.list');
    const pill = $('.pill');
    const input = $('input');
    const catcher = document.createElement('div');
    catcher.className = 'catcher';

    const box = (el, name, kind) => {
        const r = el.getBoundingClientRect();
        const div = document.createElement('div');
        div.className = `box ${kind}`;
        Object.assign(div.style, { left: `${r.left}px`, top: `${r.top}px`, width: `${r.width}px`, height: `${r.height}px` });
        if (name) {
            const label = document.createElement('span');
            label.textContent = name;
            div.append(label);
            div.toggleAttribute('data-inside', r.top < 20);
        }
        return div;
    };

    const paint = () => {
        const boxes = open || picking ? rows.filter((c) => c.visible && selected.has(c.name)).map((c) => box(c.el, null, 'sel')) : [];
        if (hover?.isConnected && isVisible(hover)) boxes.push(box(hover, nameOf(hover), 'hover'));
        layer.replaceChildren(...boxes);
    };

    const row = (c) => {
        const button = document.createElement('button');
        button.className = 'row';
        button.setAttribute('role', 'option');
        button.setAttribute('aria-selected', String(selected.has(c.name)));
        button.style.paddingLeft = `${8 + c.depth * 12}px`;
        button.innerHTML = `<i></i><span class="name"></span>${c.visible ? '' : '<em>hidden</em>'}`;
        const dot = c.name.lastIndexOf('.') + 1;
        const name = button.querySelector('.name');
        name.innerHTML = '<b></b>';
        name.firstChild.textContent = c.name.slice(0, dot);
        name.append(c.name.slice(dot));
        button.title = c.visible ? c.name : `${c.name} is not visible right now`;
        button.onmouseenter = () => ((hover = c.el), paint());
        button.onmouseleave = () => ((hover = null), paint());
        button.onclick = () => toggle(c.name);
        return button;
    };

    const render = () => {
        rows = scan();
        $('.count').textContent = selected.size ? `${selected.size}/${rows.length}` : rows.length;
        pill.classList.toggle('on', selected.size > 0);
        pill.classList.toggle('open', open);
        pill.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
        $('.pick').classList.toggle('on', picking);

        if (open) {
            const q = query.toLowerCase();
            const shown = rows.filter((c) => !q || c.name.toLowerCase().includes(q));
            list.replaceChildren(...shown.map(row));
            if (!shown.length) list.innerHTML = '<div class="empty">No components</div>';
            $('.hint').textContent = selected.size ? `Sending ${selected.size} to LaraDumps` : 'Pick components to send to LaraDumps';
            $('.clear').hidden = !selected.size;
        }

        paint();
    };

    const frames = new Map();
    const schedule = (fn = render) => {
        cancelAnimationFrame(frames.get(fn));
        frames.set(fn, requestAnimationFrame(fn));
    };

    const toggle = (name) => {
        selected.has(name) ? selected.delete(name) : selected.add(name);
        save();
        send();
        render();
    };

    const setOpen = (value) => {
        open = value;
        save();
        render();
        if (open) input.focus({ preventScroll: true });
    };

    const setPicking = (value) => {
        picking = value;
        hover = null;
        picking ? root.append(catcher) : catcher.remove();
        render();
    };

    const under = (e) => document.elementsFromPoint(e.clientX, e.clientY).find((n) => n !== host)?.closest('[wire\\:id]') ?? null;
    catcher.addEventListener('mousemove', (e) => {
        const el = under(e);
        if (el !== hover) ((hover = el), paint());
    });
    catcher.addEventListener('click', (e) => {
        const el = under(e);
        if (el) toggle(nameOf(el));
        if (!e.shiftKey) setPicking(false);
    });

    pill.onclick = () => setOpen(!open);
    $('.close').onclick = () => setOpen(false);
    $('.pick').onclick = () => setPicking(!picking);
    $('.clear').onclick = () => {
        selected.clear();
        save();
        send();
        render();
    };
    input.oninput = () => ((query = input.value), render());

    addEventListener(
        'keydown',
        (e) => {
            if (e.altKey && e.shiftKey && e.code === 'KeyL') {
                e.preventDefault();
                e.stopImmediatePropagation();
                setPicking(!picking);
            } else if (e.key === 'Escape' && picking) {
                e.preventDefault();
                e.stopImmediatePropagation();
                setPicking(false);
            }
        },
        true
    );

    const onMove = () => (open || picking || hover) && schedule(paint);
    addEventListener('scroll', onMove, { capture: true, passive: true });
    addEventListener('resize', onMove, { passive: true });

    const hook = () => window.Livewire?.hook?.('commit', ({ succeed }) => succeed(() => schedule()));
    window.Livewire ? hook() : document.addEventListener('livewire:init', hook);
    document.addEventListener('livewire:navigated', () => schedule());

    document.documentElement.append(host);
    render();
})();
