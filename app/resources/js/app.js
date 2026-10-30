/**
 * Small progressive enhancements for the auth forms. Every form still works
 * without JavaScript; these only add comfort on top.
 */

// Show or hide the password in fields rendered by <x-form.input type="password">.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (!toggle) {
        return;
    }

    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    const isHidden = input.type === 'password';

    input.type = isHidden ? 'text' : 'password';
    toggle.textContent = isHidden ? 'Hide' : 'Show';
    toggle.setAttribute('aria-pressed', String(isHidden));
    input.focus();
});

// Warn when Caps Lock is on while typing a password.
const updateCapsWarning = (event) => {
    const field = event.target.closest?.('[data-password-field]');

    if (!field || typeof event.getModifierState !== 'function') {
        return;
    }

    field.querySelector('[data-caps-warning]').hidden = !event.getModifierState('CapsLock');
};

document.addEventListener('keydown', updateCapsWarning);
document.addEventListener('keyup', updateCapsWarning);

// Stop double submits and show that the form is working.
document.addEventListener('submit', (event) => {
    const button = event.target.querySelector('button[type="submit"][data-busy-label]');

    if (!button) {
        return;
    }

    if (button.disabled) {
        event.preventDefault();

        return;
    }

    const text = button.querySelector('[data-busy-text]');

    button.dataset.idleLabel = text.textContent;
    button.disabled = true;
    button.querySelector('[data-busy-spinner]').hidden = false;
    text.textContent = button.dataset.busyLabel;
});

// A form marked data-confirm="<dialog id>" asks first: submitting opens that dialog, and
// only its data-confirm-accept button sends the form. Runs before the handlers above.
document.addEventListener('submit', (event) => {
    const form = event.target;
    const dialog = form.dataset.confirm && document.getElementById(form.dataset.confirm);

    if (!dialog || 'confirmed' in form.dataset) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    dialog.querySelector('[data-confirm-accept]').onclick = () => {
        dialog.close();
        form.dataset.confirmed = '';
        form.requestSubmit();
        delete form.dataset.confirmed;
    };
    dialog.showModal();
}, true);

// Close a dialog from its cancel button, or by clicking the dimmed page around it.
document.addEventListener('click', (event) => {
    const dialog = event.target.closest('[data-dialog-close]')?.closest('dialog')
        ?? (event.target instanceof HTMLDialogElement ? event.target : null);

    dialog?.close();
});

// Reset the button if the browser restores the page from its back/forward cache.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('button[data-busy-label][disabled]').forEach((button) => {
        button.disabled = false;
        button.querySelector('[data-busy-spinner]').hidden = true;
        button.querySelector('[data-busy-text]').textContent = button.dataset.idleLabel;
    });
});

// A select whose choices depend on another one (data-controls="<id>"): show only the
// options marked data-for the chosen value, and pick the first one that can be picked.
document.addEventListener('change', (event) => {
    const source = event.target.closest('select[data-controls]');
    const target = source && document.getElementById(source.dataset.controls);

    if (!target) {
        return;
    }

    const options = [...target.querySelectorAll('option[data-for]')];

    options.forEach((option) => {
        const belongs = option.dataset.for === source.value;

        option.hidden = !belongs;
        option.disabled = !belongs || 'taken' in option.dataset;
    });

    target.disabled = source.value === '';
    target.value = options.find((option) => !option.hidden && !option.disabled)?.value ?? '';
    target.options[0].textContent = target.disabled ? target.dataset.waitingLabel : target.dataset.readyLabel;
    target.dispatchEvent(new Event('change', { bubbles: true }));
});

// Every select opens a list drawn by the page instead of the browser's own, so it matches
// the rest of the app, and a choice can carry a second line (data-hint) or say why it
// can't be picked (data-note). The select stays in the form and keeps the value; mark one
// data-native to leave it to the browser.
const element = (tag, className, text) => {
    const node = document.createElement(tag);
    node.className = className;

    if (text) {
        node.textContent = text;
    }

    return node;
};

const icon = (className, paths) => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');

    Object.entries({ viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'aria-hidden': 'true', class: className })
        .forEach(([name, value]) => svg.setAttribute(name, value));
    svg.innerHTML = paths;

    return svg;
};

// Classes that place the field in its row go on the wrapper; the rest style the button.
const isLayoutClass = (name) => /^(?:[a-z0-9-]+:)*(?:w-|min-w-|max-w-|flex-(?:1|none|auto|initial)$|shrink|grow|basis-|self-|m[trblxy]?-|col-|row-|order-)/.test(name);

let listboxCount = 0;

