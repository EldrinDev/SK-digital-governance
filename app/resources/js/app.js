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

    try {
        await navigator.clipboard.writeText(url);
        label.textContent = 'Link copied';
    } catch (error) {
        window.prompt('Copy this link:', url);

        return;
    }

    setTimeout(() => {
        label.textContent = 'Share this post';
    }, 2500);
});
