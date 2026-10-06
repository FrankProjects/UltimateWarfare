/**
 * Destroy modal logic.
 * Depends on: build.js (WorldBuild.getBuildData, WorldBuild.applyBuildData), notifications.js (showNotification), WorldApp.imageBasePath.
 */
(function () {
    const destroyModal = document.getElementById('destroyModal');
    const confirmDestroyBtn = document.getElementById('confirmDestroyBtn');

    let selectedDestroyRegion = null;
    let selectedGameUnitCategoryId = null;
    let destroyQuantities = {};
    let availableCategories = [];

    async function showDestroyModal(region) {
        selectedDestroyRegion = region;
        destroyQuantities = {};
        document.getElementById('destroyRegionCoords').textContent = region.x + ', ' + region.y;
        bootstrap.Modal.getOrCreateInstance(destroyModal).show();

        const container = document.getElementById('destroyUnitsContainer');
        const tabsContainer = document.getElementById('destroyTabs');
        container.innerHTML = '<div class="build-loading">Loading...</div>';
        tabsContainer.innerHTML = '';

        try {
            const result = await WorldBuild.getBuildData(region.id);

            if (result.success) {
                renderCategories(result);
            } else {
                container.innerHTML = '<div class="build-loading" style="color: #f44336;">' + result.message + '</div>';
            }
        } catch (error) {
            console.error('Error loading destroy data:', error);
            container.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load data</div>';
        }
    }

    function renderCategories(result) {
        // Filter to only categories that have owned units
        availableCategories = result.categories.filter(function (cat) {
            return cat.units.some(function (u) { return u.owned > 0; });
        });

        if (availableCategories.length === 0) {
            const container = document.getElementById('destroyUnitsContainer');
            container.innerHTML = '<div class="build-loading">No units to destroy in this region.</div>';
            return;
        }

        selectedGameUnitCategoryId = availableCategories[0].id;

        const tabsContainer = document.getElementById('destroyTabs');
        tabsContainer.innerHTML = '';
        availableCategories.forEach(function (cat, index) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (index === 0 ? ' active' : '');
            tab.textContent = cat.name;
            tab.addEventListener('click', function () {
                selectedGameUnitCategoryId = cat.id;
                document.querySelectorAll('#destroyTabs .build-tab').forEach(function (t, i) {
                    t.classList.toggle('active', availableCategories[i].id === cat.id);
                });
                loadDestroyData(cat);
            });
            tabsContainer.appendChild(tab);
        });

        loadDestroyData(availableCategories[0]);
    }

    function loadDestroyData(category) {
        destroyQuantities = {};
        const units = category.units.filter(function (u) { return u.owned > 0; });
        renderDestroyUnits(units);
    }

    function renderDestroyUnits(units) {
        const container = document.getElementById('destroyUnitsContainer');
        const imgBase = WorldApp.imageBasePath;

        if (units.length === 0) {
            container.innerHTML = '<div class="build-loading">No units to destroy in this category</div>';
            return;
        }

        container.innerHTML = '';
        units.forEach(function (unit) {
            const card = document.createElement('div');
            card.className = 'build-unit-card';

            card.innerHTML =
                '<div class="build-unit-header">' +
                    '<img src="' + imgBase + '/' + unit.imageDir + unit.image + '" alt="' + unit.name + '" class="build-unit-image">' +
                    '<div>' +
                        '<div class="build-unit-name">' + unit.name + '</div>' +
                        '<div class="build-unit-owned">You have: ' + unit.owned + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="build-unit-input">' +
                    '<input type="number" min="0" max="' + unit.owned + '" value="0" data-unit-id="' + unit.gameUnitEnum + '" class="destroy-quantity-input">' +
                '</div>';

            container.appendChild(card);
        });

        container.querySelectorAll('.destroy-quantity-input').forEach(function (input) {
            input.addEventListener('input', function (e) {
                destroyQuantities[parseInt(e.target.dataset.unitId)] = parseInt(e.target.value) || 0;
            });
        });
    }

    async function confirmDestroy() {
        const hasSelection = Object.values(destroyQuantities).some(function (qty) { return qty > 0; });
        if (!hasSelection) {
            showNotification('Please select at least one unit to destroy', 'error');
            return;
        }

        confirmDestroyBtn.disabled = true;
        confirmDestroyBtn.textContent = 'Destroying...';

        try {
            const response = await fetch('/game/api/world/region/destroy/' + selectedDestroyRegion.id + '/' + selectedGameUnitCategoryId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ destroy: destroyQuantities })
            });

            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                bootstrap.Modal.getInstance(destroyModal).hide();

                // Update the build data cache and the worldmap unit display
                WorldBuild.applyBuildData(result.buildData);

                selectedDestroyRegion = null;
                destroyQuantities = {};
            } else {
                showNotification(result.message || 'Failed to destroy units', 'error');
            }
        } catch (error) {
            console.error('Error destroying units:', error);
            showNotification('An error occurred while destroying. Please try again.', 'error');
        } finally {
            confirmDestroyBtn.disabled = false;
            confirmDestroyBtn.textContent = 'Destroy Selected';
        }
    }

    destroyModal.addEventListener('hidden.bs.modal', function () { selectedDestroyRegion = null; });
    confirmDestroyBtn.onclick = confirmDestroy;

    window.WorldDestroy = {
        showDestroyModal: showDestroyModal
    };
})();
