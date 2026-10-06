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
            } else {
                reportsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load reports</div>';
            }
        } catch (error) {
            console.error('Error loading reports:', error);
            reportsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load reports</div>';
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

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 10px; text-align: left; width: 150px;">Date</th>';
        html += '<th style="padding: 10px; text-align: left;">Report</th>';
        html += '</tr>';

        reports.forEach(function (report) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 10px; color: #aaa; font-size: 12px; vertical-align: top;">' + report.date + '</td>';
            html += '<td style="padding: 10px;">' + report.report + '</td>';
            html += '</tr>';
        });

        html += '</table>';
        reportsContainer.innerHTML = html;
    }

    function updateReportsPagination(pagination) {
        if (pagination.totalPages <= 1) {
            reportsPagination.style.display = 'none';
            return;
        }

        reportsPagination.style.display = 'block';
        reportsPageInfo.textContent = 'Page ' + pagination.currentPage + ' of ' + pagination.totalPages + ' (' + pagination.totalReports + ' reports)';
        reportsPrevBtn.disabled = pagination.currentPage <= 1;
        reportsNextBtn.disabled = pagination.currentPage >= pagination.totalPages;
        reportsPrevBtn.style.opacity = pagination.currentPage <= 1 ? '0.5' : '1';
        reportsNextBtn.style.opacity = pagination.currentPage >= pagination.totalPages ? '0.5' : '1';
    }

    window.WorldReports = {
        show: showReportsModal
    };
})();
