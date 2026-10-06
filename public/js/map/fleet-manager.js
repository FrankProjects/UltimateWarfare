/**
 * FleetManager - Handles fleet rendering, animation, and ETA countdown
 */
class FleetManager {
    constructor(mapInstance) {
        this.map = mapInstance;
        this.fleets = [];
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
     * Start countdown timer for ETA updates
     */
    startETACountdown() {
        this.stopETACountdown();

        if (this.fleets && this.fleets.some(f => !f.hasArrived)) {
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
        if (!this.fleets) return;

        const now = Math.floor(Date.now() / 1000);
        let hasInTransitFleets = false;

        this.fleets.forEach(fleet => {
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
        if (!this.fleets || this.fleets.length === 0) {
            return;
        }

        ctx.save();

        this.fleets.forEach(fleet => {
            const sourceIso = coordToPixel(fleet.sourceX, fleet.sourceY);
            const targetIso = coordToPixel(fleet.targetX, fleet.targetY);

            // Draw line with arrow for direction
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
            ctx.moveTo(sourceIso.x, sourceIso.y);
            ctx.lineTo(targetIso.x, targetIso.y);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.lineDashOffset = 0;

            // Draw arrow for in-transit fleets
            if (!fleet.hasArrived) {
                this.drawArrow(ctx, sourceIso, targetIso);
            }

            // Draw fleet marker
            this.drawFleetMarker(ctx, fleet, sourceIso, targetIso);
        });

        ctx.restore();
    }

    /**
     * Draw direction arrow
     */
    drawArrow(ctx, sourceIso, targetIso) {
        const angle = Math.atan2(targetIso.y - sourceIso.y, targetIso.x - sourceIso.x);
        const arrowSize = 15;
        const arrowX = targetIso.x - Math.cos(angle) * 20;
        const arrowY = targetIso.y - Math.sin(angle) * 20;

        ctx.save();
        ctx.translate(arrowX, arrowY);
        ctx.rotate(angle);

        ctx.fillStyle = '#ffcc00';
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
    drawFleetMarker(ctx, fleet, sourceIso, targetIso) {
        let fleetIconX, fleetIconY;

        if (fleet.hasArrived) {
            fleetIconX = targetIso.x;
            fleetIconY = targetIso.y;
        } else {
            const now = Math.floor(Date.now() / 1000);
            const totalTime = fleet.timestampArrive - (fleet.timestampArrive - fleet.eta);
            const elapsed = totalTime - fleet.eta;
            const progress = Math.min(1, elapsed / totalTime);

            fleetIconX = sourceIso.x + (targetIso.x - sourceIso.x) * progress;
            fleetIconY = sourceIso.y + (targetIso.y - sourceIso.y) * progress;
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
        if (!this.fleets || this.fleets.length === 0) {
            this.fleetUIMap.forEach(function (el) { el.remove(); });
            this.fleetUIMap.clear();
            return;
        }

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

        // Determine if this is a friendly reinforcement or an attack
        const isReinforcement = fleet.targetIsYours === true;
        const actionButton = isReinforcement
            ? `<button class="fleet-widget-btn reinforce" data-fleet-id="${fleet.id}" data-action="reinforce">Reinforce</button>`
            : `<button class="fleet-widget-btn attack" data-fleet-id="${fleet.id}" data-action="attack">Attack</button>`;

        const title = isReinforcement ? '🛡 Fleet Arrived' : '⚔ Fleet Arrived';

        // Build unit details HTML
        let unitsHtml = '';
        if (fleet.units && fleet.units.length > 0) {
            unitsHtml = '<div style="text-align: left; font-size: 9px; margin-bottom: 4px; max-height: 60px; overflow-y: auto;">';
            fleet.units.forEach(unit => {
                unitsHtml += `<div style="padding: 1px 0;">${unit.amount}x ${unit.name}</div>`;
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
        widget.style.transform = 'translate(-50%, -100%) translateY(-30px)';

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
        buttonsDiv.innerHTML = '<div style="color: #ffcc00;">🔄 Recalling...</div>';

        try {
            const response = await fetch(`/game/api/fleet/recall/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Remove fleet from list
                this.removeFleet(fleet.id);

                // Update region unit data; cached build data of this region is now outdated
                this.updateRegionUnits(result.regionId, result.units);
                if (window.WorldBuild) {
                    WorldBuild.invalidateCache(result.regionId);
                }

                // Show success message
                this.showNotification(result.message, 'success');

                // Re-render map
                this.map.render();
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div style="color: #ff6b6b;">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Recall failed:', error);
            buttonsDiv.innerHTML = '<div style="color: #ff6b6b;">Network error</div>';
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
        buttonsDiv.innerHTML = '<div style="color: #4CAF50;">🛡 Reinforcing...</div>';

        try {
            const response = await fetch(`/game/api/fleet/reinforce/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Remove fleet from list
                this.removeFleet(fleet.id);

                // Update region unit data; cached build data of this region is now outdated
                this.updateRegionUnits(result.regionId, result.units);
                if (window.WorldBuild) {
                    WorldBuild.invalidateCache(result.regionId);
                }

                // Show success message
                this.showNotification(result.message, 'success');

                // Re-render map
                this.map.render();
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div style="color: #ff6b6b;">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Reinforce failed:', error);
            buttonsDiv.innerHTML = '<div style="color: #ff6b6b;">Network error</div>';
            setTimeout(() => {
                this.map.render();
            }, 3000);
        }
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
        buttonsDiv.innerHTML = '<div style="color: #ffcc00;">⚔ Attacking...</div>';

        try {
            const response = await fetch(`/game/api/fleet/attack/${fleet.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Remove fleet from list
                this.removeFleet(fleet.id);

                // Show battle report in modal
                this.showBattleReportModal(result);
            } else {
                // Show error in widget
                buttonsDiv.innerHTML = `<div style="color: #ff6b6b;">Error: ${result.message}</div>`;
                setTimeout(() => {
                    this.map.render();
                }, 3000);
            }
        } catch (error) {
            console.error('Attack failed:', error);
            buttonsDiv.innerHTML = '<div style="color: #ff6b6b;">Network error</div>';
            setTimeout(() => {
                this.map.render();
            }, 3000);
        }
    }

    /**
     * Show battle report in a modal
     */
    showBattleReportModal(result) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('battleReportModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'battleReportModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title">Battle Report</span>
                        <span class="close" id="closeBattleModal">&times;</span>
                    </div>
                    <div class="modal-body" id="battleReportBody">
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="closeBattleBtn">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Add close handlers
            modal.querySelector('#closeBattleModal').onclick = () => {
                modal.style.display = 'none';
                this.map.render();
            };
            modal.querySelector('#closeBattleBtn').onclick = () => {
                modal.style.display = 'none';
                this.map.render();
            };
            modal.onclick = (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    this.map.render();
                }
            };
        }

        // Build battle report content
        const resultClass = result.hasWon ? 'color: #4CAF50;' : 'color: #f44336;';
        const resultIcon = result.hasWon ? '🏆' : '💀';

        let battleLogHtml = '';
        if (result.battleLog && result.battleLog.length > 0) {
            battleLogHtml = '<div style="max-height: 300px; overflow-y: auto; background: #1a1a1a; padding: 10px; border-radius: 4px; margin-top: 10px;">';
            result.battleLog.forEach(line => {
                battleLogHtml += `<p style="margin: 5px 0; font-size: 12px;">${line}</p>`;
            });
            battleLogHtml += '</div>';
        }

        const body = modal.querySelector('#battleReportBody');
        body.innerHTML = `
            <div style="text-align: center; margin-bottom: 15px;">
                <span style="font-size: 48px;">${resultIcon}</span>
                <h3 style="${resultClass} margin: 10px 0;">${result.hasWon ? 'VICTORY!' : 'DEFEAT'}</h3>
                <p>${result.message}</p>
            </div>
            ${battleLogHtml}
        `;

        // Show modal
        modal.style.display = 'block';
    }

    /**
     * Show a notification message
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fleet-notification fleet-notification-${type}`;
        notification.innerHTML = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            z-index: 10002;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.3s ease;
            background: ${type === 'success' ? '#4CAF50' : (type === 'error' ? '#f44336' : '#2196F3')};
        `;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
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
        label.style.cssText = `
            position: absolute;
            background: rgba(255, 204, 0, 0.95);
            border: 2px solid #ffcc00;
            border-radius: 6px;
            padding: 4px 8px;
            color: #000;
            font-size: 11px;
            font-weight: bold;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            white-space: nowrap;
            text-align: center;
            pointer-events: none;
        `;
        container.appendChild(label);

        label.style.left = posX + 'px';
        label.style.top = posY + 'px';
        label.style.transform = 'translate(-50%, -100%) translateY(-35px)';

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
