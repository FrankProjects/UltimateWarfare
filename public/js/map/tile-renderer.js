/**
 * TileRenderer - Handles rendering of hex map tiles with game-specific features
 * Uses pointy-top hexagons
 */
class TileRenderer {
    constructor(ctx, config) {
        this.ctx = ctx;
        this.config = config;
        this.unitRenderer = new UnitRenderer(ctx);
        this.fogCache = new Map();
        this.sectorLookup = null;

        // Pre-compute hex vertex offsets (pointy-top, 6 vertices at 60° intervals from -30°)
        this.hexVertices = [];
        for (let i = 0; i < 6; i++) {
            const angle = (Math.PI / 180) * (60 * i - 30);
            this.hexVertices.push({
                dx: config.hexSize * Math.cos(angle),
                dy: config.hexSize * Math.sin(angle)
            });
        }
    }

    /**
     * Render a tile with all game-specific features
     */
    renderTile(region, pos, img, hoveredTile, images) {
        if (img) {
            // Clip terrain image to hex shape
            this.ctx.save();
            this.drawHex(pos);
            this.ctx.clip();

            // Draw the terrain image scaled to fill the hex bounding box
            this.ctx.drawImage(
                img,
                pos.x - this.config.hexWidth / 2,
                pos.y - this.config.hexHeight / 2,
                this.config.hexWidth,
                this.config.hexHeight
            );
            this.ctx.restore();

            // Draw subtle hex border
            this.ctx.save();
            this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.3)';
            this.ctx.lineWidth = 1;
            this.drawHex(pos);
            this.ctx.stroke();
            this.ctx.restore();

            // Draw territory border for owned regions
            this.renderTerritoryBorder(region, pos);

            // Draw fog of war if region is not visible
            if (region.isVisible === false) {
                this.renderFogOfWar(pos, region.x, region.y);
            }

            // Draw visibility border on visible regions adjacent to fog
            this.renderVisibilityBorder(region, pos);

            // Add ownership overlay if enabled
            if (this.config.overlaysEnabled) {
                this.renderOwnershipOverlay(region, pos);
            }

            // Attack mode highlighting
            this.renderModeHighlights(region, pos);

            // Draw coordinates and owner name
            this.renderTileLabels(region, pos);

            // Render unit indicators for your regions
            this.unitRenderer.renderUnitIndicators(
                region, pos.x, pos.y, this.config.hexHeight
            );

            // Highlight hovered tile
            if (
                hoveredTile
                && hoveredTile.x === region.x
                && hoveredTile.y === region.y
            ) {
                this.renderHoverHighlight(region, pos);
            }
        } else {
            // Fallback if image not loaded - draw colored hex
            this.renderFallbackTile(region, pos);
        }
    }

    /**
     * Render attack/send mode highlights on a tile
     */
    renderModeHighlights(region, pos) {
        if (region._attackTarget) {
            this.ctx.save();
            this.ctx.strokeStyle = '#ff0000';
            this.ctx.lineWidth = 3;
            this.drawHex(pos);
            this.ctx.stroke();
            this.ctx.restore();
        } else if (region._sendSource) {
            this.ctx.save();
            this.ctx.strokeStyle = '#2196F3';
            this.ctx.lineWidth = 3;
            this.drawHex(pos);
            this.ctx.stroke();
            this.ctx.restore();
        } else if (region._attackEligible) {
            this.ctx.save();
            this.ctx.fillStyle = 'rgba(0, 255, 100, 0.35)';
            this.drawHex(pos);
            this.ctx.fill();
            this.ctx.strokeStyle = '#00ff64';
            this.ctx.lineWidth = 2;
            this.ctx.stroke();
            this.ctx.restore();
        } else if (
            typeof region._attackEligible !== 'undefined'
            && !region._attackEligible
        ) {
            this.ctx.save();
            this.ctx.fillStyle = 'rgba(0, 0, 0, 0.4)';
            this.drawHex(pos);
            this.ctx.fill();
            this.ctx.restore();
        }
    }

    /**
     * Render ownership overlay as a hex shape
     */
    renderOwnershipOverlay(region, pos) {
        this.ctx.save();

        if (region.hasOwner) {
            if (region.isYours) {
                this.ctx.fillStyle = 'rgba(74, 90, 124, 0.9)';
            } else {
                this.ctx.fillStyle = 'rgba(124, 74, 74, 0.9)';
            }
        } else {
            this.ctx.fillStyle = 'rgba(74, 124, 89, 0.9)';
        }

        this.drawHex(pos);
        this.ctx.fill();
        this.ctx.restore();
    }

    /**
     * Render coordinates and owner name labels
     */
    renderTileLabels(region, pos) {
        this.ctx.save();
        this.ctx.fillStyle = '#ffffff';
        this.ctx.font = '9px Arial';
        this.ctx.textAlign = 'center';
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 2;

        const coordText = region.x + ',' + region.y;
        this.ctx.strokeText(coordText, pos.x, pos.y - 8);
        this.ctx.fillText(coordText, pos.x, pos.y - 8);

        if (region.hasOwner && region.ownerName) {
            this.ctx.fillStyle = region.isYours
                ? 'rgba(100, 150, 255, 0.95)'
                : 'rgba(255, 100, 100, 0.95)';
            this.ctx.font = '10px Arial';
            this.ctx.strokeText(region.ownerName, pos.x, pos.y + 2);
            this.ctx.fillText(region.ownerName, pos.x, pos.y + 2);
        }

        this.ctx.restore();
    }

    /**
     * Render hover highlight with hex outline and coordinate info
     */
    renderHoverHighlight(region, pos) {
        // Draw hex outline
        this.ctx.save();
        this.ctx.strokeStyle = '#ffffff';
        this.ctx.lineWidth = 3;
        this.drawHex(pos);
        this.ctx.stroke();
        this.ctx.restore();

        // Draw enhanced region info above the tile
        this.ctx.save();
        this.ctx.fillStyle = '#ffffff';
        this.ctx.font = 'bold 12px Arial';
        this.ctx.textAlign = 'center';
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 3;

        const hoverText = region.x + ', ' + region.y;
        const labelY = pos.y - this.config.hexSize - 5;
        this.ctx.strokeText(hoverText, pos.x, labelY);
        this.ctx.fillText(hoverText, pos.x, labelY);

        this.ctx.restore();
    }

    /**
     * Render fallback tile when image not loaded
     */
    renderFallbackTile(region, pos) {
        this.ctx.save();

        const terrainColors = {
            deep_water: '#143278',
            water: '#2350a5',
            shallow_water: '#4696c3',
            sand: '#d2be8c',
            grassland: '#4b8c3c',
            forest: '#235523',
            hills: '#6e914b',
            mountain: '#645f55',
        };

        if (region.hasOwner) {
            this.ctx.fillStyle = region.isYours
                ? '#4a5a7c'
                : '#7c4a4a';
        } else {
            this.ctx.fillStyle = terrainColors[region.type] || '#4a7c59';
        }

        this.drawHex(pos);
        this.ctx.fill();
        this.ctx.restore();
    }

    setSectorLookup(sectorLookup) {
        this.sectorLookup = sectorLookup;
    }

    /**
     * Render territory border around player-owned regions.
     * Only draws edges where the neighbor is NOT also owned by the player,
     * creating a cohesive territory outline.
     */
    renderTerritoryBorder(region, pos) {
        if (!region.isYours || !this.sectorLookup) {
            return;
        }

        const isOddRow = region.y % 2 !== 0;

        // Neighbor offsets per edge for odd-r pointy-top hex grid
        // Edge i connects vertex i to vertex (i+1)%6
        const neighborOffsets = isOddRow
            ? [
                [1, 0],    // Edge 0: Right
                [1, 1],    // Edge 1: SE
                [0, 1],    // Edge 2: SW
                [-1, 0],   // Edge 3: Left
                [0, -1],   // Edge 4: NW
                [1, -1],   // Edge 5: NE
            ]
            : [
                [1, 0],    // Edge 0: Right
                [0, 1],    // Edge 1: SE
                [-1, 1],   // Edge 2: SW
                [-1, 0],   // Edge 3: Left
                [-1, -1],  // Edge 4: NW
                [0, -1],   // Edge 5: NE
            ];

        this.ctx.save();
        this.ctx.strokeStyle = '#22c55e';
        this.ctx.lineWidth = 3;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        const verts = this.hexVertices;

        for (let i = 0; i < 6; i++) {
            const [dx, dy] = neighborOffsets[i];
            const neighborKey = (region.x + dx) + ',' + (region.y + dy);
            const neighbor = this.sectorLookup.get(neighborKey);

            if (!neighbor || !neighbor.isYours) {
                const j = (i + 1) % 6;
                this.ctx.beginPath();
                this.ctx.moveTo(pos.x + verts[i].dx, pos.y + verts[i].dy);
                this.ctx.lineTo(pos.x + verts[j].dx, pos.y + verts[j].dy);
                this.ctx.stroke();
            }
        }

        this.ctx.restore();
    }

    /**
     * Render visibility border on visible regions adjacent to fog of war.
     * Draws thick red edges where a visible region borders a non-visible one.
     */
    renderVisibilityBorder(region, pos) {
        if (region.isVisible === false || !this.sectorLookup) {
            return;
        }

        const isOddRow = region.y % 2 !== 0;

        const neighborOffsets = isOddRow
            ? [
                [1, 0],    // Edge 0: Right
                [1, 1],    // Edge 1: SE
                [0, 1],    // Edge 2: SW
                [-1, 0],   // Edge 3: Left
                [0, -1],   // Edge 4: NW
                [1, -1],   // Edge 5: NE
            ]
            : [
                [1, 0],    // Edge 0: Right
                [0, 1],    // Edge 1: SE
                [-1, 1],   // Edge 2: SW
                [-1, 0],   // Edge 3: Left
                [-1, -1],  // Edge 4: NW
                [0, -1],   // Edge 5: NE
            ];

        this.ctx.save();
        this.ctx.strokeStyle = '#dc2626';
        this.ctx.lineWidth = 4;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        const verts = this.hexVertices;

        for (let i = 0; i < 6; i++) {
            const [dx, dy] = neighborOffsets[i];
            const neighborKey = (region.x + dx) + ',' + (region.y + dy);
            const neighbor = this.sectorLookup.get(neighborKey);

            if (!neighbor || neighbor.isVisible === false) {
                const j = (i + 1) % 6;
                this.ctx.beginPath();
                this.ctx.moveTo(pos.x + verts[i].dx, pos.y + verts[i].dy);
                this.ctx.lineTo(pos.x + verts[j].dx, pos.y + verts[j].dy);
                this.ctx.stroke();
            }
        }

        this.ctx.restore();
    }

    /**
     * Draw a pointy-top hexagon centered at pos
     * 6 vertices at 60-degree intervals starting at -30 degrees
     */
    drawHex(pos) {
        const verts = this.hexVertices;
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x + verts[0].dx, pos.y + verts[0].dy);
        this.ctx.lineTo(pos.x + verts[1].dx, pos.y + verts[1].dy);
        this.ctx.lineTo(pos.x + verts[2].dx, pos.y + verts[2].dy);
        this.ctx.lineTo(pos.x + verts[3].dx, pos.y + verts[3].dy);
        this.ctx.lineTo(pos.x + verts[4].dx, pos.y + verts[4].dy);
        this.ctx.lineTo(pos.x + verts[5].dx, pos.y + verts[5].dy);
        this.ctx.closePath();
    }

    /**
     * Proxy to unit renderer for external access
     */
    renderUnitIndicators(region, posX, posY) {
        this.unitRenderer.renderUnitIndicators(
            region, posX, posY, this.config.hexHeight
        );
    }

    /**
     * Get or create a cached fog-of-war off-screen canvas for a given variant
     */
    getFogCanvas(variant) {
        if (this.fogCache.has(variant)) {
            return this.fogCache.get(variant);
        }

        const w = Math.ceil(this.config.hexWidth) + 2;
        const h = Math.ceil(this.config.hexHeight) + 2;
        const offscreen = document.createElement('canvas');
        offscreen.width = w;
        offscreen.height = h;
        const offCtx = offscreen.getContext('2d');

        // Draw centered at (w/2, h/2)
        const cx = w / 2;
        const cy = h / 2;
        const size = this.config.hexSize;

        // Dark overlay with radial gradient
        const gradient = offCtx.createRadialGradient(cx, cy, 0, cx, cy, size);
        gradient.addColorStop(0, 'rgba(25, 25, 35, 0.45)');
        gradient.addColorStop(1, 'rgba(15, 15, 25, 0.5)');
        offCtx.fillStyle = gradient;
        offCtx.fillRect(0, 0, w, h);

        // Static cloud effects (deterministic from variant)
        const seed = variant * 137;
        for (let i = 0; i < 2; i++) {
            const angle = ((seed + i * 100) % 360) * Math.PI / 180;
            const offsetX = Math.cos(angle) * 12;
            const offsetY = Math.sin(angle) * 8;
            const radius = 25 + ((seed + i * 50) % 10);

            const cloudGrad = offCtx.createRadialGradient(
                cx + offsetX, cy + offsetY, 0,
                cx + offsetX, cy + offsetY, radius
            );
            cloudGrad.addColorStop(0, 'rgba(50, 50, 70, 0.25)');
            cloudGrad.addColorStop(0.5, 'rgba(40, 40, 60, 0.15)');
            cloudGrad.addColorStop(1, 'rgba(30, 30, 50, 0)');

            offCtx.fillStyle = cloudGrad;
            offCtx.fillRect(0, 0, w, h);
        }

        // Question mark for unknown territory
        offCtx.fillStyle = 'rgba(160, 160, 180, 0.5)';
        offCtx.font = 'bold 28px Arial';
        offCtx.textAlign = 'center';
        offCtx.textBaseline = 'middle';
        offCtx.shadowColor = 'rgba(0, 0, 0, 0.3)';
        offCtx.shadowBlur = 4;
        offCtx.fillText('?', cx, cy);

        this.fogCache.set(variant, offscreen);
        return offscreen;
    }

    /**
     * Render fog of war as a semi-transparent overlay clipped to hex
     */
    renderFogOfWar(pos, tileX, tileY) {
        this.ctx.save();

        // Hex clipping path
        this.drawHex(pos);
        this.ctx.clip();

        // Blit cached fog variant
        const seed = tileX * 73 + tileY * 37;
        const variant = ((seed % 8) + 8) % 8;
        const fogCanvas = this.getFogCanvas(variant);
        this.ctx.drawImage(
            fogCanvas,
            pos.x - fogCanvas.width / 2,
            pos.y - fogCanvas.height / 2
        );

        this.ctx.restore();
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TileRenderer;
}
