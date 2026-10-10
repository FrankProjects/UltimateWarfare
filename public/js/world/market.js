/**
 * Market modal logic.
 */
(function () {
    const marketModal = document.getElementById('marketModal');
    const marketTabs = document.getElementById('marketTabs');
    const marketContainer = document.getElementById('marketContainer');

    let currentTab = 'buy';

    function show() {
        currentTab = 'buy';
        bootstrap.Modal.getOrCreateInstance(marketModal).show();
        renderTabs();
        loadTab();
    }

    function renderTabs() {
        marketTabs.innerHTML = '';
        const tabs = [
            { id: 'buy', name: 'Buy' },
            { id: 'sell', name: 'Sell' },
            { id: 'orders', name: 'My Orders' },
            { id: 'place', name: 'Place Order' }
        ];
        tabs.forEach(function (t) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (currentTab === t.id ? ' active' : '');
            tab.textContent = t.name;
            tab.onclick = function () {
                currentTab = t.id;
                renderTabs();
                loadTab();
            };
            marketTabs.appendChild(tab);
        });
    }

    function loadTab() {
        if (currentTab === 'place') {
            renderPlaceOrderForm();
        } else if (currentTab === 'buy') {
            loadList('/game/api/market/buy-list', 'buy');
        } else if (currentTab === 'sell') {
            loadList('/game/api/market/sell-list', 'sell');
        } else if (currentTab === 'orders') {
            loadList('/game/api/market/my-orders', 'orders');
        }
    }

    async function loadList(url, type) {
        marketContainer.innerHTML = '<div class="build-loading">Loading market data...</div>';

        try {
            const response = await fetch(url);
            const result = await response.json();

            if (result.success) {
                if (result.items.length === 0) {
                    marketContainer.innerHTML = '<div class="build-loading">No orders found</div>';
                    return;
                }

                if (type === 'buy') {
                    renderBuyTable(result.items);
                } else if (type === 'sell') {
                    renderSellTable(result.items);
                } else if (type === 'orders') {
                    renderOrdersTable(result.items);
                }
            } else {
                marketContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message || 'Failed to load market data') + '</div>';
            }
        } catch (error) {
            console.error('Error loading market data:', error);
            marketContainer.innerHTML = '<div class="build-loading text-negative">Failed to load market data</div>';
        }
    }

    function renderBuyTable(items) {
        let html = '<table class="uw-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Resource</th>';
        html += '<th class="text-center">Amount</th>';
        html += '<th class="text-center">Price</th>';
        html += '<th class="text-center">Seller</th>';
        html += '<th class="text-center">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr>';
            html += '<td class="text-center">' + escapeHtml(item.resource) + '</td>';
            html += '<td class="text-center">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">' + escapeHtml(item.playerName) + '</td>';
            html += '<td class="text-center">';
            if (!item.isOwn) {
                html += '<button class="market-action-btn" data-id="' + item.id + '" data-action="buy">Buy</button>';
            } else {
                html += '<span class="text-faded">Your order</span>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        marketContainer.innerHTML = html;
        bindActionButtons();
    }

    function renderSellTable(items) {
        let html = '<table class="uw-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Resource</th>';
        html += '<th class="text-center">Amount</th>';
        html += '<th class="text-center">Price</th>';
        html += '<th class="text-center">Buyer</th>';
        html += '<th class="text-center">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr>';
            html += '<td class="text-center">' + escapeHtml(item.resource) + '</td>';
            html += '<td class="text-center">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">' + escapeHtml(item.playerName) + '</td>';
            html += '<td class="text-center">';
            if (!item.isOwn) {
                html += '<button class="market-action-btn" data-id="' + item.id + '" data-action="sell">Sell</button>';
            } else {
                html += '<span class="text-faded">Your order</span>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        marketContainer.innerHTML = html;
        bindActionButtons();
    }

    function renderOrdersTable(items) {
        let html = '<table class="uw-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Type</th>';
        html += '<th class="text-center">Resource</th>';
        html += '<th class="text-center">Amount</th>';
        html += '<th class="text-center">Price</th>';
        html += '<th class="text-center">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr>';
            html += '<td class="text-center">' + escapeHtml(item.type) + '</td>';
            html += '<td class="text-center">' + escapeHtml(item.resource) + '</td>';
            html += '<td class="text-center">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">';
            html += '<button class="market-action-btn market-action-btn-danger" data-id="' + item.id + '" data-action="cancel">Cancel</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        marketContainer.innerHTML = html;
        bindActionButtons();
    }

    function bindActionButtons() {
        marketContainer.querySelectorAll('.market-action-btn').forEach(function (btn) {
            btn.onclick = function () {
                const itemId = parseInt(btn.getAttribute('data-id'), 10);
                const action = btn.getAttribute('data-action');
                marketAction(action, itemId, btn);
            };
        });
    }

    async function marketAction(action, itemId, btn) {
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = '...';

        try {
            const response = await fetch('/game/api/market/' + action + '/' + itemId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                loadTab();
            } else {
                showNotification(result.message || 'Action failed', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            console.error('Error performing market action:', error);
            showNotification('An error occurred', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function renderPlaceOrderForm() {
        let html = '<div class="uw-form">';
        html += '<div class="uw-field">';
        html += '<label class="uw-label">What do you want to do?</label>';
        html += '<select id="marketOrderType" class="form-select">';
        html += '<option value="buy">Buy</option>';
        html += '<option value="sell">Sell</option>';
        html += '</select>';
        html += '</div>';

        html += '<div class="uw-field">';
        html += '<label class="uw-label">Resource</label>';
        html += '<select id="marketOrderResource" class="form-select">';
        html += '<option value="wood">Wood</option>';
        html += '<option value="food">Food</option>';
        html += '<option value="steel">Steel</option>';
        html += '</select>';
        html += '</div>';

        html += '<div class="uw-field">';
        html += '<label class="uw-label">Amount</label>';
        html += '<input type="number" id="marketOrderAmount" min="1" value="1" class="form-control">';
        html += '</div>';

        html += '<div class="uw-field">';
        html += '<label class="uw-label">Price (cash)</label>';
        html += '<input type="number" id="marketOrderPrice" min="1" value="1" class="form-control">';
        html += '</div>';

        html += '<button id="marketSubmitOrder" class="market-action-btn uw-btn-block">Place Order</button>';
        html += '</div>';

        marketContainer.innerHTML = html;

        document.getElementById('marketSubmitOrder').onclick = function () {
            submitOrder();
        };
    }

    async function submitOrder() {
        const type = document.getElementById('marketOrderType').value;
        const resource = document.getElementById('marketOrderResource').value;
        const amount = parseInt(document.getElementById('marketOrderAmount').value, 10);
        const price = parseInt(document.getElementById('marketOrderPrice').value, 10);

        if (!amount || amount < 1) {
            showNotification('Amount must be at least 1', 'error');
            return;
        }
        if (!price || price < 1) {
            showNotification('Price must be at least 1', 'error');
            return;
        }

        const btn = document.getElementById('marketSubmitOrder');
        btn.disabled = true;
        btn.textContent = 'Placing order...';

        try {
            const response = await fetch('/game/api/market/create-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: type,
                    resource: resource,
                    amount: amount,
                    price: price
                })
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                currentTab = 'orders';
                renderTabs();
                loadTab();
            } else {
                showNotification(result.message || 'Failed to create order', 'error');
                btn.disabled = false;
                btn.textContent = 'Place Order';
            }
        } catch (error) {
            console.error('Error creating order:', error);
            showNotification('An error occurred', 'error');
            btn.disabled = false;
            btn.textContent = 'Place Order';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldMarket = {
        show: show
    };
})();
