/**
 * Build modal logic.
 * Depends on: notifications.js (showNotification), and WorldApp.imageBasePath being set.
 */
(function () {
    const buildModal = document.getElementById('buildModal');
    const confirmBuildBtn = document.getElementById('confirmBuildBtn');

    let selectedBuildRegion = null;
    let currentBuildData = null;
    let selectedGameUnitCategoryId = 1;
    let buildQuantities = {};
    let availableCategories = [];
    let buildTimerInterval = null;

    // ===== Build data cache =====
    const buildDataCache = {};
    const CACHE_TTL_MS = 30000;

    // ===== Resource / time icons for the cost row (all illustrated SVG assets) =====
    const COST_ICON_FILE = {
        cash: 'resource_cash.svg',
        wood: 'resource_wood.svg',
        steel: 'resource_steel.svg',
        food: 'resource_grain_wheat.svg',
        time: 'time.svg'
    };

    function costRowHtml(items) {
        return '<div class="build-unit-costs">' + items.map(function (item) {
            const type = item[0];
            const value = item[1];
            const display = typeof value === 'number' ? value.toLocaleString() : value;
            return '<div class="build-unit-cost-item build-cost-' + type + '">' +
                '<img class="build-cost-img" src="' + WorldApp.imageBasePath + '/icons/' + COST_ICON_FILE[type] + '" alt="">' +
                '<span>' + display + '</span></div>';
        }).join('') + '</div>';
    }

    function getCachedData(regionId) {
        const entry = buildDataCache[regionId];
        if (!entry) return null;
        return entry;
    }

    function isCacheFresh(regionId) {
        const entry = buildDataCache[regionId];
        if (!entry) return false;
        return (Date.now() - entry.timestamp) < CACHE_TTL_MS;
    }

    function setCachedData(regionId, data) {
        buildDataCache[regionId] = { data: data, timestamp: Date.now() };
    }

    // In-flight requests per region, so concurrent callers share a single request
    const pendingRequests = {};

    // Refresh the region's unit icons on the world map from an API response
    function updateMapRegionUnits(result) {
        if (result.regionId && result.units && WorldApp.worldMap) {
            WorldApp.worldMap.fleetManager.updateRegionUnits(result.regionId, result.units);
            WorldApp.worldMap.render();
        }
    }

    function fetchAllBuildData(regionId) {
        if (pendingRequests[regionId]) return pendingRequests[regionId];

        const request = fetch('/game/api/world/region/all-build-data/' + regionId)
            .then(function (response) { return response.json(); })
            .then(function (result) {
                // Don't cache responses of requests that were invalidated while in flight
                if (result.success && pendingRequests[regionId] === request) {
                    setCachedData(regionId, result);
                    updateMapRegionUnits(result);
                }
                return result;
            })
            .finally(function () {
                if (pendingRequests[regionId] === request) delete pendingRequests[regionId];
            });

        pendingRequests[regionId] = request;
        return request;
    }

    function invalidateCache(regionId) {
        delete buildDataCache[regionId];
        delete pendingRequests[regionId];
    }

    // Store up-to-date build data returned by an action response, so reopening the modal needs no request
    function applyBuildData(buildData) {
        if (!buildData) return;

        // A response of a request still in flight would be older, so don't let it overwrite this
        delete pendingRequests[buildData.regionId];
        setCachedData(buildData.regionId, buildData);
        updateMapRegionUnits(buildData);
    }

    // Returns fresh cached data when available, otherwise fetches it
    async function getBuildData(regionId) {
        if (isCacheFresh(regionId)) return getCachedData(regionId).data;
        return fetchAllBuildData(regionId);
    }

    // ===== Unit Info Tooltip =====
    const unitInfoTooltip = document.getElementById('unitInfoTooltip');
    let currentTooltipEvent = null;

    function showUnitInfoTooltip(event, unit) {
        currentTooltipEvent = event;
        const imgBase = WorldApp.imageBasePath;

        let tooltipHtml = '<div class="unit-info-header">' +
            '<img src="' + imgBase + '/' + unit.imageDir + unit.image + '" alt="' + unit.name + '" class="unit-info-image">' +
            '<div class="unit-info-title"><div class="unit-info-name">' + unit.name + '</div></div></div>';

        if (unit.description) {
            tooltipHtml += '<div class="unit-info-description">' + unit.description + '</div>';
        }

        tooltipHtml += '<div class="unit-info-section"><div class="unit-info-section-title">Build Cost</div><div class="unit-info-grid">';

        const costItems = [
            { key: 'costCash', icon: 'resource_cash.svg', label: 'Cash' },
            { key: 'costWood', icon: 'resource_wood.svg', label: 'Wood' },
            { key: 'costSteel', icon: 'resource_steel.svg', label: 'Steel' },
            { key: 'costFood', icon: 'resource_grain_wheat.svg', label: 'Food' }
        ];
        costItems.forEach(function (item) {
            if (unit[item.key] > 0) {
                tooltipHtml += '<div class="unit-info-item">' +
                    '<img src="' + imgBase + '/icons/' + item.icon + '" class="unit-info-icon">' +
                    '<span class="unit-info-label">' + item.label + ':</span>' +
                    '<span class="unit-info-value">' + unit[item.key].toLocaleString() + '</span></div>';
            }
        });
        tooltipHtml += '</div></div>';

        const incomeItems = [
            { key: 'incomeCash', icon: 'resource_cash.svg', label: 'Cash' },
            { key: 'incomeWood', icon: 'resource_wood.svg', label: 'Wood' },
            { key: 'incomeSteel', icon: 'resource_steel.svg', label: 'Steel' },
            { key: 'incomeFood', icon: 'resource_grain_wheat.svg', label: 'Food' }
        ];
        const hasIncome = incomeItems.some(function (i) { return unit[i.key] > 0; });
        if (hasIncome) {
            tooltipHtml += '<div class="unit-info-section"><div class="unit-info-section-title">Income (per hour)</div><div class="unit-info-grid">';
            incomeItems.forEach(function (item) {
                if (unit[item.key] > 0) {
                    tooltipHtml += '<div class="unit-info-item">' +
                        '<img src="' + imgBase + '/icons/' + item.icon + '" class="unit-info-icon">' +
                        '<span class="unit-info-label">' + item.label + ':</span>' +
                        '<span class="unit-info-value positive">+' + unit[item.key].toLocaleString() + '</span></div>';
                }
            });
            tooltipHtml += '</div></div>';
        }

        const upkeepItems = [
            { key: 'upkeepCash', icon: 'resource_cash.svg', label: 'Cash' },
            { key: 'upkeepWood', icon: 'resource_wood.svg', label: 'Wood' },
            { key: 'upkeepSteel', icon: 'resource_steel.svg', label: 'Steel' },
            { key: 'upkeepFood', icon: 'resource_grain_wheat.svg', label: 'Food' }
        ];
        const hasUpkeep = upkeepItems.some(function (i) { return unit[i.key] > 0; });
        if (hasUpkeep) {
            tooltipHtml += '<div class="unit-info-section"><div class="unit-info-section-title">Upkeep (per hour)</div><div class="unit-info-grid">';
            upkeepItems.forEach(function (item) {
                if (unit[item.key] > 0) {
                    tooltipHtml += '<div class="unit-info-item">' +
                        '<img src="' + imgBase + '/icons/' + item.icon + '" class="unit-info-icon">' +
                        '<span class="unit-info-label">' + item.label + ':</span>' +
                        '<span class="unit-info-value negative">-' + unit[item.key].toLocaleString() + '</span></div>';
                }
            });
            tooltipHtml += '</div></div>';
        }

        const hours = Math.floor(unit.timestamp / 3600);
        const minutes = Math.floor((unit.timestamp % 3600) / 60);
        const seconds = unit.timestamp % 60;
        const timeStr = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        tooltipHtml += '<div class="unit-info-section"><div class="unit-info-grid">' +
            '<div class="unit-info-item"><span class="unit-info-label">Net Worth:</span>' +
            '<span class="unit-info-value">' + unit.netWorth.toLocaleString() + '</span></div>' +
            '<div class="unit-info-item"><img src="' + imgBase + '/icons/time.svg" class="unit-info-icon">' +
            '<span class="unit-info-label">Build Time:</span>' +
            '<span class="unit-info-value">' + timeStr + '</span></div></div></div>';

        unitInfoTooltip.innerHTML = tooltipHtml;
        unitInfoTooltip.style.display = 'block';
        updateUnitInfoTooltipPosition(event);
    }

    function hideUnitInfoTooltip() {
        unitInfoTooltip.style.display = 'none';
        currentTooltipEvent = null;
    }

    function updateUnitInfoTooltipPosition(event) {
        const e = event || currentTooltipEvent;
        if (!e) return;

        const tooltipWidth = unitInfoTooltip.offsetWidth;
        const tooltipHeight = unitInfoTooltip.offsetHeight;
        const padding = 15;

        let left = e.clientX + padding;
        let top = e.clientY + padding;

        if (left + tooltipWidth > window.innerWidth) left = e.clientX - tooltipWidth - padding;
        if (top + tooltipHeight > window.innerHeight) top = e.clientY - tooltipHeight - padding;

        unitInfoTooltip.style.left = left + 'px';
        unitInfoTooltip.style.top = top + 'px';
    }

    // ===== Build Data =====
    function renderFromCache(result) {
        availableCategories = result.categories;

        if (availableCategories.length === 0) {
            const container = document.getElementById('buildUnitsContainer');
            container.innerHTML = '<div class="build-loading">No build options available for this region.</div>';
            return;
        }

        selectedGameUnitCategoryId = availableCategories[0].id;

        const tabsContainer = document.getElementById('buildTabs');
        tabsContainer.innerHTML = '';
        availableCategories.forEach(function (cat, index) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (index === 0 ? ' active' : '');
            tab.textContent = cat.name;
            tab.addEventListener('click', function () {
                selectedGameUnitCategoryId = cat.id;
                document.querySelectorAll('#buildTabs .build-tab').forEach(function (t, i) {
                    t.classList.toggle('active', availableCategories[i].id === cat.id);
                });
                loadBuildData(cat.id);
            });
            tabsContainer.appendChild(tab);
        });

        loadBuildData(selectedGameUnitCategoryId);
    }

    async function showBuildModal(region) {
        selectedBuildRegion = region;
        buildQuantities = {};
        document.getElementById('buildRegionCoords').textContent = region.x + ', ' + region.y;
        bootstrap.Modal.getOrCreateInstance(buildModal).show();

        const container = document.getElementById('buildUnitsContainer');
        const tabsContainer = document.getElementById('buildTabs');

        const cached = getCachedData(region.id);
        if (cached) {
            // Render from cache immediately
            renderFromCache(cached.data);

            // If cache is stale, re-fetch in background and update if data changed
            if (!isCacheFresh(region.id)) {
                try {
                    const result = await fetchAllBuildData(region.id);
                    // Re-render current tab if modal is still showing this region
                    if (result.success && selectedBuildRegion && selectedBuildRegion.id === region.id) {
                        loadBuildData(selectedGameUnitCategoryId);
                    }
                } catch (e) {
                    // Keep showing stale data
                }
            }
            return;
        }

        // No cache - show loading and fetch
        container.innerHTML = '<div class="build-loading">Loading...</div>';
        tabsContainer.innerHTML = '';
        document.getElementById('buildSpaceInfo').style.display = 'none';

        try {
            const result = await fetchAllBuildData(region.id);
            if (result.success) {
                renderFromCache(result);
            } else {
                container.innerHTML = '<div class="build-loading" style="color: #f44336;">' + result.message + '</div>';
            }
        } catch (error) {
            console.error('Error loading build data:', error);
            container.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load build data</div>';
        }
    }

    function loadBuildData(gameUnitCategoryId) {
        const cached = selectedBuildRegion ? getCachedData(selectedBuildRegion.id) : null;
        if (!cached) return;

        const category = cached.data.categories.find(function (c) { return c.id === gameUnitCategoryId; });
        if (!category) return;

        buildQuantities = {};
        currentBuildData = {
            gameUnitCategory: { id: category.id, name: category.name },
            spaceLeft: cached.data.spaceLeft,
            units: category.units,
            // Seconds since the data was fetched, to correct construction countdowns
            ageSeconds: Math.floor((Date.now() - cached.timestamp) / 1000)
        };
        renderBuildUnits(currentBuildData);
    }

    function renderBuildUnits(data) {
        const container = document.getElementById('buildUnitsContainer');
        const spaceInfo = document.getElementById('buildSpaceInfo');
        const imgBase = WorldApp.imageBasePath;
        const isLeveledCategory = data.gameUnitCategory.id === 2 || data.gameUnitCategory.id === 3;

        // Leveled categories use per-unit Build/Upgrade buttons; hide the bulk button.
        confirmBuildBtn.style.display = isLeveledCategory ? 'none' : '';

        if (data.gameUnitCategory.id === 1) {
            spaceInfo.style.display = 'block';
            spaceInfo.innerHTML = 'You have <span style="color: #4CAF50;">' + data.spaceLeft + '</span> building space left on this region.';
        } else {
            spaceInfo.style.display = 'none';
        }

        if (data.units.length === 0) {
            container.innerHTML = '<div class="build-loading">No units available in this category</div>';
            return;
        }

        container.innerHTML = '';
        data.units.forEach(function (unit) {
            const card = document.createElement('div');
            card.className = 'build-unit-card';

            const hours = Math.floor(unit.timestamp / 3600);
            const minutes = Math.floor((unit.timestamp % 3600) / 60);
            const seconds = unit.timestamp % 60;
            const timeStr = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            // Escalating cost for leveled buildings: base cost x the next actionable level.
            const nextLevel = unit.isLeveled ? (unit.level + 1) : 1;
            const costMultiplier = unit.isLeveled ? nextLevel : 1;
            const costCash = unit.costCash * costMultiplier;
            const costWood = unit.costWood * costMultiplier;
            const costSteel = unit.costSteel * costMultiplier;

            let statusHtml;
            let actionHtml;
            if (unit.isLeveled) {
                const inProgress = unit.inConstruction > 0;
                const levelText = unit.level > 0 ? ('Level ' + unit.level) : 'Not built';
                statusHtml = '<div class="build-unit-owned">' + levelText + '</div>';

                // Show a live "under construction" countdown while a build/upgrade is queued.
                if (inProgress) {
                    const timeLeft = Math.max(0, unit.constructionTimeLeft - data.ageSeconds);
                    statusHtml +=
                        '<div class="build-unit-construction">' +
                            '<span class="build-construction-label">Under construction</span>' +
                            '<span class="build-unit-timer" data-timeleft="' + timeLeft + '">' + formatTime(timeLeft) + '</span>' +
                        '</div>';
                }

                // Show a color-coded health bar once the building exists.
                if (unit.level > 0 && unit.maxHealth > 0) {
                    const pct = Math.max(0, Math.min(100, Math.round(unit.health / unit.maxHealth * 100)));
                    const tier = pct >= 66 ? 'health-high' : (pct >= 33 ? 'health-mid' : 'health-low');
                    statusHtml +=
                        '<div class="build-unit-health">' +
                            '<div class="build-unit-health-bar">' +
                                '<div class="build-unit-health-fill ' + tier + '" style="width:' + pct + '%"></div>' +
                            '</div>' +
                            '<span class="build-unit-health-label">HP ' + unit.health.toLocaleString() + ' / ' + unit.maxHealth.toLocaleString() + '</span>' +
                        '</div>';
                }

                let buttons = '';
                if (inProgress) {
                    const inProgressLabel = unit.level === 0 ? 'Building…' : ('Upgrading to level ' + (unit.level + 1) + '…');
                    buttons += '<button class="build-action-btn build-action-primary" disabled>' + inProgressLabel + '</button>';
                } else if (unit.canBuild) {
                    if (unit.level >= unit.maxLevel) {
                        buttons += '<button class="build-action-btn build-action-primary" disabled>Max level</button>';
                    } else {
                        const action = unit.level === 0 ? 'build' : 'upgrade';
                        const label = unit.level === 0 ? 'Build' : ('Upgrade to level ' + nextLevel);
                        buttons += '<button class="build-action-btn build-action-primary" data-unit-id="' + unit.gameUnitEnum + '" data-action="' + action + '">' + label + '</button>';
                    }
                }
                if (unit.level > 0 && unit.health < unit.maxHealth) {
                    buttons += '<button class="build-action-btn build-action-repair" data-unit-id="' + unit.gameUnitEnum + '" data-action="repair">Repair</button>';
                }
                actionHtml = buttons ? ('<div class="build-unit-actions">' + buttons + '</div>') : '';
            } else {
                const constructionText = unit.inConstruction > 0 ? ' (' + unit.inConstruction + ')' : '';
                statusHtml = '<div class="build-unit-owned">You have: ' + unit.owned + constructionText + '</div>';
                actionHtml = '<div class="build-unit-input">' +
                    '<input type="number" min="0" value="0" data-unit-id="' + unit.gameUnitEnum + '" class="build-quantity-input">' +
                '</div>';
            }

            card.innerHTML =
                '<div class="build-unit-header ' + (!unit.canBuild ? 'unit-locked' : '') + '">' +
                    '<img src="' + imgBase + '/' + unit.imageDir + unit.image + '" alt="' + unit.name + '" class="build-unit-image ' + (!unit.canBuild ? 'grayscale' : '') + '">' +
                    '<div>' +
                        '<div class="build-unit-name">' + unit.name + (!unit.canBuild ? ' 🔒' : '') +
                            ' <span class="build-unit-info-icon" data-unit-id="' + unit.gameUnitEnum + '">i</span></div>' +
                        (!unit.canBuild
                            ? '<div class="build-requirement">' + unit.buildRequirement + '</div>'
                            : statusHtml) +
                    '</div>' +
                '</div>' +
                costRowHtml([['cash', costCash], ['wood', costWood], ['steel', costSteel], ['time', timeStr]]) +
                actionHtml;

            container.appendChild(card);

            const infoIcon = card.querySelector('.build-unit-info-icon');
            infoIcon.addEventListener('mouseenter', function (e) { e.stopPropagation(); showUnitInfoTooltip(e, unit); });
            infoIcon.addEventListener('mouseleave', function (e) { e.stopPropagation(); hideUnitInfoTooltip(); });
            infoIcon.addEventListener('mousemove', function (e) { e.stopPropagation(); updateUnitInfoTooltipPosition(e); });

            const qtyInput = card.querySelector('.build-quantity-input');
            if (qtyInput) {
                qtyInput.disabled = !unit.canBuild;
                qtyInput.addEventListener('input', function (e) {
                    buildQuantities[parseInt(e.target.dataset.unitId)] = parseInt(e.target.value) || 0;
                });
            }

            card.querySelectorAll('.build-action-btn[data-unit-id]').forEach(function (actionBtn) {
                actionBtn.addEventListener('click', function () {
                    leveledAction(parseInt(actionBtn.dataset.unitId), actionBtn.dataset.action, actionBtn);
                });
            });
        });

        startBuildTimers();
    }

    function formatTime(seconds) {
        if (seconds <= 0) { return 'Done'; }
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        const parts = [];
        if (h > 0) { parts.push(h + 'h'); }
        if (m > 0) { parts.push(m + 'm'); }
        parts.push(s + 's');
        return parts.join(' ');
    }

    function stopBuildTimers() {
        if (buildTimerInterval) {
            clearInterval(buildTimerInterval);
            buildTimerInterval = null;
        }
    }

    function startBuildTimers() {
        stopBuildTimers();

        const container = document.getElementById('buildUnitsContainer');
        if (!container.querySelector('.build-unit-timer')) {
            return;
        }

        buildTimerInterval = setInterval(function () {
            const timers = container.querySelectorAll('.build-unit-timer');
            let completed = false;
            timers.forEach(function (el) {
                let timeLeft = parseInt(el.getAttribute('data-timeleft'), 10) - 1;
                if (timeLeft <= 0) {
                    timeLeft = 0;
                    completed = true;
                }
                el.setAttribute('data-timeleft', String(timeLeft));
                el.textContent = formatTime(timeLeft);
            });

            // A construction just finished: refresh so the new level/health bar/buttons appear.
            if (completed) {
                stopBuildTimers();
                refreshBuildData();
            }
        }, 1000);
    }

    // Re-fetch build data for the open region and re-render, keeping the modal open.
    async function refreshBuildData() {
        if (!selectedBuildRegion) return;

        const regionId = selectedBuildRegion.id;
        invalidateCache(regionId);
        const refreshed = await fetchAllBuildData(regionId);
        if (refreshed.success && selectedBuildRegion && selectedBuildRegion.id === regionId) {
            loadBuildData(selectedGameUnitCategoryId);
        }
    }

    async function leveledAction(unitId, action, button) {
        if (!selectedBuildRegion) return;

        const originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = 'Working...';

        try {
            const response = await fetch('/game/api/world/region/leveled/' + action + '/' + selectedBuildRegion.id + '/' + unitId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const amounts = document.querySelectorAll('.resource-amount');
                if (result.newCash !== undefined && amounts[0]) amounts[0].textContent = result.newCash.toLocaleString('en-US');
                if (result.newWood !== undefined && amounts[1]) amounts[1].textContent = result.newWood.toLocaleString('en-US');
                if (result.newSteel !== undefined && amounts[3]) amounts[3].textContent = result.newSteel.toLocaleString('en-US');

                showNotification(result.message, 'success');

                // Re-render from the returned build data so level/construction state updates; keep the modal open.
                applyBuildData(result.buildData);
                if (selectedBuildRegion && result.buildData && selectedBuildRegion.id === result.buildData.regionId) {
                    loadBuildData(selectedGameUnitCategoryId);
                }
            } else {
                showNotification(result.message || 'Failed to build', 'error');
                button.disabled = false;
                button.textContent = originalLabel;
            }
        } catch (error) {
            console.error('Error building unit:', error);
            showNotification('An error occurred while building. Please try again.', 'error');
            button.disabled = false;
            button.textContent = originalLabel;
        }
    }

    async function confirmBuild() {
        const hasSelection = Object.values(buildQuantities).some(function (qty) { return qty > 0; });
        if (!hasSelection) {
            showNotification('Please select at least one unit to build', 'error');
            return;
        }

        confirmBuildBtn.disabled = true;
        confirmBuildBtn.textContent = 'Building...';

        try {
            const response = await fetch('/game/api/world/region/build/' + selectedBuildRegion.id + '/' + selectedGameUnitCategoryId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ construct: buildQuantities })
            });

            const result = await response.json();

            if (result.success) {
                const amounts = document.querySelectorAll('.resource-amount');
                if (result.newCash !== undefined && amounts[0]) amounts[0].textContent = result.newCash.toLocaleString('en-US');
                if (result.newWood !== undefined && amounts[1]) amounts[1].textContent = result.newWood.toLocaleString('en-US');
                if (result.newSteel !== undefined && amounts[3]) amounts[3].textContent = result.newSteel.toLocaleString('en-US');

                showNotification(result.message, 'success');
                applyBuildData(result.buildData);
                bootstrap.Modal.getInstance(buildModal).hide();

                selectedBuildRegion = null;
                buildQuantities = {};
            } else {
                showNotification(result.message || 'Failed to build units', 'error');
            }
        } catch (error) {
            console.error('Error building units:', error);
            showNotification('An error occurred while building. Please try again.', 'error');
        } finally {
            confirmBuildBtn.disabled = false;
            confirmBuildBtn.textContent = 'Build Selected';
        }
    }

    buildModal.addEventListener('hidden.bs.modal', function () { selectedBuildRegion = null; stopBuildTimers(); });
    confirmBuildBtn.onclick = confirmBuild;

    // Expose globally
    window.WorldBuild = {
        showBuildModal: showBuildModal,
        getBuildData: getBuildData,
        applyBuildData: applyBuildData,
        invalidateCache: invalidateCache
    };
})();
