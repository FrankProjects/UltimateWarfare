/**
 * BombardmentManager - Handles bombardment cooldown arc rendering and countdown
 * Renders parabolic arcs from source to target regions with cooldown timers
 */
class BombardmentManager {
    constructor(mapInstance) {
        this.map = mapInstance;
        this.bombardments = [];
        this.animationOffset = 0;
        this.lastAnimationTime = Date.now();
        this.updateInterval = null;
    }

    /**
     * Set bombardment cooldown data and start countdown
     */
    setBombardments(bombardments) {
        this.bombardments = bombardments || [];
        this.startCountdown();
    }

    /**
     * Add a single bombardment cooldown (e.g. after executing an operation)
     */
    addBombardment(bombardment) {
        this.bombardments.push(bombardment);
        this.startCountdown();
    }

    /**
     * Start countdown timer for cooldown updates
     */
    startCountdown() {
        this.stopCountdown();

        if (this.bombardments.length > 0) {
            this.updateInterval = setInterval(() => {
                const currentTime = Date.now();
                const deltaTime = currentTime - this.lastAnimationTime;
                this.animationOffset = (this.animationOffset + deltaTime * 0.04) % 20;
                this.lastAnimationTime = currentTime;

                this.updateCooldowns();
                this.map.render();
            }, 1000);
        }
    }

    /**
     * Stop countdown timer
     */
    stopCountdown() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    /**
     * Update remaining time and remove expired cooldowns
     */
    updateCooldowns() {
        const now = Math.floor(Date.now() / 1000);
        this.bombardments = this.bombardments.filter(function (b) {
            b.remainingSeconds = Math.max(0, b.cooldownUntil - now);
            return b.remainingSeconds > 0;
        });

        if (this.bombardments.length === 0) {
            this.stopCountdown();
        }
    }

    /**
     * Render bombardment arcs on the canvas (called within world-space transform)
     */
    renderBombardmentArcs(ctx, coordToPixel) {
        if (this.bombardments.length === 0) {
            return;
        }

        const self = this;
        ctx.save();

        this.bombardments.forEach(function (bombardment) {
            const source = coordToPixel(bombardment.sourceX, bombardment.sourceY);
            const target = coordToPixel(bombardment.targetX, bombardment.targetY);

            const minutes = Math.floor(bombardment.remainingSeconds / 60);
            const seconds = bombardment.remainingSeconds % 60;
            const timeText = minutes + 'm ' + (seconds < 10 ? '0' : '') + seconds + 's';

            self.renderArc(ctx, source, target, timeText);
        });

        ctx.restore();
    }

    /**
     * Draw a parabolic (quadratic Bezier) arc from source to target
     * with a cooldown label rendered directly on the canvas at the arc peak
     */
    renderArc(ctx, source, target, timeText) {
        const midX = (source.x + target.x) / 2;
        const midY = (source.y + target.y) / 2;

        // Calculate arc height based on distance
        const dx = target.x - source.x;
        const dy = target.y - source.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        const arcHeight = Math.max(40, dist * 0.5);

        const controlX = midX;
        const controlY = midY - arcHeight;

        // Animated dashed arc
        ctx.beginPath();
        ctx.setLineDash([8, 5]);
        ctx.lineDashOffset = -this.animationOffset;
        ctx.strokeStyle = '#ff8c00';
        ctx.lineWidth = 2.5;
        ctx.moveTo(source.x, source.y);
        ctx.quadraticCurveTo(controlX, controlY, target.x, target.y);
        ctx.stroke();
        ctx.setLineDash([]);

        // Arrowhead at target end
        this.renderArrowhead(ctx, controlX, controlY, target.x, target.y);

        // Small circle at source
        ctx.beginPath();
        ctx.arc(source.x, source.y, 4, 0, Math.PI * 2);
        ctx.fillStyle = '#ff8c00';
        ctx.fill();

        // Cooldown label at the arc peak (t=0.5 on quadratic Bezier)
        const peakX = midX;
        const peakY = midY - arcHeight * 0.5;
        const label = 'Recharging: ' + timeText;

        ctx.font = 'bold 11px Arial';
        const textWidth = ctx.measureText(label).width;
        const padding = 5;
        const boxWidth = textWidth + padding * 2;
        const boxHeight = 18;

        // Background box
        ctx.fillStyle = 'rgba(40, 25, 0, 0.85)';
        ctx.strokeStyle = '#ff8c00';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(
            peakX - boxWidth / 2,
            peakY - boxHeight - 4,
            boxWidth,
            boxHeight,
            3
        );
        ctx.fill();
        ctx.stroke();

        // Text
        ctx.fillStyle = '#ff8c00';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(label, peakX, peakY - boxHeight / 2 - 4);
    }

    /**
     * Draw arrowhead pointing toward target
     */
    renderArrowhead(ctx, fromX, fromY, toX, toY) {
        const angle = Math.atan2(toY - fromY, toX - fromX);
        const arrowSize = 8;

        ctx.save();
        ctx.translate(toX, toY);
        ctx.rotate(angle);

        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(-arrowSize, -arrowSize * 0.5);
        ctx.lineTo(-arrowSize, arrowSize * 0.5);
        ctx.closePath();
        ctx.fillStyle = '#ff8c00';
        ctx.fill();

        ctx.restore();
    }

    /**
     * Cleanup
     */
    destroy() {
        this.stopCountdown();
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = BombardmentManager;
}
