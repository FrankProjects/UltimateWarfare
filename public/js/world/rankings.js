/**
 * Rankings modal logic.
 */
(function () {
    const rankingsModal = document.getElementById('rankingsModal');
    const rankingsContainer = document.getElementById('rankingsContainer');

    let allRankings = [];
    let sortColumn = 'regions';
    let sortAscending = false;

    function show() {
        bootstrap.Modal.getOrCreateInstance(rankingsModal).show();
        loadRankings();
    }

    function hide() {
        bootstrap.Modal.getInstance(rankingsModal).hide();
    }

    async function loadRankings() {
        rankingsContainer.innerHTML = '<div class="build-loading">Loading rankings...</div>';

        try {
            const response = await fetch('/game/api/rankings');
            const result = await response.json();

            if (result.success) {
                allRankings = result.rankings;
                renderRankings();
            } else {
                rankingsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load rankings</div>';
            }
        } catch (error) {
            console.error('Error loading rankings:', error);
            rankingsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load rankings</div>';
        }
    }

    function sortRankings() {
        allRankings.sort(function (a, b) {
            const valA = a[sortColumn];
            const valB = b[sortColumn];

            if (valA < valB) return sortAscending ? -1 : 1;
            if (valA > valB) return sortAscending ? 1 : -1;
            return 0;
        });
    }

    function getSortArrow(column) {
        if (sortColumn !== column) return '';
        return sortAscending ? ' ↑' : ' ↓';
    }

    function onHeaderClick(column) {
        if (sortColumn === column) {
            sortAscending = !sortAscending;
        } else {
            sortColumn = column;
            sortAscending = false;
        }
        renderRankings();
    }

    function renderRankings() {
        if (allRankings.length === 0) {
            rankingsContainer.innerHTML = '<div class="build-loading">No players found</div>';
            return;
        }

        sortRankings();

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center; width: 60px;">Rank</th>';
        html += '<th style="padding: 8px; text-align: left;">Player</th>';
        html += '<th style="padding: 8px; text-align: center; cursor: pointer;" data-sort="regions">Regions' + getSortArrow('regions') + '</th>';
        html += '<th style="padding: 8px; text-align: center; cursor: pointer;" data-sort="netWorth">Net Worth' + getSortArrow('netWorth') + '</th>';
        html += '</tr>';

        allRankings.forEach(function (player, index) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + (index + 1) + '</td>';
            html += '<td style="padding: 8px; text-align: left;"><a href="#" class="player-link" data-player="' + escapeHtml(player.name) + '" style="color: #f3e6c1; text-decoration: underline; cursor: pointer;">' + escapeHtml(player.name) + '</a>';
            if (player.federation) {
                html += ' <i style="color: #aaa;">(' + escapeHtml(player.federation) + ')</i>';
            }
            html += '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + player.regions + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + player.netWorth + '</td>';
            html += '</tr>';
        });

        html += '</table>';
        rankingsContainer.innerHTML = html;

        rankingsContainer.querySelectorAll('th[data-sort]').forEach(function (th) {
            th.onclick = function () {
                onHeaderClick(th.getAttribute('data-sort'));
            };
        });

        rankingsContainer.querySelectorAll('.player-link').forEach(function (link) {
            link.onclick = function (e) {
                e.preventDefault();
                const playerName = link.getAttribute('data-player');
                rankingsModal.addEventListener('hidden.bs.modal', function handler() {
                    rankingsModal.removeEventListener('hidden.bs.modal', handler);
                    WorldPlayerProfile.show(playerName);
                });
                hide();
            };
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldRankings = {
        show: show
    };
})();