const enhanceListbox = (select) => {
    select.id ||= `select-${++listboxCount}`;

    const label = document.querySelector(`label[for="${select.id}"]`);
    const classes = [...select.classList];
    const wrapper = element('div', ['relative', ...classes.filter(isLayoutClass)].join(' '));
    const button = element('button', [...classes.filter((name) => !isLayoutClass(name) && name !== 'block'), 'flex w-full items-center gap-3 text-left disabled:cursor-not-allowed disabled:bg-mist disabled:text-slate-muted'].join(' '));
    const buttonText = element('span', 'min-w-0 flex-1 truncate');
    const list = element('ul', 'fixed z-50 max-h-72 overflow-y-auto overscroll-contain rounded-[10px] border border-line bg-white p-1.5 text-sm text-navy shadow-lg shadow-navy/10 outline-none');
    let choices = [];
    let active = -1;
    let typed = '';
    let typedAt = 0;

    button.type = 'button';
    button.id = `${select.id}-button`;
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    button.append(buttonText, icon('h-4 w-4 shrink-0 text-slate-muted', '<path d="m6 9 6 6 6-6"/>'));

    if (label) {
        label.id ||= `${select.id}-label`;
        button.setAttribute('aria-labelledby', `${label.id} ${button.id}`);
        label.addEventListener('click', (event) => {
            event.preventDefault();
            button.focus();
        });
    } else if (select.hasAttribute('aria-label')) {
        button.setAttribute('aria-label', select.getAttribute('aria-label'));
    }

    if (select.hasAttribute('aria-describedby')) {
        button.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
    }

    if (select.hasAttribute('aria-invalid')) {
        button.setAttribute('aria-invalid', select.getAttribute('aria-invalid'));
    }

    list.id = `${select.id}-list`;
    list.tabIndex = -1;
    list.hidden = true;
    list.setAttribute('role', 'listbox');
    button.setAttribute('aria-controls', list.id);

    if (label) {
        list.setAttribute('aria-labelledby', label.id);
    }

    select.className = 'sr-only';
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    select.after(wrapper);
    wrapper.append(button, list);

    if (select.autofocus) {
        button.focus();
    }

    // A required select left empty: point the browser's message at the button instead.
    select.addEventListener('invalid', () => button.focus());

    const highlight = (index) => {
        active = index;
        choices.forEach(({ row }, position) => row.toggleAttribute('data-active', position === index));
        list.setAttribute('aria-activedescendant', choices[index]?.row.id ?? '');
        choices[index]?.row.scrollIntoView({ block: 'nearest' });
    };

    // Sit under the button, or above it when there isn't room below.
    const place = () => {
        const box = button.getBoundingClientRect();
        const below = window.innerHeight - box.bottom - 8;
        const height = Math.min(list.scrollHeight, 288);

        list.style.left = `${Math.max(8, Math.min(box.left, window.innerWidth - Math.max(box.width, list.offsetWidth) - 8))}px`;
        list.style.minWidth = `${box.width}px`;
        list.style.maxWidth = `${Math.max(box.width, Math.min(352, window.innerWidth - 16))}px`;
        list.style.top = below < height && box.top > below ? `${box.top - height - 6}px` : `${box.bottom + 6}px`;
    };

    const close = (refocus = true) => {
        if (list.hidden) {
            return;
        }

        list.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', onScroll);

        if (refocus) {
            button.focus();
        }
    };

    const onScroll = (event) => {
        if (!list.contains(event.target)) {
            close(false);
        }
    };

    const choose = (index) => {
        const choice = choices[index];

        if (!choice || choice.option.disabled) {
            return;
        }

        const changed = select.value !== choice.option.value;

        select.value = choice.option.value;
        close();

        if (changed) {
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const move = (step) => {
        for (let index = active + step; index >= 0 && index < choices.length; index += step) {
            if (!choices[index].option.disabled) {
                highlight(index);

                return;
            }
        }
    };

    // Typing letters jumps to the first choice that starts with them, as a select does.
    const jumpTo = (key) => {
        typed = Date.now() - typedAt > 700 ? key : typed + key;
        typedAt = Date.now();

        const index = choices.findIndex(({ option, text }) => !option.disabled && text.toLowerCase().startsWith(typed.toLowerCase()));

        if (index !== -1) {
            highlight(index);
        }
    };

    const render = () => {
        const chosen = select.selectedOptions[0];
        const isEmpty = !chosen || (chosen.value === '' && chosen.disabled);

        button.disabled = select.disabled;
        buttonText.textContent = chosen?.dataset.label ?? chosen?.textContent.trim() ?? '';
        buttonText.classList.toggle('text-slate-muted', isEmpty);
        choices = [];

        const rows = [...select.children].flatMap((child) => {
            if (child instanceof HTMLOptGroupElement) {
                const options = [...child.children].filter((option) => !option.hidden);

                return options.length ? [element('li', 'px-3 pb-1 pt-2.5 text-xs font-medium text-slate-muted first:pt-1', child.label), ...options] : [];
            }

            return child.hidden || (child.value === '' && child.disabled) ? [] : [child];
        }).map((item) => {
            if (!(item instanceof HTMLOptionElement)) {
                item.setAttribute('role', 'presentation');

                return item;
            }

            const option = item;
            const index = choices.length;
            const hasHint = Boolean(option.dataset.hint);
            const text = option.dataset.label ?? option.textContent.trim();
            const row = element('li', `flex items-center gap-3 rounded-[8px] px-3 ${hasHint ? 'py-2.5' : 'py-2'} data-active:bg-mist`);
            const body = element('span', 'min-w-0 flex-1');

            row.id = `${list.id}-${index}`;
            row.setAttribute('role', 'option');
            row.setAttribute('aria-selected', String(option === chosen));
            row.classList.add(option.disabled ? 'cursor-default' : 'cursor-pointer');

            if (option.disabled) {
                row.setAttribute('aria-disabled', 'true');
            }

            row.append(option === chosen ? icon('h-4 w-4 shrink-0 text-emerald', '<path d="M20 6 9 17l-5-5"/>') : element('span', 'h-4 w-4 shrink-0'));
            body.append(element('span', [
                'block',
                hasHint ? 'font-display text-base font-semibold tabular-nums' : (option === chosen ? 'font-semibold' : ''),
                option.disabled ? 'text-slate-muted' : 'text-navy',
            ].join(' '), text));

            if (hasHint) {
                body.append(element('span', 'block text-xs text-slate-muted', option.dataset.hint));
            }

            row.append(body);

            if (option.dataset.note) {
                const note = element('span', 'flex shrink-0 items-center gap-1.5 text-xs font-medium text-emerald-dark');

                note.append(icon('h-4 w-4', '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="m9 14 2 2 4-4"/>'), option.dataset.note);
                row.append(note);
            }

            row.addEventListener('mousemove', () => highlight(option.disabled ? -1 : index));
            row.addEventListener('click', () => choose(index));
            choices.push({ option, row, text });

            return row;
        });

        list.replaceChildren(...rows);
    };

    const open = () => {
        if (button.disabled) {
            return;
        }

        document.dispatchEvent(new CustomEvent('listbox:open', { detail: list }));
        list.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        place();
        list.focus({ preventScroll: true });
        highlight(choices.findIndex(({ option }) => option.selected && !option.disabled));

        if (active === -1) {
            move(1);
        }

        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', onScroll);
    };

    button.addEventListener('click', () => (list.hidden ? open() : close()));
    button.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            open();
        }
    });
    list.addEventListener('keydown', (event) => {
        const keys = {
            ArrowDown: () => move(1),
            ArrowUp: () => move(-1),
            Home: () => { active = -1; move(1); },
            End: () => { active = choices.length; move(-1); },
            Enter: () => choose(active),
            ' ': () => choose(active),
            Escape: () => close(),
        };

        if (event.key === 'Tab') {
            close(false);
        } else if (keys[event.key]) {
            event.preventDefault();
            keys[event.key]();
        } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
            jumpTo(event.key);
        }
    });
    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            close(false);
        }
    });
    document.addEventListener('listbox:open', (event) => {
        if (event.detail !== list) {
            close(false);
        }
    });
    select.addEventListener('change', render);
    select.form?.addEventListener('reset', () => setTimeout(render));
    render();
};

