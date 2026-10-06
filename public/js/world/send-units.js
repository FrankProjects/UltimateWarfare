/**
 * Send Units mode — allows sending units from your region to another of your regions
 * on the world map, reusing the attack mode highlight/select pattern.
 *
 * Flow:
 *   1. Player clicks own region → "Send Units" button
 *   2. Modal shows sendable units (troops, air, naval, special)
 *   3. Player selects quantities → clicks "Select Target"
 *   4. Map enters "send mode": your in-range regions are highlighted
 *   5. Player clicks a highlighted region → fleet is created via API
 *   6. Fleet appears on map with ETA countdown
 */
(function () {
    let sendMode = false;
    let sendSourceRegion = null;
    let sendUnitQuantities = {};
    let sendTargetData = []; // [{regionId, x, y, travelTime}]
    let sendEligibleRegionIds = new Set();

    const sendUnitsModal = document.getElementById('sendUnitsModal');
    const confirmSendUnitsBtn = document.getElementById('confirmSendUnitsBtn');

    confirmSendUnitsBtn.onclick = enterSendTargetSelection;

    function getDefaultTileClick() {
        return WorldApp.defaultTileClick;
    }

    /**
     * Step 1+2: Open send units modal, load available units from source region
     */
    async function startSendUnits(sourceRegion) {
        // Close the "Your Region" modal
        // yourRegionModal is already closed by modals.js before calling this

        sendSourceRegion = sourceRegion;
        sendUnitQuantities = {};

        document.getElementById('sendUnitsSource').textContent =
            sourceRegion.x + ', ' + sourceRegion.y;

        document.getElementById('sendUnitsInfo').innerHTML =
            '<strong>Select units to move from</strong> region ' + sourceRegion.x + ', ' + sourceRegion.y;

        const container = document.getElementById('sendUnitsContainer');
        container.innerHTML = '<div class="build-loading">Loading available units...</div>';
        bootstrap.Modal.getOrCreateInstance(sendUnitsModal).show();

        try {
            const response = await fetch('/game/api/world/region/send-units-data/' + sourceRegion.id);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = '<div class="build-loading" style="color: #f44336;">' + result.message + '</div>';
                return;
            }

            if (result.units.length === 0) {
                container.innerHTML = '<div class="build-loading">No movable units in this region.</div>';
                confirmSendUnitsBtn.style.display = 'none';
                return;
            }

            confirmSendUnitsBtn.style.display = '';
            renderSendUnits(result.units);
        } catch (error) {
            console.error('Error loading send units data:', error);
            container.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load units.</div>';
        }
    }

    /**
     * Render unit cards (reuses the same card style as attack/build)
     */
    function renderSendUnits(units) {
        const container = document.getElementById('sendUnitsContainer');
        const imgBase = WorldApp.imageBasePath;
        container.innerHTML = '';

        units.forEach(function (unit) {
            const card = document.createElement('div');
            card.className = 'build-unit-card';
            card.innerHTML =
                '<div class="build-unit-header">' +
                    '<img src="' + imgBase + '/' + unit.imageDir + unit.image + '" alt="' + unit.name + '" class="build-unit-image">' +
                    '<div>' +
                        '<div class="build-unit-name">' + unit.name + '</div>' +
                        '<div class="build-unit-owned">Available: <strong>' + unit.amount + '</strong></div>' +
                        '<div style="font-size: 10px; color: #aaa;">' + unit.category + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="build-unit-input">' +
                    '<input type="number" min="0" max="' + unit.amount + '" value="0" data-unit-id="' + unit.gameUnitId + '" class="send-quantity-input" placeholder="0 / ' + unit.amount + '">' +
                '</div>';
            container.appendChild(card);
        });

        container.querySelectorAll('.send-quantity-input').forEach(function (input) {
            input.addEventListener('input', function (e) {
                const unitId = parseInt(e.target.dataset.unitId);
                const max = parseInt(e.target.max);
                let val = parseInt(e.target.value) || 0;
                if (val > max) { val = max; e.target.value = max; }
                if (val < 0) { val = 0; e.target.value = 0; }
                sendUnitQuantities[unitId] = val;
            });
        });
    }

    /**
     * Step 3→4: Player confirmed unit selection. Now load target regions and enter map selection mode.
     */
    async function enterSendTargetSelection() {
        const hasSelection = Object.values(sendUnitQuantities).some(function (qty) { return qty > 0; });
        if (!hasSelection) {
            showNotification('Select at least one unit to send!', 'error');
            return;
        }

        bootstrap.Modal.getInstance(sendUnitsModal).hide();
        showNotification('Loading target regions...', 'info');

        try {
            const response = await fetch('/game/api/world/region/send-units-targets/' + sendSourceRegion.id);
            const result = await response.json();

            if (!result.success) {
                showNotification(result.message || 'Failed to load targets', 'error');
                return;
            }

            if (result.targets.length === 0) {
                showNotification('You have no other regions to send units to!', 'error');
                return;
            }

            sendTargetData = result.targets;
            sendEligibleRegionIds = new Set(result.targets.map(function (t) { return t.regionId; }));

            // Enter send mode — highlight eligible regions on the map
            sendMode = true;

            const worldMap = WorldApp.worldMap;
            worldMap.sectors.forEach(function (region) {
                if (region.id === sendSourceRegion.id) {
                    region._sendSource = true;
                    region._attackEligible = false;
                    region._attackTarget = false;
                } else if (sendEligibleRegionIds.has(region.id)) {
                    region._attackEligible = true;  // Reuse the green highlight rendering
                    region._attackTarget = false;
                    region._sendSource = false;
                } else {
                    region._attackEligible = false;
                    region._attackTarget = false;
                    region._sendSource = false;
                }
            });

            // Override tile click to handle target selection
            worldMap.config.onTileClick = function (region) {
                if (sendEligibleRegionIds.has(region.id)) {
                    confirmSendToTarget(region);
                } else if (region.id === sendSourceRegion.id) {
                    cancelSendMode();
                } else {
                    showNotification('Select one of your highlighted regions as the target.', 'error');
                }
            };

            worldMap.render();
            showNotification(
                'Select a target region to send units to (' + result.targets.length + ' region(s) available). Click source or press Escape to cancel.',
                'info'
            );
        } catch (error) {
            console.error('Error loading send targets:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    }

    /**
     * Step 5: Player clicked a target region. Send the fleet via API.
     */
    async function confirmSendToTarget(targetRegion) {
        showNotification('Sending units...', 'info');

        try {
            const response = await fetch('/game/api/world/region/send-units/' + sendSourceRegion.id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    targetRegionId: targetRegion.id,
                    units: sendUnitQuantities
                })
            });

            const result = await response.json();

            if (result.success) {
                cancelSendMode();

                // Update source region units on the map
                if (result.sourceRegionId && result.sourceRegionUnits) {
                    WorldApp.worldMap.fleetManager.updateRegionUnits(result.sourceRegionId, result.sourceRegionUnits);
                    // Unit counts changed, so cached build data of this region is outdated
                    WorldBuild.invalidateCache(result.sourceRegionId);
                }

                // Add fleet to the map and start ETA countdown
                const newFleet = result.fleet;
                WorldApp.worldMap.fleetManager.fleets.push(newFleet);
                WorldApp.worldMap.fleetManager.startETACountdown();
                WorldApp.worldMap.render();

                showNotification(result.message, 'success');
            } else {
                showNotification(result.message || 'Failed to send units', 'error');
            }
        } catch (error) {
            console.error('Error sending units:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    }

    /**
     * Cancel send mode and restore the default map behavior.
     */
    function cancelSendMode() {
        sendMode = false;
        sendSourceRegion = null;
        sendEligibleRegionIds.clear();
        sendTargetData = [];
        sendUnitQuantities = {};

        const worldMap = WorldApp.worldMap;
        worldMap.sectors.forEach(function (region) {
            delete region._attackEligible;
            delete region._attackTarget;
            delete region._sendSource;
        });

        worldMap.config.onTileClick = getDefaultTileClick();
        worldMap.render();
        showNotification('Send units cancelled.', 'info');
    }

    // Cancel on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sendMode) {
            cancelSendMode();
        }
    });

    // Expose globally
    window.WorldSendUnits = {
        startSendUnits: startSendUnits
    };
})();
