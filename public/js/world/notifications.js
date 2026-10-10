/**
 * Notification system for the world map.
 */
function showNotification(message, type) {
    type = type || 'info';

    const notification = document.createElement('div');
    notification.className = 'fleet-notification fleet-notification-' + type;
    notification.innerHTML = message;

    document.body.appendChild(notification);

    setTimeout(function () {
        notification.classList.add('is-leaving');
        setTimeout(function () { notification.remove(); }, 300);
    }, 3000);
}

/**
 * Styled confirmation dialog, replacement for window.confirm().
 * Renders on top of any open modal and calls onConfirm when accepted.
 */
function showConfirm(message, onConfirm, options) {
    options = options || {};

    const modal = document.getElementById('confirmModal');
    const confirmBtn = document.getElementById('confirmModalBtn');

    document.getElementById('confirmModalLabel').textContent = options.title || 'Confirm';
    document.getElementById('confirmModalBody').textContent = message;
    confirmBtn.textContent = options.confirmText || 'Confirm';

    const instance = bootstrap.Modal.getOrCreateInstance(modal);
    confirmBtn.onclick = function () {
        confirmBtn.onclick = null;
        instance.hide();
        onConfirm();
    };
    instance.show();
}