document.querySelectorAll('select:not([multiple]):not([size]):not([data-native])').forEach(enhanceListbox);

// Sections marked <details data-animate> slide open and shut instead of jumping, and their
// contents fade in. data-expanded follows what the person asked for straight away, so the
// arrow turns with the click rather than when the closing slide ends.
document.addEventListener('click', (event) => {
    const summary = event.target.closest('details[data-animate] > summary');

    if (!summary || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    event.preventDefault();

    const details = summary.parentElement;
    const opening = !details.open || details.dataset.closing === 'true';
    const from = details.offsetHeight;

    details.slide?.cancel();
    details.toggleAttribute('data-expanded', opening);
    details.dataset.closing = String(!opening);
    details.open = true;

    const borders = details.offsetHeight - details.clientHeight;
    const to = opening ? details.offsetHeight : summary.offsetHeight + borders;

    details.slide = details.animate({ height: [`${from}px`, `${to}px`] }, { duration: opening ? 320 : 240, easing: 'cubic-bezier(0.2, 0, 0, 1)' });

    if (opening) {
        details.querySelectorAll(':scope > :not(summary):not([hidden])').forEach((part) => part.animate(
            { opacity: [0, 1], transform: ['translateY(-6px)', 'translateY(0)'] },
            { duration: 260, delay: 60, easing: 'ease-out', fill: 'backwards' },
        ));
    }

    details.slide.onfinish = () => {
        details.open = opening;
        delete details.dataset.closing;
        details.slide = null;
    };
});

// Without the slide (reduced motion), keep data-expanded in step with the native toggle.
document.addEventListener('toggle', (event) => {
    if (event.target.matches?.('details[data-animate]') && !event.target.slide) {
        event.target.toggleAttribute('data-expanded', event.target.open);
    }
}, true);

// On phones and tablets the side menu is a drawer: the menu button slides it in, and the
// close button, the dimmed page behind it or Escape slide it away.
const drawer = document.getElementById('sidebar');
const drawerBackdrop = document.querySelector('[data-drawer-backdrop]');
const drawerOpener = document.querySelector('[data-drawer-open]');

const setDrawer = (open) => {
    if (!drawer || !drawerBackdrop) {
        return;
    }

    drawer.toggleAttribute('data-open', open);
    drawerBackdrop.toggleAttribute('data-open', open);
    drawerOpener?.setAttribute('aria-expanded', String(open));
    document.documentElement.classList.toggle('overflow-hidden', open);

    // Visible at once when opening, so its close button can take focus; when closing, it
    // stays visible until the slide ends.
    if (open) {
        drawer.style.transitionProperty = 'translate';
        drawer.style.visibility = 'visible';
        drawer.querySelector('[data-drawer-close]')?.focus();
    } else {
        drawer.style.removeProperty('transition-property');
        drawer.style.removeProperty('visibility');
    }
};

drawerOpener?.addEventListener('click', () => setDrawer(true));
drawerBackdrop?.addEventListener('click', () => {
    setDrawer(false);
    drawerOpener?.focus();
});
drawer?.querySelector('[data-drawer-close]')?.addEventListener('click', () => {
    setDrawer(false);
    drawerOpener?.focus();
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer?.hasAttribute('data-open')) {
        setDrawer(false);
        drawerOpener?.focus();
    }
});
window.matchMedia('(min-width: 64rem)').addEventListener('change', (event) => event.matches && setDrawer(false));

