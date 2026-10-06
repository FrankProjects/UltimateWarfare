/**
 * Attack mode & attack unit selection logic.
 * Depends on: notifications.js, WorldApp.worldMap, WorldApp.imageBasePath
 */
(function () {
    let attackMode = false;
    let attackTargetRegion = null;
    let attackEligibleRegionIds = new Set();
    let attackUnitQuantities = {};
    let currentAttackSource = null;
    let currentAttackTarget = null;

    const attackUnitsModal = document.getElementById('attackUnitsModal');
    const confirmAttackUnitsBtn = document.getElementById('confirmAttackUnitsBtn');
    const attackModeBanner = document.getElementById('attackModeBanner');

    confirmAttackUnitsBtn.onclick = sendAttackFleet;
    document.getElementById('cancelAttackModeBtn').onclick = cancelAttackMode;

    function getDefaultTileClick() {
        return WorldApp.defaultTileClick;
    }

    async function startAttackFromSelection(enemyRegion) {
        const enemyInstance = bootstrap.Modal.getInstance(document.getElementById('enemyRegionModal'));
        if (enemyInstance) enemyInstance.hide();
        showNotification('Loading eligible regions...', 'info');

        try {
            const response = await fetch('/game/api/world/region/attack-from/' + enemyRegion.id);
            const result = await response.json();

            if (!result.success) {
                showNotification(result.message || 'Failed to load attack data', 'error');
                return;
            }

            if (result.eligibleRegions.length === 0) {
                showNotification('You have no regions in range to attack from! Move troops closer first.', 'error');
                return;
            }

            attackMode = true;
            attackTargetRegion = enemyRegion;
            attackEligibleRegionIds = new Set(result.eligibleRegions.map(function (r) { return r.regionId; }));

            const worldMap = WorldApp.worldMap;
            worldMap.sectors.forEach(function (region) {
                region._attackEligible = attackEligibleRegionIds.has(region.id);
                region._attackTarget = (region.id === enemyRegion.id);
            });

            worldMap.config.onTileClick = function (region) {
                if (region._attackEligible) {
                    showAttackUnitsModal(attackTargetRegion, region);
                } else if (region._attackTarget) {
                    cancelAttackMode();
                } else {
                    showNotification('This region cannot attack the target. Select a highlighted region.', 'error');
                }
            };

            worldMap.render();
            attackModeBanner.style.display = 'flex';
            document.getElementById('attackModeText').textContent =
                'Attack Mode: Select one of ' + result.eligibleRegions.length + ' highlighted region(s) to attack from. Click target or press Escape to cancel.';
        } catch (error) {
            console.error('Error loading attack-from regions:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    }

    function cancelAttackMode() {
        attackMode = false;
        attackTargetRegion = null;
        attackEligibleRegionIds.clear();

        const worldMap = WorldApp.worldMap;
        worldMap.sectors.forEach(function (region) {
            delete region._attackEligible;
            delete region._attackTarget;
        });

        worldMap.config.onTileClick = getDefaultTileClick();
        worldMap.render();
        attackModeBanner.style.display = 'none';
    }

    async function showAttackUnitsModal(targetRegion, sourceRegion) {
        currentAttackTarget = targetRegion;
        currentAttackSource = sourceRegion;
        attackUnitQuantities = {};

        document.getElementById('attackUnitsTarget').textContent =
            targetRegion.x + ',' + targetRegion.y + ' (' + targetRegion.ownerName + ')';

        document.getElementById('attackUnitsInfo').innerHTML =
            '<strong>Attacking from:</strong> ' + sourceRegion.x + ', ' + sourceRegion.y +
            ' → <strong>Target:</strong> ' + targetRegion.x + ', ' + targetRegion.y;

        const container = document.getElementById('attackUnitsContainer');
        container.innerHTML = '<div class="build-loading">Loading available units...</div>';
        bootstrap.Modal.getOrCreateInstance(attackUnitsModal).show();

        try {
            const response = await fetch('/game/api/world/region/attack-units/' + targetRegion.id + '/' + sourceRegion.id);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = '<div class="build-loading" style="color: #f44336;">' + result.message + '</div>';
                return;
            }
            if (result.units.length === 0) {
                container.innerHTML = '<div class="build-loading">No eligible units in this region for this attack.</div>';
                return;
            }
            renderAttackUnits(result.units);
        } catch (error) {
            console.error('Error loading attack units:', error);
            container.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load units</div>';
        }
    }

    function renderAttackUnits(units) {
        const container = document.getElementById('attackUnitsContainer');
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
                    '<input type="number" min="0" max="' + unit.amount + '" value="0" data-unit-id="' + unit.gameUnitId + '" class="attack-quantity-input" placeholder="0 / ' + unit.amount + '">' +
                '</div>';
            container.appendChild(card);
        });

        container.querySelectorAll('.attack-quantity-input').forEach(function (input) {
            input.addEventListener('input', function (e) {
                let unitId = parseInt(e.target.dataset.unitId);
                let max = parseInt(e.target.max);
                let val = parseInt(e.target.value) || 0;
                if (val > max) { val = max; e.target.value = max; }
                if (val < 0) { val = 0; e.target.value = 0; }
                attackUnitQuantities[unitId] = val;
            });
        });
    }

    async function sendAttackFleet() {
        const hasSelection = Object.values(attackUnitQuantities).some(function (qty) { return qty > 0; });
        if (!hasSelection) {
            showNotification('Select at least one unit to send!', 'error');
            return;
        }

        confirmAttackUnitsBtn.disabled = true;
        confirmAttackUnitsBtn.textContent = '⚔ Sending...';

        try {
            const response = await fetch(
                '/game/api/world/region/attack-send/' + currentAttackTarget.id + '/' + currentAttackSource.id,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ units: attackUnitQuantities }),
                }
            );

            const result = await response.json();

            if (result.success) {
                bootstrap.Modal.getInstance(attackUnitsModal).hide();
                cancelAttackMode();

                // Update source region units on the map
                if (result.sourceRegionId && result.sourceRegionUnits) {
                    WorldApp.worldMap.fleetManager.updateRegionUnits(result.sourceRegionId, result.sourceRegionUnits);
                    // Unit counts changed, so cached build data of this region is outdated
                    WorldBuild.invalidateCache(result.sourceRegionId);
                }

                const newFleet = result.fleet;
                WorldApp.worldMap.fleetManager.fleets.push(newFleet);
                WorldApp.worldMap.fleetManager.startETACountdown();
                WorldApp.worldMap.render();

                showNotification(result.message, 'success');
            } else {
                showNotification(result.message || 'Failed to send attack', 'error');
            }
        } catch (error) {
            console.error('Error sending attack:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            confirmAttackUnitsBtn.disabled = false;
            confirmAttackUnitsBtn.textContent = '⚔ Send Attack';
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && attackMode) {
            cancelAttackMode();
        }
    });

    // Expose globally
    window.WorldAttack = {
        startAttackFromSelection: startAttackFromSelection
    };
})();
