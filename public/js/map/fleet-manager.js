/**
 * FleetManager - Handles fleet rendering, animation, and ETA countdown
 */
class FleetManager {
    constructor(mapInstance) {
        this.map = mapInstance;
        this.fleets = [];
        // Enemy fleets detected by radar: only route and arrival time are known
        this.detectedFleets = [];
        this.fleetUIMap = new Map();

        // Animation state
        this.animationOffset = 0;
        this.lastAnimationTime = Date.now();
        this.etaUpdateInterval = null;

        // Callbacks for UI events
        this.onFleetAction = null;
    }

    /**
     * Set fleet data and start countdown if needed
     */
    setFleets(fleets) {
        this.fleets = fleets || [];
        this.startETACountdown();
    }

    /**
     * Set enemy fleets detected by radar
     */
    setDetectedFleets(fleets) {
        this.detectedFleets = fleets || [];
        this.startETACountdown();
    }

    /**
     * Start countdown timer for ETA updates
     */
    startETACountdown() {
        this.stopETACountdown();

        if ([...this.fleets, ...this.detectedFleets].some(f => !f.hasArrived)) {
            this.etaUpdateInterval = setInterval(() => {
                // Update animation offset for fleet line
                const currentTime = Date.now();
                const deltaTime = currentTime - this.lastAnimationTime;
                this.animationOffset = (this.animationOffset + deltaTime * 0.05) % 28;
                this.lastAnimationTime = currentTime;

                // Update fleet ETAs
                this.updateFleetETAs();

                // Request map re-render
                this.map.render();
            }, 1000);
        }
    }

    /**
     * Stop ETA countdown timer
     */
    stopETACountdown() {
        if (this.etaUpdateInterval) {
            clearInterval(this.etaUpdateInterval);
            this.etaUpdateInterval = null;
        }
    }

    /**
     * Update ETA for all in-transit fleets
     */
    updateFleetETAs() {
        const now = Math.floor(Date.now() / 1000);
        let hasInTransitFleets = false;

        [...this.fleets, ...this.detectedFleets].forEach(fleet => {
            if (!fleet.hasArrived) {
                fleet.eta = Math.max(0, fleet.timestampArrive - now);

                if (fleet.eta <= 0) {
                    fleet.hasArrived = true;
                    console.log(`Fleet ${fleet.id} has arrived!`);
                } else {
                    hasInTransitFleets = true;
                }
            }
        });

        if (!hasInTransitFleets) {
            this.stopETACountdown();
        }
    }

    /**
     * Render fleet lines on the canvas
     */
    renderFleetLines(ctx, coordToPixel) {
        ctx.save();

        this.fleets.forEach(fleet => {
            const points = this.getRoutePoints(fleet, coordToPixel);

            // Draw the route with an arrow for direction
            ctx.beginPath();

            if (fleet.hasArrived) {
                ctx.setLineDash([8, 6]);
                ctx.strokeStyle = '#ff6b6b';
            } else {
                ctx.setLineDash([12, 8]);
                ctx.lineDashOffset = -this.animationOffset;
                ctx.strokeStyle = '#ffcc00';
            }

            ctx.lineWidth = 3;
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.lineDashOffset = 0;

            // Draw arrow for in-transit fleets
            if (!fleet.hasArrived) {
                this.drawArrow(ctx, points[points.length - 2], points[points.length - 1]);
            }

            // Draw fleet marker
            this.drawFleetMarker(ctx, fleet, points);
        });

        this.renderDetectedFleetLines(ctx, coordToPixel);

        ctx.restore();
    }

