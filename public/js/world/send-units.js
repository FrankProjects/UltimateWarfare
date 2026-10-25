/**
 * Send Units mode — allows sending units from your region to another of your regions, or ships
 * to open sea next to your regions, on the world map, reusing the attack mode highlight/select pattern.
 *
 * Flow:
 *   1. Player clicks own region → "Send Units" button
 *   2. Modal shows sendable units (troops, air, naval, special)
 *   3. Player selects quantities → clicks "Select Target"
 *   4. Map enters "send mode": your in-range regions are highlighted
 *   5. Player clicks a highlighted region → fleet is created via API
 *   6. Fleet appears on map with ETA countdown
 *
 * From the open sea modal, the player instead picks one of their regions with ships and sends
 * naval units straight to that open sea (no target selection).
 */
(function () {
    let sendMode = false;
    let sendSourceRegion = null;
    let sendUnitQuantities = {};
    let sendNavalUnitIds = new Set(); // Only ships can sail to water regions
    let sendFixedTarget = null; // Open sea the ships sail to, skipping target selection
    let sendTargetData = []; // [{regionId, x, y, distance}]
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
    async function startSendUnits(sourceRegion, fixedTarget = null) {
        // Close the "Your Region" modal
        // yourRegionModal is already closed by modals.js before calling this

        sendSourceRegion = sourceRegion;
        sendUnitQuantities = {};
        sendFixedTarget = fixedTarget;

        document.getElementById('sendUnitsSource').textContent =
            sourceRegion.x + ', ' + sourceRegion.y;

        document.getElementById('sendUnitsInfo').innerHTML = fixedTarget
            ? '<strong>Select ships to send from</strong> region ' + sourceRegion.x + ', ' + sourceRegion.y +
                ' <strong>to open sea</strong> ' + fixedTarget.x + ', ' + fixedTarget.y
            : '<strong>Select units to move from</strong> region ' + sourceRegion.x + ', ' + sourceRegion.y;
        confirmSendUnitsBtn.innerHTML = fixedTarget ? '&#9875; Send Ships' : '&#128640; Select Target';

        const container = document.getElementById('sendUnitsContainer');
        container.innerHTML = '<div class="build-loading">Loading available units...</div>';
        bootstrap.Modal.getOrCreateInstance(sendUnitsModal).show();

        try {
            const response = await fetch('/game/api/world/region/send-units-data/' + sourceRegion.id);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = '<div class="build-loading text-negative">' + result.message + '</div>';
                return;
            }

            // Only ships can sail to open sea
            const units = fixedTarget ? result.units.filter(function (unit) { return unit.isNaval; }) : result.units;
            if (units.length === 0) {
                container.innerHTML = '<div class="build-loading">No ' + (fixedTarget ? 'naval' : 'movable') + ' units in this region.</div>';
                confirmSendUnitsBtn.style.display = 'none';
                return;
            }

            confirmSendUnitsBtn.style.display = '';
            renderSendUnits(units);
        } catch (error) {
            console.error('Error loading send units data:', error);
            container.innerHTML = '<div class="build-loading text-negative">Failed to load units.</div>';
        }
    }

    /**
     * Render unit cards (reuses the same card style as attack/build)
     */
    function renderSendUnits(units) {
        sendNavalUnitIds = new Set(units.filter(function (unit) { return unit.isNaval; }).map(function (unit) { return unit.gameUnitId; }));
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
                        '<div class="unit-card-category">' + unit.category + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="build-unit-input">' +
                    '<input type="number" min="0" max="' + unit.amount + '" value="0" data-unit-id="' + unit.gameUnitId + '" class="form-control form-control-sm send-quantity-input" placeholder="0 / ' + unit.amount + '">' +
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

        if (sendFixedTarget) {
            confirmSendToTarget(sendFixedTarget);
            return;
        }

        showNotification('Loading target regions...', 'info');

        try {
            const response = await fetch('/game/api/world/region/send-units-targets/' + sendSourceRegion.id);
            const result = await response.json();

            if (!result.success) {
                showNotification(result.message || 'Failed to load targets', 'error');
                return;
            }

            const onlyNaval = Object.keys(sendUnitQuantities).every(function (unitId) {
                return sendUnitQuantities[unitId] === 0 || sendNavalUnitIds.has(parseInt(unitId));
            });
            const targets = result.targets.filter(function (t) { return onlyNaval || !t.isWater; });

            if (targets.length === 0) {
                showNotification('You have no other regions to send units to!', 'error');
                return;
            }

            sendTargetData = targets;
            sendEligibleRegionIds = new Set(targets.map(function (t) { return t.regionId; }));

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
                'Select a target region to send units to (' + targets.length + ' region(s) available). Click source or press Escape to cancel.',
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
                resetSendMode();

                // Update source region units on the map
                if (result.sourceRegionId && result.sourceRegionUnits) {
                    WorldApp.worldMap.fleetManager.updateRegionUnits(result.sourceRegionId, result.sourceRegionUnits);
                    // Unit counts changed, so cached build data of this region is outdated
                    WorldBuild.invalidateCache(result.sourceRegionId);
                }

                // A water region is released when its last units leave
                if (result.sourceRegionReleased) {
                    WorldModals.applyRegionRelease(result.sourceRegionId);
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
     * Send ships to open sea from one of your regions with naval units: directly when there is only one,
     * otherwise the player picks the source region on the map.
     */
    function startSendToOpenSea(targetRegion) {
        const worldMap = WorldApp.worldMap;
        const sources = worldMap.sectors.filter(function (region) {
            return region.isYours && region.id !== targetRegion.id && region.units && region.units.navalUnits > 0;
        });

        if (sources.length === 0) {
            showNotification('You have no naval ships to send!', 'error');
            return;
        }

        if (sources.length === 1) {
            startSendUnits(sources[0], targetRegion);
            return;
        }

        sendMode = true;
        const sourceIds = new Set(sources.map(function (region) { return region.id; }));
        worldMap.sectors.forEach(function (region) {
            region._attackEligible = sourceIds.has(region.id); // Reuse the green highlight rendering
            region._attackTarget = false;
            region._sendSource = false;
        });

        worldMap.config.onTileClick = function (region) {
            if (sourceIds.has(region.id)) {
                resetSendMode();
                startSendUnits(region, targetRegion);
            } else {
                showNotification('Select one of your highlighted regions with naval ships.', 'error');
            }
        };

        worldMap.render();
        showNotification(
            'Select the region to send ships from (' + sources.length + ' region(s) with ships). Press Escape to cancel.',
            'info'
        );
    }

    /**
     * Cancel send mode and restore the default map behavior.
     */
    function cancelSendMode() {
        resetSendMode();
        showNotification('Send units cancelled.', 'info');
    }

    /**
     * Leave send mode: clear the highlights and restore the default map behavior.
     */
    function resetSendMode() {
        sendMode = false;
        sendSourceRegion = null;
        sendFixedTarget = null;
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
    }

    // Cancel on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sendMode) {
            cancelSendMode();
        }
    });

    // Expose globally
    window.WorldSendUnits = {
        startSendUnits: startSendUnits,
        startSendToOpenSea: startSendToOpenSea
    };
})();
