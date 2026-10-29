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

// A select marked data-listbox opens a list drawn by the page instead of the browser's own,
// so each choice can carry a second line (data-hint) and choices that can't be picked can
// say why (data-note). The select stays in the form and keeps the value.
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

const enhanceListbox = (select) => {
    const label = document.querySelector(`label[for="${select.id}"]`);
    const wrapper = element('div', 'relative mt-1.5');
    const button = element('button', `${select.className.replace(/\b(mt-1\.5|block)\b/g, '')} flex items-center gap-3 text-left disabled:cursor-not-allowed disabled:bg-mist disabled:text-slate-muted`);
    const buttonText = element('span', 'flex-1 truncate');
    const list = element('ul', 'absolute inset-x-0 top-full z-20 mt-1.5 max-h-72 space-y-0.5 overflow-y-auto rounded-[10px] border border-line bg-white p-1.5 shadow-lg shadow-navy/10 outline-none');
    let choices = [];
    let active = -1;

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
    }

    if (select.hasAttribute('aria-describedby')) {
        button.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
    }

    list.id = `${select.id}-list`;
    list.tabIndex = -1;
    list.hidden = true;
    list.setAttribute('role', 'listbox');
    button.setAttribute('aria-controls', list.id);

    select.classList.add('sr-only');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    select.after(wrapper);
    wrapper.append(button, list);

    const highlight = (index) => {
        active = index;
        choices.forEach(({ row }, position) => row.toggleAttribute('data-active', position === index));
        list.setAttribute('aria-activedescendant', choices[index]?.row.id ?? '');
        choices[index]?.row.scrollIntoView({ block: 'nearest' });
    };

    const close = (refocus = true) => {
        list.hidden = true;
        button.setAttribute('aria-expanded', 'false');

        if (refocus) {
            button.focus();
        }
    };

    const choose = (index) => {
        const choice = choices[index];

        if (!choice || choice.option.disabled) {
            return;
        }

        select.value = choice.option.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        close();
    };

    const render = () => {
        const chosen = select.selectedOptions[0];

        button.disabled = select.disabled;
        buttonText.textContent = chosen?.dataset.label ?? chosen?.textContent ?? '';
        buttonText.classList.toggle('text-slate-muted', !chosen || chosen.value === '');

        choices = [...select.options].filter((option) => option.value !== '' && !option.hidden).map((option, index) => {
            const row = element('li', 'flex items-center gap-3 rounded-[8px] px-3 py-2.5 data-active:bg-mist');
            const text = element('span', 'min-w-0 flex-1');

            row.id = `${list.id}-${index}`;
            row.setAttribute('role', 'option');
            row.setAttribute('aria-selected', String(option === chosen));
            row.classList.add(option.disabled ? 'cursor-default' : 'cursor-pointer');

            if (option.disabled) {
                row.setAttribute('aria-disabled', 'true');
            }

            row.append(option === chosen ? icon('h-4 w-4 shrink-0 text-emerald', '<path d="M20 6 9 17l-5-5"/>') : element('span', 'h-4 w-4 shrink-0'));
            text.append(element('span', `block font-display font-semibold tabular-nums ${option.disabled ? 'text-slate-muted' : 'text-navy'}`, option.dataset.label ?? option.textContent));

            if (option.dataset.hint) {
                text.append(element('span', 'block text-xs text-slate-muted', option.dataset.hint));
            }

            row.append(text);

            if (option.dataset.note) {
                const note = element('span', 'flex shrink-0 items-center gap-1.5 text-xs font-medium text-emerald-dark');

                note.append(icon('h-4 w-4', '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="m9 14 2 2 4-4"/>'), option.dataset.note);
                row.append(note);
            }

            row.addEventListener('mousemove', () => highlight(option.disabled ? -1 : index));
            row.addEventListener('click', () => choose(index));

            return { option, row };
        });

        list.replaceChildren(...choices.map(({ row }) => row));
    };

    const open = () => {
        if (button.disabled) {
            return;
        }

        list.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        list.focus();
        highlight(choices.findIndex(({ option }) => option.selected));

        if (active === -1) {
            move(1);
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
        }
    });
    document.addEventListener('click', (event) => {
        if (!list.hidden && !wrapper.contains(event.target)) {
            close(false);
        }
    });
    select.addEventListener('change', render);
    render();
};

document.querySelectorAll('select[data-listbox]').forEach(enhanceListbox);

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
