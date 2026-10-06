/**
 * Region overview modal logic.
 */
(function () {
    const regionModal = document.getElementById('regionOverviewModal');
    const regionContainer = document.getElementById('regionOverviewContainer');

    let allRegions = [];
    let sortColumn = null;
    let sortAscending = true;

    const categories = [
        { key: 1, label: 'Build' },
        { key: 2, label: 'Defense' },
        { key: 3, label: 'Special' },
        { key: 5, label: 'Elite' },
        { key: 6, label: 'Troops' },
        { key: 7, label: 'Naval' },
        { key: 8, label: 'Air' },
        { key: 9, label: 'Missiles' }
    ];

    function show() {
        bootstrap.Modal.getOrCreateInstance(regionModal).show();
        loadRegions();
    }

    function hide() {
        bootstrap.Modal.getInstance(regionModal).hide();
    }

    async function loadRegions() {
        regionContainer.innerHTML = '<div class="build-loading">Loading regions...</div>';

        try {
            const response = await fetch('/game/api/region/overview');
            const result = await response.json();

            if (result.success) {
                allRegions = result.regions;
                renderRegions();
            } else {
                regionContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load region data</div>';
            }
        } catch (error) {
            console.error('Error loading regions:', error);
            regionContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load region data</div>';
        }
    }

    function sortRegions() {
        if (sortColumn === null) return;

        allRegions.sort(function (a, b) {
            let valA, valB;

            if (sortColumn === 'position') {
                valA = a.x * 10000 + a.y;
                valB = b.x * 10000 + b.y;
            } else {
                const catA = a.categoryCounts[sortColumn];
                const catB = b.categoryCounts[sortColumn];
                valA = catA ? catA.count : 0;
                valB = catB ? catB.count : 0;
            }

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
            sortAscending = true;
        }
        renderRegions();
    }

    function renderRegions() {
        if (allRegions.length === 0) {
            regionContainer.innerHTML = '<div class="build-loading">You have no regions. Buy one on the map!</div>';
            return;
        }

        sortRegions();

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 6px; text-align: center; cursor: pointer;" data-sort="position">Pos' + getSortArrow('position') + '</th>';

        categories.forEach(function (cat) {
            html += '<th style="padding: 6px; text-align: center; cursor: pointer; font-size: 0.85em;" data-sort="' + cat.key + '">' + cat.label + getSortArrow(cat.key) + '</th>';
        });

        html += '<th style="padding: 6px; text-align: center;">Actions</th>';
        html += '</tr>';

        allRegions.forEach(function (region) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 6px; text-align: center;">' + region.x + ', ' + region.y + '</td>';

            categories.forEach(function (cat) {
                const data = region.categoryCounts[cat.key];
                const count = data ? data.count : 0;
                const inConstruction = data ? data.inConstruction : 0;
                let text = count.toString();
                if (inConstruction > 0) {
                    text += ' (+' + inConstruction + ')';
                }
                if (cat.key === 1) {
                    text += ' / ' + region.space;
                }
                html += '<td style="padding: 6px; text-align: center; font-size: 0.85em;">' + escapeHtml(text) + '</td>';
            });

            html += '<td style="padding: 6px; text-align: center; white-space: nowrap;">';
            html += '<button class="fleet-action-btn reinforce region-build-btn" data-id="' + region.id + '">Build</button>';
            html += ' <button class="fleet-action-btn attack region-destroy-btn" data-id="' + region.id + '">Destroy</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</table>';
        regionContainer.innerHTML = html;

        // Header click handlers for sorting
        regionContainer.querySelectorAll('th[data-sort]').forEach(function (th) {
            th.onclick = function () {
                const col = th.getAttribute('data-sort');
                onHeaderClick(col === 'position' ? 'position' : parseInt(col, 10));
            };
        });

        regionContainer.querySelectorAll('.region-build-btn').forEach(function (btn) {
            btn.onclick = function () {
                const regionId = parseInt(btn.getAttribute('data-id'), 10);
                const mapRegion = findMapRegion(regionId);
                if (mapRegion) {
                    regionModal.addEventListener('hidden.bs.modal', function handler() {
                        regionModal.removeEventListener('hidden.bs.modal', handler);
                        WorldBuild.showBuildModal(mapRegion);
                    });
                    hide();
                }
            };
        });

        regionContainer.querySelectorAll('.region-destroy-btn').forEach(function (btn) {
            btn.onclick = function () {
                const regionId = parseInt(btn.getAttribute('data-id'), 10);
                const mapRegion = findMapRegion(regionId);
                if (mapRegion) {
                    regionModal.addEventListener('hidden.bs.modal', function handler() {
                        regionModal.removeEventListener('hidden.bs.modal', handler);
                        WorldDestroy.showDestroyModal(mapRegion);
                    });
                    hide();
                }
            };
        });
    }

    function findMapRegion(regionId) {
        if (!WorldApp.worldRegions) return null;
        for (let i = 0; i < WorldApp.worldRegions.length; i++) {
            if (WorldApp.worldRegions[i].id === regionId) {
                return WorldApp.worldRegions[i];
            }
        }
        return null;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldRegionOverview = {
        show: show
    };
})();
