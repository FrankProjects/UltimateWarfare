/**
 * Keeps the resource ticker counting in real time, without polling the server.
 *
 * Uses the same formula as the server (ResourceAccrualCalculator): gains are measured against the Unix epoch
 * and floored, so the shown value matches what the server will store for the same second.
 *
 * The income/upkeep rate only changes through a completing construction/research (processed by the queue
 * worker) or a player action. The ticker refreshes itself shortly after the next completion, and action
 * handlers call ResourceTicker.set() (values from the response) and/or ResourceTicker.refresh().
 */
const ResourceTicker = (function () {
    // Give the queue worker time to process a completion before fetching (its interval plus margin)
    const COMPLETION_PROCESSING_DELAY_SECONDS = 3;
    // When a completion stays overdue (worker down), back off up to this delay instead of fetching every few seconds
    const MAX_OVERDUE_DELAY_SECONDS = 60;
    // setTimeout can't wait longer than ~24.8 days
    const MAX_TIMEOUT_SECONDS = 86400;

    const ticker = document.querySelector('.resource-ticker[data-server-time]');
    const anchors = {};
    let refreshTimeout = null;
    let overdueDelaySeconds = COMPLETION_PROCESSING_DELAY_SECONDS;

    if (!ticker) {
        return { set: function () {}, refresh: function () {}, completionDelaySeconds: COMPLETION_PROCESSING_DELAY_SECONDS };
    }

    // Server time at page load; elapsed time is measured with the client clock, so client clock skew doesn't matter
    let serverTimeAnchor = parseInt(ticker.dataset.serverTime, 10);
    let clientTimeAnchor = Date.now();

    function serverNow() {
        return serverTimeAnchor + Math.floor((Date.now() - clientTimeAnchor) / 1000);
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

    function renderRate(key) {
        const anchor = anchors[key];
        anchor.rateElement.textContent = (anchor.rate >= 0 ? '+' : '') + anchor.rate.toLocaleString('en-US') + '/h';
        anchor.rateElement.classList.toggle('positive', anchor.rate >= 0);
        anchor.rateElement.classList.toggle('negative', anchor.rate < 0);
    }

    function scheduleRefresh(nextCompletion) {
        if (refreshTimeout) {
            clearTimeout(refreshTimeout);
            refreshTimeout = null;
        }
        if (!nextCompletion) {
            return;
        }

        let waitSeconds;
        if (nextCompletion > serverNow()) {
            overdueDelaySeconds = COMPLETION_PROCESSING_DELAY_SECONDS;
            waitSeconds = Math.min(MAX_TIMEOUT_SECONDS, nextCompletion - serverNow() + COMPLETION_PROCESSING_DELAY_SECONDS);
        } else {
            waitSeconds = overdueDelaySeconds;
            overdueDelaySeconds = Math.min(MAX_OVERDUE_DELAY_SECONDS, overdueDelaySeconds * 2);
        }
        refreshTimeout = setTimeout(refresh, waitSeconds * 1000);
    }

    async function refresh() {
        try {
            const response = await fetch('/game/api/resources');
            const result = await response.json();
            if (!result.success) {
                return;
            }

            serverTimeAnchor = result.serverTime;
            clientTimeAnchor = Date.now();
            for (const key in anchors) {
                anchors[key].amount = result.resources[key];
                anchors[key].rate = result.rates[key];
                anchors[key].at = result.serverTime;
                renderRate(key);
            }
            render();

            // A completion that is still due wasn't processed yet: try again after the delay
            scheduleRefresh(result.nextCompletion);
        } catch (error) {
            console.error('Error refreshing resources:', error);
        }
    }

    ticker.querySelectorAll('.resource-item[data-resource]').forEach(function (item) {
        anchors[item.dataset.resource] = {
            element: item.querySelector('.resource-amount'),
            rateElement: item.querySelector('.resource-income'),
            amount: parseInt(item.dataset.amount, 10),
            rate: parseInt(item.dataset.rate, 10),
            at: serverTimeAnchor,
            shown: parseInt(item.dataset.amount, 10)
        };
    });

    setInterval(render, 1000);
    scheduleRefresh(parseInt(ticker.dataset.nextCompletion, 10) || null);

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
        },

        /**
         * Fetch current resources, rates and the next completion, e.g. after an action that changed them
         */
        refresh: refresh,

        completionDelaySeconds: COMPLETION_PROCESSING_DELAY_SECONDS
    };
})();