// Fold the workspace side menu down to icons, and remember the choice in this browser.
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

const showSidebarState = () => {
    const isCollapsed = document.documentElement.dataset.sidebar === 'collapsed';

    sidebarToggle.setAttribute('aria-pressed', String(isCollapsed));
    sidebarToggle.title = isCollapsed ? 'Expand menu' : 'Collapse menu';
    sidebarToggle.querySelector('[data-sidebar-label]').textContent = sidebarToggle.title;
};

if (sidebarToggle) {
    showSidebarState();

    sidebarToggle.addEventListener('click', () => {
        const isCollapsed = document.documentElement.dataset.sidebar === 'collapsed';

        if (isCollapsed) {
            delete document.documentElement.dataset.sidebar;
        } else {
            document.documentElement.dataset.sidebar = 'collapsed';
        }

        try {
            localStorage.setItem('sk.sidebar', isCollapsed ? 'expanded' : 'collapsed');
        } catch (error) {
            // Private browsing or blocked storage: the menu still folds, it just isn't remembered.
        }

        showSidebarState();
    });
}

// Share a page: the phone's share sheet where there is one, otherwise copy the link.
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-share]');

    if (!button) {
        return;
    }

    const label = button.querySelector('[data-share-label]');
    const url = button.dataset.shareUrl;

    if (navigator.share) {
        try {
            await navigator.share({ title: button.dataset.shareTitle, url });
        } catch (error) {
            // The person closed the share sheet; nothing to do.
        }

        return;
    }

    const original = label.dataset.idle ?? label.textContent;
    label.dataset.idle = original;

    try {
        await navigator.clipboard.writeText(url);
        label.textContent = 'Link copied';
        window.toast?.('Link copied. Paste it anywhere to share.', 'info');
    } catch (error) {
        window.prompt('Copy this link:', url);

        return;
    }

    setTimeout(() => {
        label.textContent = original;
    }, 2500);
});

// Header dropdowns (notifications, account, mobile menu): one open at a time,
// and they close on a click outside or the Escape key.
const dropdowns = () => document.querySelectorAll('details[data-dropdown][open]');

document.addEventListener('click', (event) => {
    dropdowns().forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    dropdowns().forEach((dropdown) => {
        dropdown.open = false;
        dropdown.querySelector('summary')?.focus();
    });
});

// Notification tabs in the bell: show one kind at a time without leaving the page.
document.addEventListener('click', (event) => {
    const tab = event.target.closest('[data-notification-filter]');

    if (!tab) {
        return;
    }

    const panel = tab.closest('[data-notification-panel]');
    const category = tab.dataset.notificationFilter;

    panel.querySelectorAll('[data-notification-filter]').forEach((other) => {
        other.setAttribute('aria-pressed', String(other === tab));
    });

    panel.querySelectorAll('li[data-category]').forEach((item) => {
        item.hidden = category !== 'all' && item.dataset.category !== category;
    });
});

// Toasts: messages that swipe in at the top right and leave on their own. Each kind
// (success, info, warning, error) stays up for its own time, shown by the line at its
// bottom; the close button dismisses one early.
const dismissToast = (toast) => {
    if (!toast || toast.dataset.leaving) {
        return;
    }

    toast.dataset.leaving = 'true';
    clearTimeout(toast.toastTimer);
    toast.addEventListener('animationend', (event) => {
        // Only the toast's own exit, not the time-left line inside it.
        if (event.target === toast) {
            toast.remove();
        }
    });
    toast.classList.add('toast-leaving');

    // No animation (reduced motion): remove straight away.
    if (getComputedStyle(toast).animationName === 'none') {
        toast.remove();
    }
};

const watchToast = (toast) => {
    toast.toastTimer = setTimeout(() => dismissToast(toast), Number(toast.dataset.duration) || 5000);
};

document.querySelectorAll('[data-toaster] [data-toast]').forEach(watchToast);

document.addEventListener('click', (event) => {
    const close = event.target.closest('[data-toast-close]');

    if (close) {
        dismissToast(close.closest('[data-toast]'));
    }
});

