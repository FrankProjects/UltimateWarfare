/**
 * Keeps the resource ticker counting in real time, without polling the server.
 *
 * Uses the same formula as the server (ResourceAccrualCalculator): gains are measured against the Unix epoch
 * and floored, so the shown value matches what the server will store for the same second.
 * Server responses that change resources re-anchor the ticker through ResourceTicker.set().
 */
const ResourceTicker = (function () {
    const ticker = document.querySelector('.resource-ticker[data-server-time]');
    const anchors = {};

    if (!ticker) {
        return { set: function () {} };
    }

    // Server time at page load; elapsed time is measured with the client clock, so client clock skew doesn't matter
    const serverTimeAtLoad = parseInt(ticker.dataset.serverTime, 10);
    const clientTimeAtLoad = Date.now();

    function serverNow() {
        return serverTimeAtLoad + Math.floor((Date.now() - clientTimeAtLoad) / 1000);
    }

    function floorDiv(dividend, divisor) {
        return Math.floor(dividend / divisor);
    }

    function valueAt(anchor, timestamp) {
        if (timestamp <= anchor.at) {
            return anchor.amount;
        }

        const gain = floorDiv(anchor.rate * timestamp, 3600) - floorDiv(anchor.rate * anchor.at, 3600);
        return Math.max(0, anchor.amount + gain);
    }

    function render() {
        const now = serverNow();
        for (const key in anchors) {
            const anchor = anchors[key];
            const value = valueAt(anchor, now);
            if (value !== anchor.shown) {
                anchor.element.textContent = value.toLocaleString('en-US');
                anchor.shown = value;
            }
        }
    }

    ticker.querySelectorAll('.resource-item[data-resource]').forEach(function (item) {
        anchors[item.dataset.resource] = {
            element: item.querySelector('.resource-amount'),
            amount: parseInt(item.dataset.amount, 10),
            rate: parseInt(item.dataset.rate, 10),
            at: serverTimeAtLoad,
            shown: parseInt(item.dataset.amount, 10)
        };
    });

    setInterval(render, 1000);

    return {
        /**
         * Re-anchor resources to values the server just returned, e.g. { cash: result.newCash }
         */
        set: function (values) {
            const now = serverNow();
            for (const key in values) {
                if (values[key] === undefined || !anchors[key]) {
                    continue;
                }
                anchors[key].amount = values[key];
                anchors[key].at = now;
            }
            render();
        }
    };
})();