    /**
     * Render enemy fleets detected by radar as red dotted lines from source to target
     */
    renderDetectedFleetLines(ctx, coordToPixel) {
        const color = '#ff3b3b';

        this.detectedFleets.forEach(fleet => {
            const points = this.getRoutePoints(fleet, coordToPixel);

            ctx.beginPath();
            ctx.setLineDash([2, 8]);
            ctx.lineDashOffset = fleet.hasArrived ? 0 : -this.animationOffset;
            ctx.lineCap = 'round';
            ctx.strokeStyle = color;
            ctx.lineWidth = 4;
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.lineDashOffset = 0;
            ctx.lineCap = 'butt';

            if (!fleet.hasArrived) {
                this.drawArrow(ctx, points[points.length - 2], points[points.length - 1], color);
            }

            const position = fleet.hasArrived
                ? points[points.length - 1]
                : this.getPointAlongRoute(points, this.getProgress(fleet));
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.arc(position.x, position.y - 15, 6, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 1.5;
            ctx.stroke();
        });
    }

    /**
     * Fraction (0-1) of the route an in-transit fleet has travelled
     */
    getProgress(fleet) {
        const now = Date.now() / 1000;
        const totalTime = fleet.timestampArrive - fleet.timestamp;

        return totalTime > 0 ? Math.min(1, Math.max(0, (now - fleet.timestamp) / totalTime)) : 1;
    }

    /**
     * Pixel positions of the regions the fleet travels through; fleets without a stored route travel straight
     */
    getRoutePoints(fleet, coordToPixel) {
        const path = fleet.path && fleet.path.length >= 2
            ? fleet.path
            : [[fleet.sourceX, fleet.sourceY], [fleet.targetX, fleet.targetY]];

        return path.map(coordinate => coordToPixel(coordinate[0], coordinate[1]));
    }

    /**
     * Position at the given fraction (0-1) of the route's total length
     */
    getPointAlongRoute(points, progress) {
        const segmentLengths = [];
        let totalLength = 0;
        for (let i = 1; i < points.length; i++) {
            const length = Math.hypot(points[i].x - points[i - 1].x, points[i].y - points[i - 1].y);
            segmentLengths.push(length);
            totalLength += length;
        }

        let remaining = totalLength * progress;
        for (let i = 0; i < segmentLengths.length; i++) {
            if (remaining <= segmentLengths[i] || i === segmentLengths.length - 1) {
                const fraction = segmentLengths[i] > 0 ? Math.min(1, remaining / segmentLengths[i]) : 1;
                return {
                    x: points[i].x + (points[i + 1].x - points[i].x) * fraction,
                    y: points[i].y + (points[i + 1].y - points[i].y) * fraction,
                };
            }
            remaining -= segmentLengths[i];
        }

        return points[points.length - 1];
    }

    /**
     * Draw direction arrow
     */
    drawArrow(ctx, sourceIso, targetIso, color = '#ffcc00') {
        const angle = Math.atan2(targetIso.y - sourceIso.y, targetIso.x - sourceIso.x);
        const arrowSize = 15;
        const arrowX = targetIso.x - Math.cos(angle) * 20;
        const arrowY = targetIso.y - Math.sin(angle) * 20;

        ctx.save();
        ctx.translate(arrowX, arrowY);
        ctx.rotate(angle);

        ctx.fillStyle = color;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 1.5;

        ctx.beginPath();
        ctx.moveTo(arrowSize, 0);
        ctx.lineTo(-arrowSize / 2, -arrowSize / 2);
        ctx.lineTo(-arrowSize / 2, arrowSize / 2);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.restore();
    }

    /**
     * Draw fleet marker at current position
     */
    drawFleetMarker(ctx, fleet, points) {
        let fleetIconX, fleetIconY;

        if (fleet.hasArrived) {
            fleetIconX = points[points.length - 1].x;
            fleetIconY = points[points.length - 1].y;
        } else {
            // Interpolate along the route between departure and arrival, so the marker moves at the fleet's
            // (slowest unit) speed
            const position = this.getPointAlongRoute(points, this.getProgress(fleet));
            fleetIconX = position.x;
            fleetIconY = position.y;
        }

        ctx.beginPath();
        if (fleet.hasArrived) {
            ctx.fillStyle = '#ff4444';
            ctx.arc(fleetIconX, fleetIconY - 20, 8, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
        } else {
            ctx.fillStyle = '#ffcc00';
            ctx.arc(fleetIconX, fleetIconY - 15, 6, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 1;
            ctx.stroke();
        }
    }

    /**
     * Render fleet UI elements (attack buttons, ETA labels) as HTML overlays
     * Uses DOM pooling to update in place instead of destroy/recreate each frame
     */
    renderFleetUIElements(canvas, camera, coordToPixel) {
        const container = canvas.parentElement;
        const canvasRect = canvas.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        const offsetX = canvasRect.left - containerRect.left;
        const offsetY = canvasRect.top - containerRect.top;

        const activeFleetIds = new Set();

        this.fleets.forEach(fleet => {
            const targetIso = coordToPixel(fleet.targetX, fleet.targetY);

            const screenX = (targetIso.x * camera.zoom) + camera.x + canvas.width / 2;
            const screenY = (targetIso.y * camera.zoom) + camera.y + canvas.height / 2;

            if (screenX < 0 || screenX > canvas.width || screenY < 0 || screenY > canvas.height) {
                // Off-screen: hide existing element if any
                const offEl = this.fleetUIMap.get(fleet.id);
                if (offEl) {
                    offEl.style.display = 'none';
                }
                activeFleetIds.add(fleet.id);
                return;
            }

            const posX = screenX + offsetX;
            const posY = screenY + offsetY;
            activeFleetIds.add(fleet.id);

            const existing = this.fleetUIMap.get(fleet.id);

            if (fleet.hasArrived) {
                if (existing && existing.dataset.fleetState === 'arrived') {
                    // Update position only
                    existing.style.display = '';
                    existing.style.left = posX + 'px';
                    existing.style.top = posY + 'px';
                } else {
                    // State changed or new — create widget
                    if (existing) existing.remove();
                    const widget = this.createArrivedFleetWidget(container, fleet, posX, posY);
                    widget.dataset.fleetState = 'arrived';
                    this.fleetUIMap.set(fleet.id, widget);
                }
            } else {
                if (existing && existing.dataset.fleetState === 'transit') {
                    // Update position and ETA text only
                    existing.style.display = '';
                    existing.style.left = posX + 'px';
                    existing.style.top = posY + 'px';
                    existing.textContent = '\uD83D\uDD50 ETA: ' + this.formatETA(fleet.eta);
                } else {
                    // New in-transit fleet
                    if (existing) existing.remove();
                    const label = this.createETALabel(container, fleet, posX, posY);
                    label.dataset.fleetState = 'transit';
                    this.fleetUIMap.set(fleet.id, label);
                }
            }
        });

        this.detectedFleets.forEach(fleet => {
            const key = 'enemy-' + fleet.id;
            const targetIso = coordToPixel(fleet.targetX, fleet.targetY);
            const screenX = (targetIso.x * camera.zoom) + camera.x + canvas.width / 2;
            const screenY = (targetIso.y * camera.zoom) + camera.y + canvas.height / 2;
            activeFleetIds.add(key);

            let label = this.fleetUIMap.get(key);
            if (screenX < 0 || screenX > canvas.width || screenY < 0 || screenY > canvas.height) {
                if (label) {
                    label.style.display = 'none';
                }
                return;
            }

            if (!label) {
                label = document.createElement('div');
                label.className = 'fleet-eta-label enemy';
                container.appendChild(label);
                this.fleetUIMap.set(key, label);
            }
            label.style.display = '';
            label.style.left = (screenX + offsetX) + 'px';
            label.style.top = (screenY + offsetY) + 'px';
            label.textContent = fleet.hasArrived
                ? '\u26A0 Enemy arrived'
                : '\u26A0 Enemy ETA: ' + this.formatETA(fleet.eta);
        });

        // Remove elements for fleets that no longer exist
        const self = this;
        this.fleetUIMap.forEach(function (el, fleetId) {
            if (!activeFleetIds.has(fleetId)) {
                el.remove();
                self.fleetUIMap.delete(fleetId);
            }
        });
    }

    /**
     * Create widget for arrived fleet
     */
    createArrivedFleetWidget(container, fleet, posX, posY) {
        const widget = document.createElement('div');
        widget.className = 'fleet-widget';

        // Determine if this is a friendly reinforcement (or ships occupying open sea) or an attack
        const isReinforcement = fleet.targetIsYours === true || fleet.targetIsOpenSea === true;
        const reinforceLabel = fleet.targetIsOpenSea === true ? 'Occupy' : 'Reinforce';
        const actionButton = isReinforcement
            ? `<button class="fleet-widget-btn reinforce" data-fleet-id="${fleet.id}" data-action="reinforce">${reinforceLabel}</button>`
            : `<button class="fleet-widget-btn attack" data-fleet-id="${fleet.id}" data-action="attack">Attack</button>`;

        const title = isReinforcement ? '🛡 Fleet Arrived' : '⚔ Fleet Arrived';

        // Build unit details HTML
        let unitsHtml = '';
        if (fleet.units && fleet.units.length > 0) {
            unitsHtml = '<div class="fleet-widget-units">';
            fleet.units.forEach(unit => {
                unitsHtml += `<div>${unit.amount}x ${unit.name}</div>`;
            });
            unitsHtml += '</div>';
        }

        widget.innerHTML = `
            <div class="fleet-widget-title">${title}</div>
            ${unitsHtml}
            <div class="fleet-widget-buttons">
                ${actionButton}
                <button class="fleet-widget-btn recall" data-fleet-id="${fleet.id}" data-action="recall">Recall</button>
            </div>
        `;
        container.appendChild(widget);

        widget.style.left = posX + 'px';
        widget.style.top = posY + 'px';

        // Attach event listeners based on button type
        if (isReinforcement) {
            widget.querySelector('.fleet-widget-btn.reinforce').addEventListener('click', (e) => {
                e.stopPropagation();
                const button = e.currentTarget;
                if (button.disabled) return;
                button.disabled = true;
                this.handleReinforce(fleet, widget);
            });
        } else {
            widget.querySelector('.fleet-widget-btn.attack').addEventListener('click', (e) => {
                e.stopPropagation();
                const button = e.currentTarget;
                if (button.disabled) return;
                button.disabled = true;
                this.handleAttack(fleet, widget);
            });
        }

        // Recall button - async call
        widget.querySelector('.fleet-widget-btn.recall').addEventListener('click', (e) => {
            e.stopPropagation();
            const button = e.currentTarget;
            if (button.disabled) return;
            button.disabled = true;
            this.handleRecall(fleet, widget);
        });

        return widget;
    }

    /**
     * Handle recall action via API
     */
    async handleRecall(fleet, widget) {
        const buttonsDiv = widget.querySelector('.fleet-widget-buttons');

        // Disable buttons and show loading
        buttonsDiv.innerHTML = '<div class="text-yellow">🔄 Recalling...</div>';

        try {
            const response = await fetch(`/game/api/fleet/recall/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                this.applyFleetLanded(fleet.id, result);
                this.showNotification(result.message, 'success');
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div class="text-enemy">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Recall failed:', error);
            buttonsDiv.innerHTML = '<div class="text-enemy">Network error</div>';
            setTimeout(() => {
                this.map.render();
            }, 3000);
        }
    }

    /**
     * Handle reinforce action via API
     */
    async handleReinforce(fleet, widget) {
        const buttonsDiv = widget.querySelector('.fleet-widget-buttons');

        // Disable buttons and show loading
        buttonsDiv.innerHTML = '<div class="text-positive">🛡 Reinforcing...</div>';

        try {
            const response = await fetch(`/game/api/fleet/reinforce/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                this.applyFleetLanded(fleet.id, result);
                this.showNotification(result.message, 'success');
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div class="text-enemy">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Reinforce failed:', error);
            buttonsDiv.innerHTML = '<div class="text-enemy">Network error</div>';
            setTimeout(() => {
                this.map.render();
            }, 3000);
        }
    }

    /**
     * Update the map after a recall or reinforce. Ships landing on open sea make that water region yours.
     */
    applyFleetLanded(fleetId, result) {
        this.removeFleet(fleetId);
        if (window.WorldBuild) {
            WorldBuild.invalidateCache(result.regionId);
        }

        const region = this.map.sectors.find(r => r.id === result.regionId);
        if (region && !region.isYours && window.WorldModals) {
            this.updateFleetTargets(result.regionId, true);
            WorldModals.applyRegionOwnership(result.regionId, result.units, result.newlyVisibleEnemyUnits);
            return;
        }

        this.updateRegionUnits(result.regionId, result.units);
        this.map.render();
    }

    /**
     * A region changed owner: fleets heading there reinforce it when it became yours, or otherwise sail to
     * open sea (a released water region). Their widgets are rebuilt on the next render.
     */
    updateFleetTargets(regionId, isYours) {
        this.fleets.forEach(f => {
            if (f.targetRegionId === regionId) {
                f.targetIsYours = isYours;
                f.targetIsOpenSea = !isYours;
            }
        });

        this.fleetUIMap.forEach((el, id) => {
            const fleet = this.fleets.find(f => f.id === id);
            if (fleet && fleet.targetRegionId === regionId) {
                el.remove();
                this.fleetUIMap.delete(id);
            }
        });
    }

    /**
     * Remove a fleet from the list
     */
    removeFleet(fleetId) {
        this.fleets = this.fleets.filter(f => f.id !== fleetId);
    }

    /**
     * Update region unit data in the sectors array
     */
    updateRegionUnits(regionId, units) {
        if (!regionId || !units) return;

        const region = this.map.sectors.find(r => r.id === regionId);
        if (region) {
            region.units = units;
        }
    }

    /**
     * Handle attack action via API
     */
    async handleAttack(fleet, widget) {
        const buttonsDiv = widget.querySelector('.fleet-widget-buttons');

        // Disable buttons and show loading
        buttonsDiv.innerHTML = '<div class="text-yellow">⚔ Attacking...</div>';

        try {
            const response = await fetch(`/game/api/fleet/attack/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Update the fleet and the target region on the map (ownership and units)
                this.applyBattleResult(fleet.id, result);

                // Show battle report in modal
                this.showBattleReportModal(result);
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div class="text-enemy">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Attack failed:', error);
            buttonsDiv.innerHTML = '<div class="text-enemy">Network error</div>';
            setTimeout(() => {
                this.map.render();
            }, 3000);
        }
    }

    /**
     * Update the map after a battle. A won battle makes the region yours with your surviving units,
     * a lost battle only changes the (masked) defending units and keeps the survivors in the fleet.
     */
    applyBattleResult(fleetId, result) {
        if (result.fleetUnits.length > 0) {
            const fleet = this.fleets.find(f => f.id === fleetId);
            if (fleet) {
                fleet.units = result.fleetUnits;
                fleet.unitCount = result.fleetUnits.reduce((sum, unit) => sum + unit.amount, 0);
            }
        } else {
            this.removeFleet(fleetId);
        }

        if (result.hasWon) {
            // Aircraft fly back to the source region after a won battle
            if (result.sourceRegionUnits) {
                this.updateRegionUnits(result.sourceRegionId, result.sourceRegionUnits);
                if (window.WorldBuild) {
                    WorldBuild.invalidateCache(result.sourceRegionId);
                }
            }

            // Other fleets heading to the conquered region can now reinforce it
            this.fleets.forEach(f => {
                if (f.targetRegionId === result.regionId) {
                    f.targetIsYours = true;
                    f.targetIsOpenSea = false;
                }
            });
        }

        // Widgets of affected fleets are rebuilt on the next render with the new data
        this.fleetUIMap.forEach((el, id) => {
            const fleet = this.fleets.find(f => f.id === id);
            if (id === fleetId || (fleet && fleet.targetRegionId === result.regionId)) {
                el.remove();
                this.fleetUIMap.delete(id);
            }
        });

        if (window.WorldBuild) {
            WorldBuild.invalidateCache(result.regionId);
        }

        if (result.hasWon && window.WorldModals) {
            WorldModals.applyRegionOwnership(result.regionId, result.regionUnits, result.newlyVisibleEnemyUnits);
        } else if (result.regionHasOwner === false && window.WorldModals) {
            // All defenders of a water region died, so the region is open sea now
            WorldModals.applyRegionRelease(result.regionId);
        } else {
            this.updateRegionUnits(result.regionId, result.regionUnits);
            this.map.render();
        }
    }

    /**
     * Render the units and buildings each side lost in a battle; also used by the battle summary in reports
     */
    static renderBattleLosses(lostUnits, attackerWon) {
        if (!lostUnits) {
            return '';
        }

        const escape = (text) => {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(String(text)));
            return div.innerHTML;
        };
        const renderList = (units, buildings) => {
            let html = '';
            units.forEach(unit => {
                html += `<li>${escape(unit.amount)} ${escape(unit.name)}</li>`;
            });
            buildings.forEach(building => {
                html += `<li>${escape(building.name)} (level ${escape(building.level)}) destroyed</li>`;
            });

            return html === '' ? '<li class="text-muted">No losses</li>' : html;
        };

        return `
            <div class="battle-losses">
                <h4>Losses${attackerWon ? ' &mdash; region captured' : ''}</h4>
                <div class="battle-losses-columns">
                    <div>
                        <h5>Attacker</h5>
                        <ul>${renderList(lostUnits.attacker, [])}</ul>
                    </div>
                    <div>
                        <h5>Defender</h5>
                        <ul>${renderList(lostUnits.defender, lostUnits.defenderBuildings)}</ul>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show battle report in a modal
     */
    showBattleReportModal(result) {
        const modal = document.getElementById('battleReportModal');
        if (!modal.dataset.bound) {
            modal.addEventListener('hidden.bs.modal', () => this.map.render());
            modal.dataset.bound = 'true';
        }

        // Build battle report content
        const resultClass = result.hasWon ? 'text-positive' : 'text-negative';
        const resultIcon = result.hasWon ? '🏆' : '💀';

        let battleLogHtml = '';
        if (result.battleLog && result.battleLog.length > 0) {
            battleLogHtml = '<div class="battle-log">';
            result.battleLog.forEach(line => {
                battleLogHtml += `<p>${line}</p>`;
            });
            battleLogHtml += '</div>';
        }

        const body = modal.querySelector('#battleReportBody');
        body.innerHTML = `
            <div class="battle-result">
                <span class="battle-result-icon">${resultIcon}</span>
                <h3 class="battle-result-title ${resultClass}">${result.hasWon ? 'VICTORY!' : 'DEFEAT'}</h3>
                <p>${result.message}</p>
            </div>
            ${FleetManager.renderBattleLosses(result.lostUnits, result.hasWon)}
            ${battleLogHtml}
        `;

        bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    /**
     * Show a notification message
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fleet-notification fleet-notification-${type}`;
        notification.innerHTML = message;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('is-leaving');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Create ETA label for in-transit fleet
     */
    createETALabel(container, fleet, posX, posY) {
        const label = document.createElement('div');
        label.className = 'fleet-eta-label';
        label.setAttribute('data-fleet-id', fleet.id);
        label.innerHTML = '🕐 ETA: ' + this.formatETA(fleet.eta);
        container.appendChild(label);

        label.style.left = posX + 'px';
        label.style.top = posY + 'px';

        return label;
    }

    /**
     * Format seconds into human readable ETA
     */
    formatETA(seconds) {
        if (seconds <= 0) return 'Arrived';

        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    }

    /**
     * Check if there are fleets
     */
    hasFleets() {
        return this.fleets && this.fleets.length > 0;
    }

    /**
     * Clean up resources
     */
    destroy() {
        this.stopETACountdown();
        this.fleetUIMap.forEach(function (el) { el.remove(); });
        this.fleetUIMap.clear();
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FleetManager;
}