window.toast = (message, kind = 'success') => {
    const toaster = document.querySelector('[data-toaster]');
    const template = document.querySelector(`[data-toast-template="${kind}"]`) ?? document.querySelector('[data-toast-template="success"]');

    if (!toaster || !template) {
        return;
    }

    const toast = template.content.firstElementChild.cloneNode(true);
    toast.querySelector('[data-toast-message]').textContent = message;
    toaster.append(toast);

    // Keep at most three on screen.
    [...toaster.querySelectorAll('[data-toast]:not([data-leaving])')].slice(0, -3).forEach(dismissToast);
    watchToast(toast);
};

// The announcement form's preview: the post card beside the form follows what's typed,
// the kind picked and the image chosen, the way it will show on the portal.
const announcementForm = document.querySelector('[data-announcement-form]');

if (announcementForm) {
    const field = (name) => announcementForm.elements.namedItem(name);
    const title = field('title');
    const body = field('body');
    const image = field('image');
    const removeImage = announcementForm.querySelector('[data-remove-image]');
    const previewImage = announcementForm.querySelector('[data-preview-image]');
    const fileName = announcementForm.querySelector('[data-file-name]');
    const clearImage = announcementForm.querySelector('[data-clear-image]');
    const dropzone = announcementForm.querySelector('[data-dropzone]');
    const importedImage = announcementForm.querySelector('[data-imported-image]');
    const importer = announcementForm.querySelector('[data-facebook-import]');
    let importedPreview = null;
    const detailLine = /^\s*(🗓️?|📅|⏰|[\u{1F550}-\u{1F567}]|📍)\s*(.+)$/u;
    let objectUrl = null;

    const detailKind = (marker) => (marker.startsWith('📍') ? 'place' : (marker.startsWith('🗓') || marker.startsWith('📅') ? 'date' : 'time'));

    const showCounts = () => {
        announcementForm.querySelectorAll('[data-count-for]').forEach((counter) => {
            const input = field(counter.dataset.countFor);
            counter.textContent = `${input.value.length.toLocaleString()} / ${Number(input.maxLength).toLocaleString()}`;
        });
    };

    const showText = () => {
        const heading = announcementForm.querySelector('[data-preview-title]');
        heading.textContent = title.value.trim() || heading.dataset.placeholder;
        heading.classList.toggle('text-slate-muted', title.value.trim() === '');

        const lines = body.value.split(/\r?\n/);
        const details = lines.map((line) => line.match(detailLine)).filter(Boolean).map((match) => ({ kind: detailKind(match[1]), text: match[2].trim() }));
        const caption = lines.filter((line) => !detailLine.test(line)).join('\n').replace(/\n{3,}/g, '\n\n').trim();
        const excerpt = caption.length > 180 ? `${caption.slice(0, caption.lastIndexOf(' ', 180) > 120 ? caption.lastIndexOf(' ', 180) : 180)}…` : caption;

        const detailList = announcementForm.querySelector('[data-preview-details]');
        detailList.replaceChildren(...details.map((detail) => {
            const row = document.createElement('div');
            const term = document.createElement('dt');
            const value = document.createElement('dd');

            row.className = 'flex gap-2';
            term.className = 'w-11 shrink-0 text-xs font-medium leading-5 text-emerald';
            term.textContent = { date: 'Date', time: 'Time', place: 'Place' }[detail.kind];
            value.className = 'font-medium text-navy';
            value.textContent = detail.text;
            row.append(term, value);

            return row;
        }));
        detailList.hidden = details.length === 0;

        const captionText = announcementForm.querySelector('[data-preview-caption]');
        captionText.replaceChildren(...(excerpt || captionText.dataset.placeholder).split(/(#[\p{L}\p{N}_]+)/u).map((part) => {
            if (!part.startsWith('#')) {
                return document.createTextNode(part);
            }

            const tag = document.createElement('span');
            tag.className = 'font-medium text-emerald';
            tag.textContent = part;

            return tag;
        }));
        captionText.classList.toggle('text-slate-muted', excerpt === '');

        const date = details.find((detail) => detail.kind === 'date')?.text ?? '';
        announcementForm.querySelectorAll('[data-poster-date]').forEach((line) => {
            line.textContent = date;
            line.hidden = date === '';
        });
    };

    const showCover = () => {
        const chosen = announcementForm.querySelector('input[name="category"]:checked')?.value;
        const source = objectUrl ?? importedPreview ?? (removeImage?.checked ? '' : previewImage.dataset.savedImage);

        previewImage.hidden = !source;
        previewImage.src = source || '';
        announcementForm.querySelectorAll('[data-poster]').forEach((poster) => {
            poster.hidden = Boolean(source) || poster.dataset.poster !== chosen;
        });
    };

    const showFile = () => {
        const file = image.files[0];

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }

        objectUrl = file ? URL.createObjectURL(file) : null;

        // A photo picked by hand replaces one brought over from Facebook.
        if (file) {
            importedPreview = null;
            importedImage.value = '';
        }

        fileName.textContent = file ? file.name : (importedPreview ? 'Photo from the Facebook post' : fileName.dataset.empty);
        clearImage.hidden = !file && !importedPreview;
        showCover();
        photoChanged();
    };

    announcementForm.addEventListener('input', (event) => {
        if (event.target === title || event.target === body) {
            showText();
            showCounts();
        }
    });

    announcementForm.addEventListener('change', (event) => {
        if (event.target.name === 'category' || event.target === removeImage) {
            showCover();
        } else if (event.target === image) {
            showFile();
        } else if (event.target.matches('[data-preview-pin-toggle]')) {
            announcementForm.querySelector('[data-preview-pin]').hidden = !event.target.checked;
        }
    });

    clearImage.addEventListener('click', () => {
        image.value = '';
        importedPreview = null;
        importedImage.value = '';
        showFile();
    });

    // Bring a post over from the SK's connected Facebook Page: the server reads it, and the
    // form fills in for the officer to check before posting.
    if (importer) {
        const link = importer.querySelector('input');
        const button = importer.querySelector('[data-import-button]');
        const status = announcementForm.querySelector('[data-import-status]');

        const say = (message, isError = false) => {
            status.textContent = message;
            status.className = `mt-2 min-h-5 text-sm ${isError ? 'text-error' : 'text-emerald'}`;
        };

        const setValue = (name, value) => {
            const input = field(name);
            input.value = value ?? '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const bringOver = async () => {
            if (link.value.trim() === '' || button.disabled) {
                return say('Paste the link to the Facebook post first.', true);
            }

            button.disabled = true;
            importer.querySelector('[data-import-spinner]').classList.remove('hidden');
            importer.querySelector('[data-import-label]').textContent = 'Reading the post…';
            say('');

            try {
                const response = await fetch(importer.dataset.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': field('_token').value },
                    body: JSON.stringify({ url: link.value.trim() }),
                });
                const post = await response.json().catch(() => ({}));

                if (!response.ok) {
                    say(post.errors?.url?.[0] ?? post.message ?? 'The post couldn’t be brought over. Try again.', true);

                    return;
                }

                announcementForm.querySelector(`input[name="category"][value="${post.category}"]`).checked = true;
                setValue('title', post.title);
                setValue('body', post.body);
                setValue('source_url', post.source_url);
                setValue('originally_posted_at', post.originally_posted_at);
                field('source_url').closest('details').open = true;

                image.value = '';
                objectUrl = null;
                importedPreview = post.image?.preview ?? null;
                importedImage.value = post.image?.token ?? '';
                showFile();

                say(post.image
                    ? 'Brought over with its photo. Check the headline and kind, then post it.'
                    : 'Brought over. The post had no photo, so it gets a poster. Check the headline and kind, then post it.');
                field('title').focus();
            } catch {
                say('The post couldn’t be brought over. Check the internet connection and try again.', true);
            } finally {
                button.disabled = false;
                importer.querySelector('[data-import-spinner]').classList.add('hidden');
                importer.querySelector('[data-import-label]').textContent = 'Bring it over';
            }
        };

        button.addEventListener('click', bringOver);
        link.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                bringOver();
            }
        });
        link.addEventListener('paste', () => setTimeout(bringOver));
    }

    // Date, time and place buttons start a new caption line with their marker.
    announcementForm.querySelectorAll('[data-insert-line]').forEach((button) => {
        button.addEventListener('click', () => {
            const at = body.selectionStart ?? body.value.length;
            const before = body.value.slice(0, at);
            const prefix = before === '' || before.endsWith('\n') ? '' : '\n';
            const line = `${prefix}${button.dataset.insertLine}`;

            body.focus();
            body.setRangeText(line, at, body.selectionEnd ?? at, 'end');
            body.placeholder = body.value === line ? `${button.dataset.insertLine}${button.dataset.example}` : body.placeholder;
            body.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    ['dragenter', 'dragover'].forEach((type) => dropzone.addEventListener(type, (event) => {
        event.preventDefault();
        dropzone.toggleAttribute('data-dragging', true);
    }));
    ['dragleave', 'drop'].forEach((type) => dropzone.addEventListener(type, () => dropzone.toggleAttribute('data-dragging', false)));
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();

        if (event.dataTransfer.files.length) {
            image.files = event.dataTransfer.files;
            showFile();
        }
    });

    // Told when the photo changes, so the Facebook checklist can tick it off.
    let photoChanged = () => {};

    // A photo pasted with Ctrl + V anywhere on the form becomes the post's image.
    announcementForm.addEventListener('paste', (event) => {
        const picture = [...(event.clipboardData?.files ?? [])].find((file) => ['image/jpeg', 'image/png', 'image/webp'].includes(file.type));

        if (!picture) {
            return;
        }

        event.preventDefault();

        const files = new DataTransfer();
        const extension = picture.type.split('/')[1].replace('jpeg', 'jpg');
        files.items.add(new File([picture], `pasted-photo.${extension}`, { type: picture.type }));
        image.files = files.files;
        showFile();
        fileName.textContent = 'Photo pasted from the clipboard';
    });

    // "Paste from Facebook": clean a post copied off Facebook and fill the form with it.
    const pastePanel = announcementForm.querySelector('[data-facebook-paste]');

    if (pastePanel) {
        const pasted = pastePanel.querySelector('#facebook_text');
        const pastedLink = pastePanel.querySelector('#facebook_post_link');
        const status = pastePanel.querySelector('[data-paste-status]');
        const months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        // Facebook's buttons, counters and menus that come along when a post is selected and copied.
        const clutter = [
            /^(like|love|comment|share|send|reply|follow|following|see more|see less|see translation|see original|rate this translation|all reactions:?|most relevant|newest|all comments|top fan|author|edited|hide|public|shared with public|sponsored|·)$/i,
            /^(write|view|see) (a |all |more |previous )?.*(comment|replies|reply)s?…?\.?$/i,
            /^[^\p{L}\p{N}]*[\d.,]+\s*[km]?$/iu,
            /^[\d.,]+\s*[km]?\s*(comments?|shares?|reactions?|views?|likes?|reacts?|plays?)$/i,
            /^(you|and)\b.*\bothers?$/i,
        ];

        // "2d", "Yesterday at 3:15 PM", "October 28 at 10:00 AM": when it went up on Facebook.
        const readTimestamp = (line) => {
            const text = line.replace(/[^\p{L}\p{N}:, ]/gu, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
            const now = new Date();
            let match;

            if (/^just now$/.test(text)) {
                return now;
            }

            if ((match = text.match(/^(\d+)\s*(m|min|mins|h|hr|hrs|d|w|y)$/))) {
                const minutes = { m: 1, min: 1, mins: 1, h: 60, hr: 60, hrs: 60, d: 1440, w: 10080, y: 525600 }[match[2]];

                return new Date(now.getTime() - Number(match[1]) * minutes * 60000);
            }

            const clock = (date, time) => {
                const parts = time?.match(/(\d{1,2}):(\d{2})\s*(am|pm)/);

                if (parts) {
                    date.setHours((Number(parts[1]) % 12) + (parts[3] === 'pm' ? 12 : 0), Number(parts[2]), 0, 0);
                }

                return date;
            };

            if ((match = text.match(/^yesterday(?: at (.+))?$/))) {
                const date = new Date(now);
                date.setDate(date.getDate() - 1);

                return clock(date, match[1]);
            }

            if ((match = text.match(/^([a-z]+) (\d{1,2})(?:, (\d{4}))?(?: at (.+))?$/)) && months.some((month) => month.startsWith(match[1].slice(0, 3)))) {
                const month = months.findIndex((name) => name.startsWith(match[1].slice(0, 3)));
                const date = clock(new Date(Number(match[3] ?? now.getFullYear()), month, Number(match[2]), 12), match[4]);

                if (!match[3] && date > now) {
                    date.setFullYear(date.getFullYear() - 1);
                }

                return date;
            }

            return null;
        };

        // "📅 Date: Nov 7", "Oras: 2:00 PM", "📍 Venue: Covered court" become the form's date, time and place lines.
        const detail = (line) => {
            const match = line.match(/^\s*(?:(🗓️?|📅|📆|⏰|⌚|[\u{1F550}-\u{1F567}]|📍)\s*)?(?:(date|petsa|kailan|when|time|oras|venue|place|lugar|saan|where|location)\s*[:\-–]\s*)?(.+)$/iu);

            if (!match || (!match[1] && !match[2])) {
                return line;
            }

            const label = (match[2] ?? '').toLowerCase();
            const marker = match[1] ?? '';
            const kind = ['date', 'petsa', 'kailan', 'when'].includes(label) || /🗓|📅|📆/u.test(marker) ? 'date'
                : ['time', 'oras'].includes(label) || /⏰|⌚|[\u{1F550}-\u{1F567}]/u.test(marker) ? 'time'
                : 'place';

            return `${{ date: '🗓️', time: '🕑', place: '📍' }[kind]} ${match[3].trim()}`;
        };

        const read = (text) => {
            let lines = text.replace(/\r/g, '').split('\n').map((line) => line.replace(/\s*(…|\.\.\.)?\s*see more$/i, '').trimEnd());
            let postedAt = null;

            // The Page's name and the time stamp under it, at the top of a copied post.
            const top = lines.findIndex((line) => line.trim() !== '');
            const stampAt = [top, top + 1, top + 2].find((index) => index >= 0 && lines[index] !== undefined && readTimestamp(lines[index]));

            if (stampAt !== undefined) {
                postedAt = readTimestamp(lines[stampAt]);
                lines = lines.filter((_, index) => index > stampAt || (index < top));
            }

            lines = lines.filter((line) => !clutter.some((pattern) => pattern.test(line.trim()))).map(detail);

            // The first line that says something becomes the headline, and leaves the caption so it isn't shown twice.
            const headlineAt = lines.findIndex((line) => line.trim() !== ''
                && !/^(#[\p{L}\p{N}_]+\s*)+$/u.test(line.trim())
                && !/^(🗓️|🕑|📍) /u.test(line.trim()));
            const headline = headlineAt === -1 ? '' : lines[headlineAt].trim().replace(/^[^\p{L}\p{N}#]+/u, '');

            if (headlineAt !== -1) {
                lines.splice(headlineAt, 1);
            }

            const body = lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
            const words = `${headline}\n${body}`.toLowerCase();
            const category = /paalala|reminder|deadline|huling araw/.test(words) ? 'reminder'
                : /salamat|thank you|congratulations|congrats|pagbati|recap/.test(words) ? 'recognition'
                : /^(🗓️|📍) /mu.test(body) || /join us|inaanyayahan|tara na|register/.test(words) ? 'event'
                : 'announcement';

            return {
                body,
                category,
                postedAt,
                title: headline.length > 150 ? `${headline.slice(0, headline.lastIndexOf(' ', 148) > 100 ? headline.lastIndexOf(' ', 148) : 148)}…` : headline,
            };
        };

        const localDateTime = (date) => new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);

        const input = pastePanel.querySelector('[data-paste-input]');
        const result = pastePanel.querySelector('[data-paste-result]');
        const checklist = pastePanel.querySelector('[data-paste-checklist]');
        const summary = pastePanel.querySelector('[data-paste-summary]');
        const icons = {
            done: ['bg-emerald text-white', '<path d="M20 6 9 17l-5-5"/>'],
            todo: ['bg-gold/25 text-[#a16207]', '<path d="M12 8v5M12 16h.01"/>'],
            none: ['bg-mist text-slate-muted', '<path d="M7 12h10"/>'],
        };
        let lastFill = null;

        // One line of the checklist: what came over from the post, or what's still to do.
        const row = ([state, label, value]) => {
            const item = document.createElement('li');
            const mark = document.createElement('span');
            const text = document.createElement('span');
            const name = document.createElement('span');
            const detail = document.createElement('span');

            item.className = 'flex min-w-0 items-start gap-2.5';
            mark.className = `mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full ${icons[state][0]}`;
            mark.append(icon('h-3 w-3', icons[state][1]));
            text.className = 'min-w-0 leading-snug';
            name.className = 'block font-medium';
            name.textContent = label;
            detail.className = 'block truncate text-xs text-slate-muted';
            detail.textContent = value;
            text.append(name, detail);
            item.append(mark, text);

            return item;
        };

        const showChecklist = () => {
            if (!lastFill) {
                return;
            }

            const { post, link } = lastFill;
            const kind = announcementForm.querySelector(`input[name="category"][value="${post.category}"]`).closest('label').querySelector('.font-semibold').textContent.trim();
            const details = post.body.split('\n').filter((line) => /^(🗓️|🕑|📍) /u.test(line)).length;
            const hasPhoto = image.files.length > 0 || Boolean(importedPreview);
            const postedOn = post.postedAt?.toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });

            checklist.replaceChildren(...[
                [post.title ? 'done' : 'todo', 'Headline', post.title || 'Type one below'],
                ['done', 'Kind of post', `${kind}, change it below if it’s wrong`],
                [details ? 'done' : 'none', 'Date, time and place', details ? `${details} ${details === 1 ? 'line' : 'lines'} for the poster and calendar` : 'None in the post'],
                [!link ? 'none' : (postedOn ? 'done' : 'todo'), 'Facebook link and date', !link ? 'No link pasted' : (postedOn ? `Posted ${postedOn}` : 'Add when it went up, under Options')],
                [hasPhoto ? 'done' : 'todo', 'Photo', hasPhoto ? 'Added to the post' : 'Copy it on Facebook, then Ctrl + V'],
            ].map(row));
        };

        photoChanged = showChecklist;

        const fill = () => {
            if (pasted.value.trim() === '') {
                status.textContent = 'Paste the post from Facebook first.';

                return;
            }

            const post = read(pasted.value);
            const link = pastedLink.value.trim();
            const set = (name, value) => {
                field(name).value = value;
                field(name).dispatchEvent(new Event('input', { bubbles: true }));
            };

            set('title', post.title);
            set('body', post.body);
            announcementForm.querySelector(`input[name="category"][value="${post.category}"]`).checked = true;
            showCover();

            if (link !== '') {
                set('source_url', link);
                field('source_url').closest('details').open = true;

                if (post.postedAt) {
                    set('originally_posted_at', localDateTime(post.postedAt));
                }
            }

            lastFill = { post, link };
            status.textContent = '';
            input.hidden = true;
            result.hidden = false;
            summary.textContent = post.title ? `Filled in from “${post.title}”` : 'Filled in from your Facebook post';
            summary.classList.add('truncate', 'text-emerald');
            showChecklist();
        };

        pastePanel.querySelector('[data-paste-again]').addEventListener('click', () => {
            lastFill = null;
            pasted.value = '';
            pastedLink.value = '';
            input.hidden = false;
            result.hidden = true;
            summary.textContent = 'Paste it in and the form fills itself.';
            summary.classList.remove('truncate', 'text-emerald');
            pasted.focus();
        });

        pastePanel.querySelector('[data-paste-fill]').addEventListener('click', fill);
        pasted.addEventListener('paste', () => setTimeout(() => {
            if (field('title').value.trim() === '' && field('body').value.trim() === '') {
                fill();
            }
        }));
    }

    showText();
    showCounts();
    showCover();
}
