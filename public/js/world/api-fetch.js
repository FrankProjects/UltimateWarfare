(function () {
    const originalFetch = window.fetch;
    let sessionExpiredHandled = false;

    window.fetch = function (url, options) {
        return originalFetch.call(this, url, options).then(function (response) {
            if (response.status === 401 && !sessionExpiredHandled) {
                sessionExpiredHandled = true;
                showNotification('Your session has expired. Redirecting to login...', 'error');
                setTimeout(function () {
                    window.location.href = '/login';
                }, 2000);
            }

            if (response.status === 401) {
                return new Response(
                    JSON.stringify({ success: false, message: 'Session expired' }),
                    { status: 401, headers: { 'Content-Type': 'application/json' } }
                );
            }

            return response;
        });
    };
})();
