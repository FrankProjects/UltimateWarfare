/**
 * Region modal handlers (buy, enemy, yours) and UI controls.
 * Depends on: notifications.js, WorldApp config, WorldBuild, WorldAttack
 */
(function () {
    const imgBase = WorldApp.imageBasePath;

    /**
     * Recalculate visibility on all sectors (owned regions + 6 hex neighbors).
     * Mirrors the PHP calculateVisibleRegions logic using odd-r offset hex grid.
     */
    function recalculateVisibility(sectors) {
        const visibleCoords = {};

        sectors.forEach(function (s) {
            if (!s.isYours) return;
            const x = s.x;
            const y = s.y;
            const key = x + ',' + y;
            visibleCoords[key] = true;

            visibleCoords[(x - 1) + ',' + y] = true;
            visibleCoords[(x + 1) + ',' + y] = true;

            if (y % 2 === 0) {
                visibleCoords[(x - 1) + ',' + (y - 1)] = true;
                visibleCoords[x + ',' + (y - 1)] = true;
                visibleCoords[(x - 1) + ',' + (y + 1)] = true;
                visibleCoords[x + ',' + (y + 1)] = true;
            } else {
                visibleCoords[x + ',' + (y - 1)] = true;
                visibleCoords[(x + 1) + ',' + (y - 1)] = true;
                visibleCoords[x + ',' + (y + 1)] = true;
                visibleCoords[(x + 1) + ',' + (y + 1)] = true;
            }
        });

        sectors.forEach(function (s) {
            s.isVisible = !!visibleCoords[s.x + ',' + s.y];
        });
    }

    // ===== Buy Region Modal =====
    const modal = document.getElementById('buyRegionModal');
    const confirmBuyBtn = document.getElementById('confirmBuyBtn');
    let selectedRegion = null;
    let isBuyingRegion = false;
    let currentRegionPrice = WorldApp.regionPrice;

    function showBuyRegionModal(region) {
        selectedRegion = region;
        const modalBody = document.getElementById('modalBody');
        const imageUrl = imgBase + '/map/' + region.image;

        modalBody.innerHTML =
            '<div class="region-info"><div class="region-image"><img src="' + imageUrl + '" alt="' + region.type + '"></div>' +
            '<div class="region-details">' +
            '<p><strong>Coordinates:</strong> ' + region.x + ', ' + region.y + '</p>' +
            '<p><strong>Type:</strong> ' + region.type + '</p>' +
            '<p style="margin-top: 15px;"><strong>Price:</strong> $' + currentRegionPrice.toLocaleString('en-US') + '</p>' +
            '<p style="margin-top: 10px;">Do you want to buy this region?</p>' +
            '<p style="color: #ffa500; font-size: 11px; margin-top: 5px;">Note: The price increases with each region you own.</p>' +
            '</div></div>';
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    modal.addEventListener('hidden.bs.modal', function () { selectedRegion = null; });

    confirmBuyBtn.onclick = async function () {
        if (!selectedRegion || isBuyingRegion) return;
        isBuyingRegion = true;
        confirmBuyBtn.disabled = true;
        confirmBuyBtn.textContent = 'Buying...';

        try {
            const response = await fetch('/game/api/world/region/buy/' + selectedRegion.id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const worldMap = WorldApp.worldMap;
                const regionIndex = worldMap.sectors.findIndex(function (r) { return r.id === selectedRegion.id; });
                if (regionIndex !== -1) {
                    worldMap.sectors[regionIndex].hasOwner = true;
                    worldMap.sectors[regionIndex].isYours = true;
                    worldMap.sectors[regionIndex].ownerName = WorldApp.playerName;
                }

                // Recalculate fog of war with new ownership
                recalculateVisibility(WorldApp.worldRegions);

                // Apply masked enemy unit data for newly visible neighbors
                if (result.newlyVisibleEnemyUnits) {
                    Object.keys(result.newlyVisibleEnemyUnits).forEach(function (id) {
                        const sector = WorldApp.worldRegions.find(function (s) { return s.id === parseInt(id); });
                        if (sector && !sector.isYours) {
                            sector.units = result.newlyVisibleEnemyUnits[id];
                        }
                    });
                }

                if (result.newCash !== undefined) {
                    const cashElements = document.querySelectorAll('.resource-amount');
                    if (cashElements.length > 0) cashElements[0].textContent = result.newCash.toLocaleString('en-US');
                }

                if (result.newRegionPrice !== undefined) {
                    currentRegionPrice = result.newRegionPrice;
                }

                worldMap.setSectors(WorldApp.worldRegions).then(function () { worldMap.render(); });
                showNotification(result.message, 'success');
                bootstrap.Modal.getInstance(modal).hide();
            } else {
                showNotification(result.message || 'Failed to buy region', 'error');
            }
        } catch (error) {
            console.error('Error buying region:', error);
            showNotification('An error occurred while buying the region. Please try again.', 'error');
        } finally {
            isBuyingRegion = false;
            confirmBuyBtn.disabled = false;
            confirmBuyBtn.textContent = 'Buy Region';
        }
    };

    // ===== Enemy Region Modal =====
    const enemyModal = document.getElementById('enemyRegionModal');
    let selectedEnemyRegion = null;

    function showEnemyRegionModal(region) {
        selectedEnemyRegion = region;
        const enemyModalBody = document.getElementById('enemyModalBody');
        const imageUrl = imgBase + '/map/' + region.image;

        enemyModalBody.innerHTML =
            '<div class="region-info"><div class="region-image"><img src="' + imageUrl + '" alt="' + region.type + '"></div>' +
            '<div class="region-details">' +
            '<p><strong>Coordinates:</strong> ' + region.x + ', ' + region.y + '</p>' +
            '<p><strong>Type:</strong> ' + region.type + '</p>' +
            '<p><strong>Owner:</strong> <a href="#" id="enemyOwnerLink" style="color: #ff6b6b; text-decoration: underline; cursor: pointer;">' + region.ownerName + '</a></p>' +
            '</div></div>';
        bootstrap.Modal.getOrCreateInstance(enemyModal).show();

        document.getElementById('enemyOwnerLink').onclick = function (e) {
            e.preventDefault();
            bootstrap.Modal.getInstance(enemyModal).hide();
            enemyModal.addEventListener('hidden.bs.modal', function handler() {
                enemyModal.removeEventListener('hidden.bs.modal', handler);
                WorldPlayerProfile.show(region.ownerName);
            });
        };
    }

    enemyModal.addEventListener('hidden.bs.modal', function () { selectedEnemyRegion = null; });

    // ===== Send Message Modal =====
    const sendMessageModal = document.getElementById('sendMessageModal');
    const messageSubject = document.getElementById('messageSubject');
    const messageBody = document.getElementById('messageBody');
    const confirmSendMessageBtn = document.getElementById('confirmSendMessageBtn');
    let isSendingMessage = false;

    let sendMessageToPlayer = null;

    function showSendMessageModal(playerName, subject) {
        sendMessageToPlayer = playerName;
        document.getElementById('sendMessageRecipient').textContent = playerName;
        messageSubject.value = subject || '';
        messageBody.value = '';
        confirmSendMessageBtn.disabled = false;
        confirmSendMessageBtn.textContent = 'Send Message';
        bootstrap.Modal.getOrCreateInstance(sendMessageModal).show();
    }

    // Allow messages.js to trigger reply
    if (window.WorldMessages) {
        window.WorldMessages.onReply = function (playerName, subject) {
            showSendMessageModal(playerName, subject);
        };
    }

    confirmSendMessageBtn.onclick = async function () {
        if (isSendingMessage) return;

        const subject = messageSubject.value.trim();
        const message = messageBody.value.trim();

        if (subject === '') {
            showNotification('Please type a subject', 'error');
            return;
        }
        if (message === '') {
            showNotification('Please type a message', 'error');
            return;
        }

        isSendingMessage = true;
        confirmSendMessageBtn.disabled = true;
        confirmSendMessageBtn.textContent = 'Sending...';

        try {
            const response = await fetch('/game/api/message/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    toPlayerName: sendMessageToPlayer,
                    subject: subject,
                    message: message
                })
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                bootstrap.Modal.getInstance(sendMessageModal).hide();
                const enemyInstance = bootstrap.Modal.getInstance(enemyModal);
                if (enemyInstance) enemyInstance.hide();
            } else {
                showNotification(result.message || 'Failed to send message', 'error');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showNotification('An error occurred while sending the message. Please try again.', 'error');
        } finally {
            isSendingMessage = false;
            confirmSendMessageBtn.disabled = false;
            confirmSendMessageBtn.textContent = 'Send Message';
        }
    };

    document.getElementById('sendMessageBtn').onclick = function () {
        if (selectedEnemyRegion) showSendMessageModal(selectedEnemyRegion.ownerName);
    };
    document.getElementById('attackBtn').onclick = function () {
        if (selectedEnemyRegion) WorldAttack.startAttackFromSelection(selectedEnemyRegion);
    };
    document.getElementById('operationBtn').onclick = function () {
        if (selectedEnemyRegion) WorldOperations.startOperation(selectedEnemyRegion);
    };

    // ===== Your Region Modal =====
    const yourModal = document.getElementById('yourRegionModal');
    let selectedYourRegion = null;

    function showYourRegionModal(region) {
        selectedYourRegion = region;
        const yourModalBody = document.getElementById('yourModalBody');
        const imageUrl = imgBase + '/map/' + region.image;

        yourModalBody.innerHTML =
            '<div class="region-info"><div class="region-image"><img src="' + imageUrl + '" alt="' + region.type + '"></div>' +
            '<div class="region-details">' +
            '<p><strong>Coordinates:</strong> ' + region.x + ', ' + region.y + '</p>' +
            '<p><strong>Type:</strong> ' + region.type + '</p>' +
            '<p><strong>Owner:</strong> <span style="color: #6bafff;">You</span></p>' +
            '</div></div>';
        bootstrap.Modal.getOrCreateInstance(yourModal).show();
    }

    yourModal.addEventListener('hidden.bs.modal', function () { selectedYourRegion = null; });

    let pendingBuildRegion = null;
    document.getElementById('buildBtn').onclick = function () {
        if (selectedYourRegion) {
            pendingBuildRegion = selectedYourRegion;
            bootstrap.Modal.getInstance(yourModal).hide();
            yourModal.addEventListener('hidden.bs.modal', function handler() {
                yourModal.removeEventListener('hidden.bs.modal', handler);
                if (pendingBuildRegion) {
                    WorldBuild.showBuildModal(pendingBuildRegion);
                    pendingBuildRegion = null;
                }
            });
        }
    };
    document.getElementById('sendUnitsBtn').onclick = function () {
        if (selectedYourRegion) {
            const region = selectedYourRegion;
            bootstrap.Modal.getInstance(yourModal).hide();
            yourModal.addEventListener('hidden.bs.modal', function handler() {
                yourModal.removeEventListener('hidden.bs.modal', handler);
                WorldSendUnits.startSendUnits(region);
            });
        }
    };

    // ===== Navigation Hamburger Menu =====
    const navMenu = document.getElementById('navMenu');
    document.getElementById('navMenuBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        navMenu.classList.toggle('show');
    });

    function closeNavMenu() { navMenu.classList.remove('show'); }

    document.getElementById('navReportsBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldReports.show(); };
    document.getElementById('navRegionsBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldRegionOverview.show(); };
    document.getElementById('navConstructionBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldConstruction.show(); };
    document.getElementById('navFleetsBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldFleetOverview.show(); };
    document.getElementById('navMarketBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldMarket.show(); };
    document.getElementById('navResearchBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldResearch.show(); };
    document.getElementById('navFederationBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldFederation.show(); };
    document.getElementById('navRankingsBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldRankings.show(); };
    document.getElementById('navStatisticsBtn').onclick = function (e) { e.preventDefault(); closeNavMenu(); WorldStatistics.show(); };

    // ===== Close nav dropdown on outside click =====
    window.addEventListener('click', function (event) {
        if (navMenu.classList.contains('show')) { closeNavMenu(); }
    });

    // ===== Default tile click handler =====
    WorldApp.defaultTileClick = function (region) {
        if (!region.hasOwner) {
            showBuyRegionModal(region);
        } else if (region.isYours) {
            showYourRegionModal(region);
        } else {
            showEnemyRegionModal(region);
        }
    };
})();
