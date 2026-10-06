/**
 * Notification system for the world map.
 */
function showNotification(message, type) {
    type = type || 'info';

    const notification = document.createElement('div');
    notification.className = 'fleet-notification fleet-notification-' + type;
    notification.innerHTML = message;
    notification.style.cssText =
        'position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 6px; ' +
        'color: #fff; font-weight: bold; z-index: 10002; box-shadow: 0 4px 12px rgba(0,0,0,0.4); ' +
        'animation: slideIn 0.3s ease; ' +
        'background: ' + (type === 'success' ? '#4CAF50' : (type === 'error' ? '#f44336' : '#2196F3')) + ';';

    document.body.appendChild(notification);

    setTimeout(function () {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(function () { notification.remove(); }, 300);
    }, 3000);
}
