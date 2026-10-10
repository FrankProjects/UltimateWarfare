/**
 * Fleet overview modal logic.
 */
(function () {
    const fleetModal = document.getElementById('fleetOverviewModal');
    const fleetContainer = document.getElementById('fleetOverviewContainer');

    let allFleets = [];
    let timerInterval = null;

    fleetModal.addEventListener('hidden.bs.modal', function () {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    });

    function show() {
        bootstrap.Modal.getOrCreateInstance(fleetModal).show();
        loadFleets();
    }

    async function loadFleets() {
        fleetContainer.innerHTML = '<div class="build-loading">Loading fleet data...</div>';

        try {
            const response = await fetch('/game/api/fleet/overview');
            const result = await response.json();

            if (result.success) {
                allFleets = result.fleets;
                renderFleets();
                startTimers();
            } else {
                fleetContainer.innerHTML = '<div class="build-loading text-negative">Failed to load fleet data</div>';
            }
        } catch (error) {
            console.error('Error loading fleets:', error);
            fleetContainer.innerHTML = '<div class="build-loading text-negative">Failed to load fleet data</div>';
        }
    }

    function renderFleets() {
        if (allFleets.length === 0) {
            fleetContainer.innerHTML = '<div class="build-loading">No fleets sent out</div>';
            return;
        }

        let html = '<table class="uw-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">From</th>';
        html += '<th class="text-center">To</th>';
        html += '<th class="text-center">Owner</th>';
        html += '<th class="text-start">Fleet</th>';
        html += '<th class="text-center">Time Left</th>';
        html += '<th class="text-center">Actions</th>';
        html += '</tr>';

        allFleets.forEach(function (fleet) {
            const unitsHtml = fleet.units.map(function (u) {
                return escapeHtml(u.amount + ' ' + u.name);
            }).join('<br>');

            let timeHtml;
            if (fleet.hasArrived) {
                timeHtml = '<b class="text-positive">Arrived!</b>';
            } else {
                timeHtml = '<span class="fleet-timer" data-timeleft="' + fleet.timeLeft + '">' + formatTime(fleet.timeLeft) + '</span>';
            }

            let actionsHtml = '';
            if (!fleet.hasArrived) {
                actionsHtml = '<button class="fleet-action-btn recall" data-id="' + fleet.id + '" data-action="recall">Recall</button>';
            } else if (fleet.targetIsYours) {
                actionsHtml = '<button class="fleet-action-btn reinforce" data-id="' + fleet.id + '" data-action="reinforce">Reinforce</button>';
                actionsHtml += '<br><button class="fleet-action-btn recall fleet-action-btn-below" data-id="' + fleet.id + '" data-action="recall">Recall</button>';
            } else {
                actionsHtml = '<button class="fleet-action-btn attack" data-id="' + fleet.id + '" data-action="attack">Attack</button>';
                actionsHtml += '<br><button class="fleet-action-btn recall fleet-action-btn-below" data-id="' + fleet.id + '" data-action="recall">Recall</button>';
            }

            html += '<tr>';
            html += '<td class="text-center">' + fleet.sourceX + ', ' + fleet.sourceY + '</td>';
            html += '<td class="text-center">' + fleet.targetX + ', ' + fleet.targetY + '</td>';
            html += '<td class="text-center">' + (fleet.targetIsYours ? '<span class="text-own">You</span>' : escapeHtml(fleet.targetOwner || 'Nobody')) + '</td>';
            html += '<td class="fleet-overview-units">' + unitsHtml + '</td>';
            html += '<td class="text-center">' + timeHtml + '</td>';
            html += '<td class="text-center">' + actionsHtml + '</td>';
            html += '</tr>';
        });

        html += '</table>';
        fleetContainer.innerHTML = html;

        fleetContainer.querySelectorAll('.fleet-action-btn').forEach(function (btn) {
            btn.onclick = function () {
                const fleetId = parseInt(btn.getAttribute('data-id'), 10);
                const action = btn.getAttribute('data-action');
                handleFleetAction(fleetId, action, btn);
            };
        });
    }

    async function handleFleetAction(fleetId, action, btn) {
        let url;
        if (action === 'recall') {
            url = '/game/api/fleet/recall/' + fleetId;
        } else if (action === 'reinforce') {
            url = '/game/api/fleet/reinforce/' + fleetId;
        } else if (action === 'attack') {
            url = '/game/api/fleet/attack/' + fleetId;
        } else {
            return;
        }

        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = '...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');

                if (window.WorldApp && WorldApp.worldMap) {
                    // Remove fleet from worldmap fleet manager
                    if (WorldApp.worldMap.fleetManager) {
                        const fm = WorldApp.worldMap.fleetManager;
                        fm.fleets = fm.fleets.filter(function (f) { return f.id !== fleetId; });
                    }

                    // Update region units on map if returned
                    if (result.regionId && result.units) {
                        WorldApp.worldMap.fleetManager.updateRegionUnits(result.regionId, result.units);
                        // Unit counts changed, so cached build data of this region is outdated
                        WorldBuild.invalidateCache(result.regionId);
                    }

                    WorldApp.worldMap.render();
                }

                loadFleets();
            } else {
                showNotification(result.message || 'Action failed', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            console.error('Error performing fleet action:', error);
            showNotification('An error occurred', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function startTimers() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        timerInterval = setInterval(function () {
            const timers = fleetContainer.querySelectorAll('.fleet-timer');
            let needsRefresh = false;

            timers.forEach(function (el) {
                let timeLeft = parseInt(el.getAttribute('data-timeleft'), 10) - 1;
                if (timeLeft <= 0) {
                    timeLeft = 0;
                    needsRefresh = true;
                }
                el.setAttribute('data-timeleft', String(timeLeft));
                el.textContent = formatTime(timeLeft);
            });

            if (needsRefresh) {
                loadFleets();
            }
        }, 1000);
    }

    function formatTime(seconds) {
        if (seconds <= 0) { return 'Arrived!'; }
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        const parts = [];
        if (h > 0) { parts.push(h + 'h'); }
        if (m > 0) { parts.push(m + 'm'); }
        parts.push(s + 's');
        return parts.join(' ');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldFleetOverview = {
        show: show
    };
})();
