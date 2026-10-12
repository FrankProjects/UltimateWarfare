/**
 * Reports modal logic.
 */
(function () {
    const reportsModal = document.getElementById('reportsModal');
    const reportsContainer = document.getElementById('reportsContainer');
    const reportsTabs = document.getElementById('reportsTabs');
    const reportsPagination = document.getElementById('reportsPagination');
    const reportsPrevBtn = document.getElementById('reportsPrevBtn');
    const reportsNextBtn = document.getElementById('reportsNextBtn');
    const reportsPageInfo = document.getElementById('reportsPageInfo');
    const reportsModalBody = reportsModal.querySelector('.modal-body');
    const battleSummaryModal = document.getElementById('battleSummaryModal');
    const battleSummaryContainer = document.getElementById('battleSummaryContainer');
    const battleSummaryBackBtn = document.getElementById('battleSummaryBackBtn');

    let currentReportsPage = 1;
    let currentReportsType = 'all';
    let reportsTotalPages = 1;
    let reportsCategories = [];


    reportsPrevBtn.onclick = function () {
        if (currentReportsPage > 1) {
            currentReportsPage--;
            loadReports();
        }
    };

    reportsNextBtn.onclick = function () {
        if (currentReportsPage < reportsTotalPages) {
            currentReportsPage++;
            loadReports();
        }
    };

    battleSummaryBackBtn.onclick = function () {
        bootstrap.Modal.getOrCreateInstance(battleSummaryModal).hide();
        bootstrap.Modal.getOrCreateInstance(reportsModal).show();
    };

    function showReportsModal() {
        bootstrap.Modal.getOrCreateInstance(reportsModal).show();
        currentReportsPage = 1;
        currentReportsType = 'all';
        loadReports();
    }

    function selectReportsTab(type) {
        currentReportsType = type;
        currentReportsPage = 1;
        loadReports();
    }

    async function loadReports() {
        reportsContainer.innerHTML = '<div class="build-loading">Loading reports...</div>';

        try {
            const response = await fetch('/game/api/reports?type=' + currentReportsType + '&page=' + currentReportsPage);
            const result = await response.json();

            if (result.success) {
                reportsCategories = result.categories;
                reportsTotalPages = result.pagination.totalPages;
                renderReportsTabs();
                renderReports(result.reports);
                updateReportsPagination(result.pagination);
                reportsModalBody.scrollTop = 0;
            } else {
                reportsContainer.innerHTML = '<div class="build-loading text-negative">Failed to load reports</div>';
            }
        } catch (error) {
            console.error('Error loading reports:', error);
            reportsContainer.innerHTML = '<div class="build-loading text-negative">Failed to load reports</div>';
        }
    }

    function renderReportsTabs() {
        reportsTabs.innerHTML = '';
        reportsCategories.forEach(function (category) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (String(currentReportsType) === String(category.id) ? ' active' : '');
            tab.textContent = category.name;
            tab.onclick = function () { selectReportsTab(category.id); };
            reportsTabs.appendChild(tab);
        });
    }

    function renderReports(reports) {
        if (reports.length === 0) {
            reportsContainer.innerHTML = '<div class="build-loading">No reports found</div>';
            return;
        }

        let html = '<table class="uw-table uw-table-relaxed">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-start reports-col-date">Date</th>';
        html += '<th class="text-start">Report</th>';
        html += '<th class="reports-col-actions"></th>';
        html += '</tr>';

        reports.forEach(function (report) {
            html += '<tr>';
            html += '<td class="uw-cell-meta">' + report.date + '</td>';
            html += '<td>' + escapeHtml(report.report) + '</td>';
            html += '<td class="reports-col-actions">';
            if (report.battleSummaryId !== null) {
                html += '<button class="uw-btn battle-summary-btn" data-id="' + report.battleSummaryId + '">View battle</button>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        reportsContainer.innerHTML = html;

        reportsContainer.querySelectorAll('.battle-summary-btn').forEach(function (button) {
            button.onclick = function () {
                showBattleSummary(parseInt(this.getAttribute('data-id')));
            };
        });
    }

    async function showBattleSummary(battleSummaryId) {
        battleSummaryContainer.innerHTML = '<div class="build-loading">Loading battle summary...</div>';
        bootstrap.Modal.getOrCreateInstance(reportsModal).hide();
        bootstrap.Modal.getOrCreateInstance(battleSummaryModal).show();

        try {
            const response = await fetch('/game/api/battle-summary/' + battleSummaryId);
            const result = await response.json();

            if (result.success) {
                renderBattleSummary(result.battleSummary);
            } else {
                battleSummaryContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message || 'Failed to load battle summary') + '</div>';
            }
        } catch (error) {
            console.error('Error loading battle summary:', error);
            battleSummaryContainer.innerHTML = '<div class="build-loading text-negative">Failed to load battle summary</div>';
        }
    }

    function renderBattleSummary(battleSummary) {
        const resultClass = battleSummary.hasWon ? 'text-positive' : 'text-negative';
        const resultIcon = battleSummary.hasWon ? '🏆' : '💀';
        const winner = battleSummary.attackerWon ? battleSummary.attacker : battleSummary.defender;

        let html = '<div class="battle-result">';
        html += '<span class="battle-result-icon">' + resultIcon + '</span>';
        html += '<h3 class="battle-result-title ' + resultClass + '">' + (battleSummary.hasWon ? 'VICTORY!' : 'DEFEAT') + '</h3>';
        html += '<p>' + escapeHtml(battleSummary.attacker) + ' attacked ' + escapeHtml(battleSummary.defender)
            + ' on ' + battleSummary.date + '. ' + escapeHtml(winner) + ' won the battle.</p>';
        html += '</div>';

        html += '<div class="battle-log battle-log-full">';
        battleSummary.battleLog.forEach(function (line) {
            html += '<p>' + escapeHtml(line) + '</p>';
        });
        html += '</div>';

        battleSummaryContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function updateReportsPagination(pagination) {
        if (pagination.totalPages <= 1) {
            reportsPagination.style.display = 'none';
            return;
        }

        reportsPagination.style.display = 'flex';
        reportsPageInfo.textContent = 'Page ' + pagination.currentPage + ' of ' + pagination.totalPages + ' (' + pagination.totalReports + ' reports)';
        reportsPrevBtn.disabled = pagination.currentPage <= 1;
        reportsNextBtn.disabled = pagination.currentPage >= pagination.totalPages;
    }

    window.WorldReports = {
        show: showReportsModal
    };
})();
