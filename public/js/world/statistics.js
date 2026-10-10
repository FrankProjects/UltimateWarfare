/**
 * Statistics modal logic (overview, income, army, infrastructure).
 * Depends on: notifications.js
 */
(function () {
    const statisticsModal = document.getElementById('statisticsModal');
    const statisticsTabs = document.getElementById('statisticsTabs');
    const statisticsContainer = document.getElementById('statisticsContainer');
    const statisticsFooter = document.getElementById('statisticsFooter');

    let currentTab = 'overview';
    let cachedData = null;

    statisticsModal.addEventListener('hidden.bs.modal', function () {
        cachedData = null;
    });

    function show() {
        currentTab = 'overview';
        cachedData = null;
        bootstrap.Modal.getOrCreateInstance(statisticsModal).show();
        loadStatistics();
    }

    function renderTabs() {
        statisticsTabs.innerHTML = '';
        const tabs = [
            { id: 'overview', name: 'Overview' },
            { id: 'income', name: 'Income' },
            { id: 'army', name: 'Army' },
            { id: 'infrastructure', name: 'Infrastructure' }
        ];

        tabs.forEach(function (tab) {
            const el = document.createElement('div');
            el.className = 'build-tab' + (currentTab === tab.id ? ' active' : '');
            el.textContent = tab.name;
            el.onclick = function () {
                currentTab = tab.id;
                renderTabs();
                renderCurrentTab();
            };
            statisticsTabs.appendChild(el);
        });
    }

    async function loadStatistics() {
        statisticsContainer.innerHTML = '<div class="build-loading">Loading statistics...</div>';

        try {
            const response = await fetch('/game/api/statistics');
            const result = await response.json();

            if (result.success) {
                cachedData = result.data;
                renderTabs();
                renderCurrentTab();
            } else {
                statisticsContainer.innerHTML = '<div class="build-loading text-negative">Failed to load statistics</div>';
            }
        } catch (error) {
            statisticsContainer.innerHTML = '<div class="build-loading text-negative">Error loading statistics</div>';
        }
    }

    function renderCurrentTab() {
        if (!cachedData) return;

        if (currentTab === 'overview') {
            renderOverviewTab();
        } else if (currentTab === 'income') {
            renderIncomeTab();
        } else if (currentTab === 'army') {
            renderArmyTab();
        } else if (currentTab === 'infrastructure') {
            renderInfrastructureTab();
        }
    }

    function formatNumber(n) {
        return Number(n).toLocaleString();
    }

    function renderOverviewTab() {
        const status = cachedData.status;
        const warnings = cachedData.warnings;

        let html = '';

        for (let i = 0; i < warnings.length; i++) {
            const w = warnings[i];
            html += '<div class="stats-warning">';
            if (w.type === 'food') {
                if (w.seconds === 0) {
                    html += '<strong>You don\'t have enough food to feed your Population</strong><br>';
                    html += 'You will lose 30% of your population every hour!<br><br>';
                    html += '<strong>You don\'t have enough food to feed your army</strong><br>';
                    html += 'You will lose 10% of your army every hour!';
                } else {
                    html += 'You don\'t have enough food to feed your Population in <strong>' + formatNumber(w.seconds) + ' seconds from now!</strong><br>';
                    html += 'Once you hit 0 food, you will lose 30% of your population and 10% of your army every hour!';
                }
            } else if (w.type === 'cash') {
                if (w.seconds === 0) {
                    html += '<strong>You don\'t have enough cash to pay your army</strong><br>';
                    html += 'You will lose 10% of your army every hour!';
                } else {
                    html += 'You don\'t have enough cash to pay your army in <strong>' + formatNumber(w.seconds) + ' seconds from now!</strong><br>';
                    html += 'Once you hit 0 cash, you will lose 10% of your army every hour!';
                }
            }
            html += '</div>';
        }

        html += '<table class="uw-table uw-table-plain">';
        html += '<tr><td class="uw-table-label">Cash</td><td>$ ' + formatNumber(status.cash) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Wood</td><td>' + formatNumber(status.wood) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Steel</td><td>' + formatNumber(status.steel) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Food</td><td>' + formatNumber(status.food) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Regions</td><td>' + formatNumber(status.regions) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Net Worth</td><td>' + formatNumber(status.netWorth) + '</td></tr>';
        html += '</table>';

        statisticsContainer.innerHTML = html;
        renderFooter();
    }

    function renderIncomeTab() {
        const inc = cachedData.income;
        let html = '';

        html += '<h3 class="stats-heading">Cash Income</h3>';
        html += '<table class="uw-table uw-table-compact uw-table-plain">';
        html += '<tr><td class="uw-table-label">Income</td><td>' + formatNumber(inc.cashIncome) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Upkeep</td><td>-' + formatNumber(inc.cashUpkeep) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Net Income</td><td class="' + (inc.cashNet < 0 ? 'text-negative' : 'text-positive') + '">' + (inc.cashNet >= 0 ? '+' : '') + formatNumber(inc.cashNet) + '</td></tr>';
        html += '</table>';

        html += '<h3 class="stats-heading">Food Production</h3>';
        html += '<table class="uw-table uw-table-compact uw-table-plain">';
        html += '<tr><td class="uw-table-label">Production</td><td>' + formatNumber(inc.foodProduction) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Consumption</td><td>-' + formatNumber(inc.foodConsumption) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Net</td><td class="' + (inc.foodNet < 0 ? 'text-negative' : 'text-positive') + '">' + (inc.foodNet >= 0 ? '+' : '') + formatNumber(inc.foodNet) + '</td></tr>';
        html += '</table>';

        html += '<h3 class="stats-heading">Resources</h3>';
        html += '<table class="uw-table uw-table-compact uw-table-plain">';
        html += '<tr><td class="uw-table-label">Steel</td><td>' + formatNumber(inc.steelIncome) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Wood</td><td>' + formatNumber(inc.woodIncome) + '</td></tr>';
        html += '</table>';

        statisticsContainer.innerHTML = html;
        renderFooter();
    }

    function renderArmyTab() {
        const army = cachedData.army;
        let html = '';

        for (let i = 0; i < army.length; i++) {
            const group = army[i];
            html += '<h3 class="stats-heading">' + escapeHtml(group.category) + '</h3>';

            if (group.units.length === 0) {
                html += '<p class="text-faint">You don\'t have any ' + escapeHtml(group.category) + '</p>';
            } else {
                html += '<table class="uw-table uw-table-compact uw-table-plain">';
                for (let j = 0; j < group.units.length; j++) {
                    const unit = group.units[j];
                    html += '<tr><td class="uw-table-label">' + escapeHtml(unit.name) + '</td><td>' + formatNumber(unit.amount) + '</td></tr>';
                }
                html += '</table>';
            }
        }

        statisticsContainer.innerHTML = html;
        renderFooter();
    }

    function renderInfrastructureTab() {
        const infra = cachedData.infrastructure;
        let html = '';

        for (let i = 0; i < infra.length; i++) {
            const group = infra[i];
            html += '<h3 class="stats-heading">' + escapeHtml(group.category) + '</h3>';

            if (group.units.length === 0) {
                html += '<p class="text-faint">You don\'t have any ' + escapeHtml(group.category) + '</p>';
            } else {
                html += '<table class="uw-table uw-table-compact uw-table-plain">';
                for (let j = 0; j < group.units.length; j++) {
                    const unit = group.units[j];
                    html += '<tr><td class="uw-table-label">' + escapeHtml(unit.name) + '</td><td>' + formatNumber(unit.amount) + '</td></tr>';
                }
                html += '</table>';
            }
        }

        statisticsContainer.innerHTML = html;
        renderFooter();
    }

    function renderFooter() {
        statisticsFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldStatistics = {
        show: show
    };
})();
