/**
 * Map initialization, UI controls, and unit tooltip.
 * Depends on: notifications.js, modals.js (WorldApp.defaultTileClick)
 */
(function () {
    const worldRegions = WorldApp.worldRegions;
    const playerFleets = WorldApp.playerFleets;
    const playerBombardments = WorldApp.playerBombardments || [];

    // Find the player's first owned region for centering
    let homeRegion = null;
    for (let i = 0; i < worldRegions.length; i++) {
        if (worldRegions[i].isYours) {
            homeRegion = worldRegions[i];
            break;
        }
    }

    console.log('Loading ' + worldRegions.length + ' world regions...');

    const worldMap = new HexMap('worldMap', {
        hexSize: 40,
        imageBasePath: WorldApp.imageBasePath,
        overlaysEnabled: false,
        onTileClick: WorldApp.defaultTileClick
    });

    WorldApp.worldMap = worldMap;

    function centerOnHomeRegion() {
        if (homeRegion) {
            const pos = worldMap.hexToPixel(homeRegion.x, homeRegion.y);
            worldMap.cameraController.resetView(pos.x, pos.y);
        } else {
            worldMap.cameraController.resetView();
        }
    }

    worldMap.setSectors(worldRegions).then(function () {
        worldMap.setFleets(playerFleets);
        worldMap.setBombardments(playerBombardments);
        centerOnHomeRegion();
        console.log('World map loaded successfully with ' + worldRegions.length + ' regions!');

        if (WorldApp.showWelcome) {
            var welcomeModal = document.getElementById('welcomeModal');
            if (welcomeModal) {
                bootstrap.Modal.getOrCreateInstance(welcomeModal).show();
            }
        }
    }).catch(function (error) {
        console.error('Failed to load world map:', error);
        showNotification('Failed to load world map. Please check the console for details.', 'error');
    });

    // ===== UI Controls =====
    const overlayLegend = document.getElementById('overlayLegend');

    document.getElementById('toggleOverlay').addEventListener('click', function () {
        worldMap.config.overlaysEnabled = !worldMap.config.overlaysEnabled;
        overlayLegend.style.display = worldMap.config.overlaysEnabled ? 'flex' : 'none';
        worldMap.render();
    });

    document.getElementById('resetViewBtn').addEventListener('click', function () {
        centerOnHomeRegion();
    });

    document.getElementById('zoomInBtn').addEventListener('click', function () {
        worldMap.cameraController.zoomIn();
    });

    document.getElementById('zoomOutBtn').addEventListener('click', function () {
        worldMap.cameraController.zoomOut();
    });

    document.getElementById('profileDropdownBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById('profileDropdown').classList.toggle('show');
    });

    window.addEventListener('click', function () {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });

    // ===== Unit Tooltip =====
    const unitTooltip = document.getElementById('unitTooltip');

    // Count text for a tooltip row, including what is under construction
    function unitCountHtml(unit) {
        const inConstruction = unit.inConstruction || 0;

        if (unit.level === undefined) {
            const constructionText = inConstruction > 0
                ? ' <span class="unit-construction">(+' + inConstruction + ')</span>'
                : '';
            return unit.amount + constructionText;
        }

        if (inConstruction === 0) {
            return 'Level ' + unit.level;
        }
        if (unit.level === 0) {
            return '<span class="unit-construction">Under construction</span>';
        }
        return 'Level ' + unit.level + ' <span class="unit-construction">&rarr; ' + (unit.level + inConstruction) + '</span>';
    }

    let tooltipRAF = null;

    worldMap.canvas.addEventListener('mousemove', function (e) {
        if (tooltipRAF) return;
        const evt = e;
        tooltipRAF = requestAnimationFrame(function () {
            tooltipRAF = null;

            const rect = worldMap.canvas.getBoundingClientRect();
            const mouseX = evt.clientX - rect.left;
            const mouseY = evt.clientY - rect.top;

            // O(1) tile lookup instead of iterating all sectors
            const hoveredRegion = worldMap.getTileAtScreenPos(mouseX, mouseY);
            let foundIcon = null;

            if (hoveredRegion && hoveredRegion.iconPositions) {
                const worldPos = worldMap.cameraController.screenToWorld(mouseX, mouseY);
                for (let j = 0; j < hoveredRegion.iconPositions.length; j++) {
                    const icon = hoveredRegion.iconPositions[j];
                    const dx = worldPos.x - icon.x;
                    const dy = worldPos.y - icon.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance <= icon.size) {
                        foundIcon = { icon: icon, region: hoveredRegion };
                        break;
                    }
                }
            }

            if (foundIcon) {
                const fi = foundIcon.icon;
                const region = foundIcon.region;
                const isMasked = region.units && region.units.masked;

                let tooltipHtml = '<div class="tooltip-title" style="color: ' + fi.color + '">' + fi.label + '</div>';

                if (isMasked) {
                    tooltipHtml += '<div class="tooltip-item">' +
                        '<span class="unit-name">Unknown composition</span>' +
                        '<span class="unit-count">?</span></div>';
                    tooltipHtml += '<div class="tooltip-total">Total: ?</div>';
                } else {
                    if (fi.details && fi.details.length > 0) {
                        fi.details.forEach(function (unit) {
                            tooltipHtml += '<div class="tooltip-item">' +
                                '<span class="unit-name">' + unit.name + '</span>' +
                                '<span class="unit-count">' + unitCountHtml(unit) + '</span></div>';
                        });
                    }
                    const totalConstruction = fi.inConstruction > 0
                        ? ' <span class="unit-construction">(+' + fi.inConstruction + ')</span>'
                        : '';
                    tooltipHtml += '<div class="tooltip-total">Total: ' + fi.count + totalConstruction + '</div>';
                    if (fi.hasConstruction) {
                        tooltipHtml += '<div class="tooltip-note">Orange: under construction</div>';
                    }
                }

                unitTooltip.innerHTML = tooltipHtml;
                unitTooltip.style.display = 'block';
                unitTooltip.style.left = (evt.clientX + 15) + 'px';
                unitTooltip.style.top = (evt.clientY + 10) + 'px';
            } else {
                unitTooltip.style.display = 'none';
            }
        });
    });

    worldMap.canvas.addEventListener('mouseleave', function () {
        unitTooltip.style.display = 'none';
    });
})();
