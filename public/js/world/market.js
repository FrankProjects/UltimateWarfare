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
                marketContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message || 'Failed to load market data') + '</div>';
            }
        } catch (error) {
            console.error('Error loading market data:', error);
            marketContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load market data</div>';
        }
    }

    function renderBuyTable(items) {
        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center;">Resource</th>';
        html += '<th style="padding: 8px; text-align: center;">Amount</th>';
        html += '<th style="padding: 8px; text-align: center;">Price</th>';
        html += '<th style="padding: 8px; text-align: center;">Seller</th>';
        html += '<th style="padding: 8px; text-align: center;">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.resource) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.playerName) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">';
            if (!item.isOwn) {
                html += '<button class="market-action-btn" data-id="' + item.id + '" data-action="buy">Buy</button>';
            } else {
                html += '<span style="color: #888;">Your order</span>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        marketContainer.innerHTML = html;
        bindActionButtons();
    }

    function renderSellTable(items) {
        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center;">Resource</th>';
        html += '<th style="padding: 8px; text-align: center;">Amount</th>';
        html += '<th style="padding: 8px; text-align: center;">Price</th>';
        html += '<th style="padding: 8px; text-align: center;">Buyer</th>';
        html += '<th style="padding: 8px; text-align: center;">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.resource) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.playerName) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">';
            if (!item.isOwn) {
                html += '<button class="market-action-btn" data-id="' + item.id + '" data-action="sell">Sell</button>';
            } else {
                html += '<span style="color: #888;">Your order</span>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        marketContainer.innerHTML = html;
        bindActionButtons();
    }

    function renderOrdersTable(items) {
        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center;">Type</th>';
        html += '<th style="padding: 8px; text-align: center;">Resource</th>';
        html += '<th style="padding: 8px; text-align: center;">Amount</th>';
        html += '<th style="padding: 8px; text-align: center;">Price</th>';
        html += '<th style="padding: 8px; text-align: center;">Action</th>';
        html += '</tr>';

        items.forEach(function (item) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.type) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(item.resource) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + item.amount.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">$' + item.price.toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">';
            html += '<button class="market-action-btn" data-id="' + item.id + '" data-action="cancel" style="background-color: #f44336;">Cancel</button>';
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
        let html = '<div style="max-width: 400px; margin: 0 auto; padding: 10px;">';
        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">What do you want to do?</label>';
        html += '<select id="marketOrderType" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<option value="buy">Buy</option>';
        html += '<option value="sell">Sell</option>';
        html += '</select>';
        html += '</div>';

        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">Resource</label>';
        html += '<select id="marketOrderResource" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<option value="wood">Wood</option>';
        html += '<option value="food">Food</option>';
        html += '<option value="steel">Steel</option>';
        html += '</select>';
        html += '</div>';

        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">Amount</label>';
        html += '<input type="number" id="marketOrderAmount" min="1" value="1" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; box-sizing: border-box;">';
        html += '</div>';

        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">Price (cash)</label>';
        html += '<input type="number" id="marketOrderPrice" min="1" value="1" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; box-sizing: border-box;">';
        html += '</div>';

        html += '<button id="marketSubmitOrder" style="width: 100%; padding: 10px; background: #8B7355; color: #f3e6c1; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Place Order</button>';
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
