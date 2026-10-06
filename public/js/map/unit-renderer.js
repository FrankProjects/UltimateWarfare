/**
 * UnitRenderer - Handles rendering of unit indicators on tiles
 */
class UnitRenderer {
    constructor(ctx) {
        this.ctx = ctx;
    }

    /**
     * Draw a simple icon representing a unit type
     */
    drawUnitIcon(type, x, y, size, color) {
        this.ctx.save();
        this.ctx.fillStyle = color;
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 1;

        switch (type) {
            case 'buildings':
                this.drawBuildingIcon(x, y, size);
                break;
            case 'defences':
                this.drawShieldIcon(x, y, size);
                break;
            case 'special':
                this.drawStarIcon(x, y, size);
                break;
            case 'specialUnits':
                this.drawDiamondIcon(x, y, size);
                break;
            case 'troops':
                this.drawTankIcon(x, y, size);
                break;
            case 'airUnits':
                this.drawPlaneIcon(x, y, size);
                break;
            case 'navalUnits':
                this.drawShipIcon(x, y, size);
                break;
            case 'missiles':
                this.drawMissileIcon(x, y, size);
                break;
        }

        this.ctx.restore();
    }

    drawBuildingIcon(x, y, size) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, y - size * 0.6);
        this.ctx.lineTo(x + size * 0.5, y - size * 0.1);
        this.ctx.lineTo(x + size * 0.5, y + size * 0.5);
        this.ctx.lineTo(x - size * 0.5, y + size * 0.5);
        this.ctx.lineTo(x - size * 0.5, y - size * 0.1);
        this.ctx.closePath();
        this.ctx.fill();
        this.ctx.stroke();
    }

    drawShieldIcon(x, y, size) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, y - size * 0.5);
        this.ctx.lineTo(x + size * 0.45, y - size * 0.3);
        this.ctx.lineTo(x + size * 0.45, y + size * 0.1);
        this.ctx.quadraticCurveTo(x, y + size * 0.6, x, y + size * 0.6);
        this.ctx.quadraticCurveTo(x, y + size * 0.6, x - size * 0.45, y + size * 0.1);
        this.ctx.lineTo(x - size * 0.45, y - size * 0.3);
        this.ctx.closePath();
        this.ctx.fill();
        this.ctx.stroke();
    }

    drawTankIcon(x, y, size) {
        const ctx = this.ctx;
        const fillStyle = ctx.fillStyle;
        
        // Tank body
        ctx.beginPath();
        ctx.roundRect(x - size * 0.45, y - size * 0.1, size * 0.9, size * 0.45, 3);
        ctx.fill();
        ctx.stroke();
        
        // Tank turret
        ctx.beginPath();
        ctx.roundRect(x - size * 0.25, y - size * 0.35, size * 0.5, size * 0.3, 2);
        ctx.fill();
        ctx.stroke();
        
        // Tank cannon
        ctx.beginPath();
        ctx.moveTo(x + size * 0.25, y - size * 0.2);
        ctx.lineTo(x + size * 0.55, y - size * 0.2);
        ctx.lineTo(x + size * 0.55, y - size * 0.12);
        ctx.lineTo(x + size * 0.25, y - size * 0.12);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Tank tracks
        ctx.fillStyle = '#333';
        ctx.beginPath();
        ctx.roundRect(x - size * 0.5, y + size * 0.2, size * 1.0, size * 0.2, 2);
        ctx.fill();
        ctx.stroke();
        
        ctx.fillStyle = fillStyle;
    }

    drawStarIcon(x, y, size) {
        this.drawStar(x, y, 5, size * 0.5, size * 0.25);
        this.ctx.fill();
        this.ctx.stroke();
    }

    drawDiamondIcon(x, y, size) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, y - size * 0.5);
        this.ctx.lineTo(x + size * 0.4, y);
        this.ctx.lineTo(x, y + size * 0.5);
        this.ctx.lineTo(x - size * 0.4, y);
        this.ctx.closePath();
        this.ctx.fill();
        this.ctx.stroke();
    }

    drawPlaneIcon(x, y, size) {
        const ctx = this.ctx;
        
        // Fuselage
        ctx.beginPath();
        ctx.moveTo(x - size * 0.5, y);
        ctx.lineTo(x + size * 0.4, y - size * 0.08);
        ctx.lineTo(x + size * 0.5, y);
        ctx.lineTo(x + size * 0.4, y + size * 0.08);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Wings
        ctx.beginPath();
        ctx.moveTo(x - size * 0.2, y);
        ctx.lineTo(x - size * 0.3, y - size * 0.45);
        ctx.lineTo(x - size * 0.1, y - size * 0.4);
        ctx.lineTo(x, y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        ctx.beginPath();
        ctx.moveTo(x - size * 0.2, y);
        ctx.lineTo(x - size * 0.3, y + size * 0.45);
        ctx.lineTo(x - size * 0.1, y + size * 0.4);
        ctx.lineTo(x, y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Tail
        ctx.beginPath();
        ctx.moveTo(x - size * 0.5, y);
        ctx.lineTo(x - size * 0.55, y - size * 0.25);
        ctx.lineTo(x - size * 0.45, y - size * 0.2);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
    }

    drawShipIcon(x, y, size) {
        const ctx = this.ctx;
        
        // Hull
        ctx.beginPath();
        ctx.moveTo(x - size * 0.5, y + size * 0.3);
        ctx.lineTo(x - size * 0.4, y);
        ctx.lineTo(x + size * 0.4, y);
        ctx.lineTo(x + size * 0.5, y + size * 0.3);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Superstructure
        ctx.beginPath();
        ctx.rect(x - size * 0.25, y - size * 0.3, size * 0.5, size * 0.3);
        ctx.fill();
        ctx.stroke();
        
        // Bridge/Tower
        ctx.beginPath();
        ctx.rect(x - size * 0.1, y - size * 0.5, size * 0.2, size * 0.2);
        ctx.fill();
        ctx.stroke();
        
        // Waves
        ctx.strokeStyle = '#4a9eff';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(x - size * 0.6, y + size * 0.35);
        ctx.quadraticCurveTo(x - size * 0.4, y + size * 0.25, x - size * 0.2, y + size * 0.35);
        ctx.quadraticCurveTo(x, y + size * 0.45, x + size * 0.2, y + size * 0.35);
        ctx.quadraticCurveTo(x + size * 0.4, y + size * 0.25, x + size * 0.6, y + size * 0.35);
        ctx.stroke();
    }

    drawMissileIcon(x, y, size) {
        const ctx = this.ctx;
        const fillStyle = ctx.fillStyle;
        
        // Missile body
        ctx.beginPath();
        ctx.moveTo(x - size * 0.45, y - size * 0.1);
        ctx.lineTo(x + size * 0.35, y - size * 0.1);
        ctx.lineTo(x + size * 0.5, y);
        ctx.lineTo(x + size * 0.35, y + size * 0.1);
        ctx.lineTo(x - size * 0.45, y + size * 0.1);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Fins
        ctx.beginPath();
        ctx.moveTo(x - size * 0.45, y - size * 0.1);
        ctx.lineTo(x - size * 0.5, y - size * 0.35);
        ctx.lineTo(x - size * 0.3, y - size * 0.1);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        ctx.beginPath();
        ctx.moveTo(x - size * 0.45, y + size * 0.1);
        ctx.lineTo(x - size * 0.5, y + size * 0.35);
        ctx.lineTo(x - size * 0.3, y + size * 0.1);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        
        // Flame/exhaust
        ctx.fillStyle = '#ff6600';
        ctx.beginPath();
        ctx.moveTo(x - size * 0.45, y - size * 0.08);
        ctx.lineTo(x - size * 0.65, y);
        ctx.lineTo(x - size * 0.45, y + size * 0.08);
        ctx.closePath();
        ctx.fill();
        
        ctx.fillStyle = fillStyle;
    }

    drawStar(cx, cy, spikes, outerRadius, innerRadius) {
        let rot = Math.PI / 2 * 3;
        let step = Math.PI / spikes;

        this.ctx.beginPath();
        this.ctx.moveTo(cx, cy - outerRadius);

        for (let i = 0; i < spikes; i++) {
            let x = cx + Math.cos(rot) * outerRadius;
            let y = cy + Math.sin(rot) * outerRadius;
            this.ctx.lineTo(x, y);
            rot += step;

            x = cx + Math.cos(rot) * innerRadius;
            y = cy + Math.sin(rot) * innerRadius;
            this.ctx.lineTo(x, y);
            rot += step;
        }

        this.ctx.lineTo(cx, cy - outerRadius);
        this.ctx.closePath();
    }

    /**
     * Render unit indicators on a tile
     */
    renderUnitIndicators(region, isoX, isoY, tileHeight) {
        if (!region.units) {
            return;
        }

        const units = region.units;
        const isMasked = !!units.masked;

        // Only render for own regions or visible enemy regions with masked data
        if (!region.isYours && !isMasked) {
            return;
        }

        const iconSize = 10;
        const spacing = 14;

        const categories = [
            { key: 'buildings', color: '#3d5a80', label: 'Buildings' },
            { key: 'defences', color: '#7b6d8d', label: 'Defences' },
            { key: 'special', color: '#d4a03c', label: 'Special' },
            { key: 'specialUnits', color: '#c75146', label: 'Elite Units' },
            { key: 'troops', color: '#5a8c5a', label: 'Troops' },
            { key: 'navalUnits', color: '#4a7ba7', label: 'Naval Units' },
            { key: 'airUnits', color: '#87ceeb', label: 'Air Units' },
            { key: 'missiles', color: '#d64545', label: 'Missiles' },
        ];

        // Collect active unit types (owned, or under construction for own regions)
        const activeTypes = [];
        categories.forEach(function (cat) {
            const details = units.details ? units.details[cat.key] : null;
            const hasConstruction = !isMasked && !!details && details.some(function (unit) {
                return unit.inConstruction > 0;
            });

            if ((units[cat.key] && units[cat.key] !== 0) || hasConstruction) {
                activeTypes.push({
                    type: cat.key,
                    count: isMasked ? '?' : units[cat.key],
                    inConstruction: units.inConstruction ? (units.inConstruction[cat.key] || 0) : 0,
                    hasConstruction: hasConstruction,
                    color: cat.color,
                    label: cat.label
                });
            }
        });

        if (activeTypes.length === 0) {
            return;
        }

        const startX = isoX - ((activeTypes.length - 1) * spacing) / 2;
        const baseY = isoY + tileHeight * 0.15;

        // Store icon positions for hover detection
        region.iconPositions = [];

        activeTypes.forEach((item, index) => {
            const iconX = startX + (index * spacing);
            const iconY = baseY;

            region.iconPositions.push({
                x: iconX,
                y: iconY,
                size: iconSize,
                type: item.type,
                count: item.count,
                inConstruction: item.inConstruction,
                hasConstruction: item.hasConstruction,
                label: item.label,
                color: item.color,
                details: units.details ? units.details[item.type] : null
            });

            // Draw background circle (red tint for enemy regions)
            this.ctx.save();
            this.ctx.fillStyle = isMasked ? 'rgba(80, 0, 0, 0.5)' : 'rgba(0, 0, 0, 0.6)';
            this.ctx.beginPath();
            this.ctx.arc(iconX, iconY, iconSize * 0.8, 0, Math.PI * 2);
            this.ctx.fill();
            this.ctx.restore();

            // Draw the icon, faded when nothing of this category is built yet
            this.ctx.save();
            if (item.count === 0) {
                this.ctx.globalAlpha = 0.5;
            }
            this.drawUnitIcon(item.type, iconX, iconY, iconSize, item.color);
            this.ctx.restore();

            // Draw a construction badge when units of this category are under construction
            if (item.hasConstruction) {
                this.ctx.save();
                this.ctx.fillStyle = '#ffa500';
                this.ctx.strokeStyle = '#000000';
                this.ctx.lineWidth = 1;
                this.ctx.beginPath();
                this.ctx.arc(iconX + iconSize * 0.6, iconY - iconSize * 0.6, 2.5, 0, Math.PI * 2);
                this.ctx.fill();
                this.ctx.stroke();
                this.ctx.restore();
            }
        });
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UnitRenderer;
}
