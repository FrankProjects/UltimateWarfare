/**
 * HexMap - A scrollable hexagonal world map renderer
 * Main orchestrator that coordinates all map modules
 * Uses pointy-top hexagons with odd-r offset coordinates
 */
class HexMap {
    constructor(canvasId, config = {}) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) {
            throw new Error(
                'Canvas element with id "' + canvasId + '" not found'
            );
        }

        this.ctx = this.canvas.getContext('2d');

        // Hex geometry (pointy-top)
        const hexSize = config.hexSize || 40;
        const hexWidth = Math.sqrt(3) * hexSize;
        const hexHeight = 2 * hexSize;

        this.config = {
            hexSize: hexSize,
            hexWidth: hexWidth,
            hexHeight: hexHeight,
            rowStepY: hexHeight * 0.75,
            imageBasePath: config.imageBasePath || '/images',
            overlaysEnabled: config.overlaysEnabled || false,
            ...config
        };

        // Map data
        this.sectors = [];
        this.sectorLookup = new Map();
        this.images = new Map();
        this.imagesLoaded = false;
        this.hoveredTile = null;
        this.highlightedRegionIds = null;
        this.attackTargetRegionId = null;

        // Initialize modules
        this.cameraController = new CameraController(this.canvas, {
            minZoom: 0.3,
            maxZoom: 3.0
        });
        this.fleetManager = new FleetManager(this);
        this.bombardmentManager = new BombardmentManager(this);
        this.tileRenderer = new TileRenderer(this.ctx, this.config);

        // Wire up camera callbacks
        this.cameraController.onRenderRequest = () => this.render();
        this.cameraController.onTileHover = (e) => this.updateHoveredTile(e);
        this.cameraController.onTileClick = (e) => this.handleTileClick(e);

        // Expose camera for external access
        this.camera = this.cameraController.camera;

        this.init();
    }

    init() {
        this.setupCanvas();
        this.cameraController.setupEventListeners();
        this.cameraController.centerCamera();
    }

    setupCanvas() {
        const resizeCanvas = () => {
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = rect.height;

            if (this.imagesLoaded) {
                this.render();
            }
        };

        requestAnimationFrame(() => resizeCanvas());
        window.addEventListener('resize', resizeCanvas);
    }

    // ===== Data Methods =====

    loadImages(sectors) {
        const imageUrls = new Set();

        sectors.forEach(sector => {
            imageUrls.add(
                this.config.imageBasePath + '/map/' + sector.image
            );
        });

        const loadPromises = Array.from(imageUrls).map(url => {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => {
                    this.images.set(url, img);
                    resolve();
                };
                img.onerror = () => {
                    console.warn('Failed to load tile image: ' + url);
                    resolve();
                };
                img.src = url;
            });
        });

        return Promise.all(loadPromises).then(() => {
            this.imagesLoaded = true;
        });
    }

    setSectors(sectors) {
        this.sectors = sectors;

        // Build O(1) lookup map keyed by "col,row"
        this.sectorLookup.clear();
        sectors.forEach(sector => {
            this.sectorLookup.set(sector.x + ',' + sector.y, sector);
        });

        this.tileRenderer.setSectorLookup(this.sectorLookup);

        return this.loadImages(sectors);
    }

    setFleets(fleets) {
        this.fleetManager.setFleets(fleets);
    }

    setBombardments(bombardments) {
        this.bombardmentManager.setBombardments(bombardments);
    }

    setHighlightedRegions(regionIds, targetRegionId) {
        this.highlightedRegionIds = regionIds;
        this.attackTargetRegionId = targetRegionId;
    }

    clearHighlightedRegions() {
        this.highlightedRegionIds = null;
        this.attackTargetRegionId = null;
    }

    // ===== Hex Coordinate Conversion =====

    /**
     * Convert grid (col, row) to pixel position (center of hex)
     * Uses odd-r offset: odd rows are shifted right by half a hex width
     */
    hexToPixel(col, row) {
        const x = col * this.config.hexWidth
            + (row % 2) * (this.config.hexWidth / 2);
        const y = row * this.config.rowStepY;
        return { x: x, y: y };
    }

    /**
     * Convert pixel position to grid (col, row) via nearest-hex lookup
     * Checks the estimated tile and its neighbors for the closest match
     */
    getTileAtScreenPos(screenX, screenY) {
        const world = this.cameraController.screenToWorld(screenX, screenY);

        // Approximate row from y
        const approxRow = Math.round(world.y / this.config.rowStepY);

        // Approximate col from x (accounting for odd-row offset)
        const rowOffset = (approxRow % 2) * (this.config.hexWidth / 2);
        const approxCol = Math.round(
            (world.x - rowOffset) / this.config.hexWidth
        );

        // Check candidate tile and 8 surrounding grid cells
        let bestTile = null;
        let bestDist = Infinity;

        for (let dr = -1; dr <= 1; dr++) {
            for (let dc = -1; dc <= 1; dc++) {
                const r = approxRow + dr;
                const c = approxCol + dc;
                const sector = this.sectorLookup.get(c + ',' + r);

                if (sector) {
                    const pos = this.hexToPixel(c, r);
                    const dx = world.x - pos.x;
                    const dy = world.y - pos.y;
                    const dist = dx * dx + dy * dy;

                    if (dist < bestDist) {
                        bestDist = dist;
                        bestTile = sector;
                    }
                }
            }
        }

        return bestTile;
    }

    // ===== Interaction =====

    updateHoveredTile(e) {
        const rect = this.canvas.getBoundingClientRect();
        const tile = this.getTileAtScreenPos(
            e.clientX - rect.left,
            e.clientY - rect.top
        );

        if (tile !== this.hoveredTile) {
            this.hoveredTile = tile;
            this.render();
        }
    }

    handleTileClick(e) {
        const rect = this.canvas.getBoundingClientRect();
        const tile = this.getTileAtScreenPos(
            e.clientX - rect.left,
            e.clientY - rect.top
        );

        if (tile && this.config.onTileClick) {
            this.config.onTileClick(tile);
        }
    }

    // ===== Rendering =====

    render() {
        if (!this.imagesLoaded) {
            return;
        }

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.ctx.save();
        this.cameraController.applyTransform(this.ctx);

        // Render only visible tiles (view frustum culling)
        const self = this;
        const bounds = this.cameraController.getVisibleBounds();
        const margin = this.config.hexWidth;
        const minRow = Math.floor((bounds.minY - margin) / this.config.rowStepY) - 1;
        const maxRow = Math.ceil((bounds.maxY + margin) / this.config.rowStepY) + 1;
        const minCol = Math.floor((bounds.minX - margin) / this.config.hexWidth) - 1;
        const maxCol = Math.ceil((bounds.maxX + margin) / this.config.hexWidth) + 1;

        for (let row = minRow; row <= maxRow; row++) {
            for (let col = minCol; col <= maxCol; col++) {
                const sector = this.sectorLookup.get(col + ',' + row);
                if (sector) {
                    this.renderTile(sector);
                }
            }
        }

        // Render fleet lines
        this.fleetManager.renderFleetLines(
            this.ctx,
            function (x, y) { return self.hexToPixel(x, y); }
        );

        // Render bombardment arcs
        this.bombardmentManager.renderBombardmentArcs(
            this.ctx,
            function (x, y) { return self.hexToPixel(x, y); }
        );

        this.ctx.restore();

        // Render UI elements
        this.renderUI();
        this.fleetManager.renderFleetUIElements(
            this.canvas,
            this.camera,
            function (x, y) { return self.hexToPixel(x, y); }
        );
    }

    renderTile(sector) {
        const pos = this.hexToPixel(sector.x, sector.y);
        const imageUrl = this.config.imageBasePath + '/map/' + sector.image;
        const img = this.images.get(imageUrl);

        this.tileRenderer.renderTile(
            sector, pos, img, this.hoveredTile, this.images
        );
    }

    renderUnitIndicators(region, posX, posY) {
        this.tileRenderer.renderUnitIndicators(region, posX, posY);
    }

    renderUI() {
        this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
        this.ctx.fillRect(10, 10, 150, 30);

        this.ctx.fillStyle = '#f3e6c1';
        this.ctx.font = '14px Arial';
        this.ctx.textAlign = 'left';
        this.ctx.fillText(
            'Zoom: ' + (this.camera.zoom * 100).toFixed(0) + '%',
            20,
            30
        );
    }

    // ===== Cleanup =====

    destroy() {
        this.fleetManager.destroy();
        this.bombardmentManager.destroy();
        window.removeEventListener('resize', this.setupCanvas);
    }
}

// Backward compatibility alias
const IsometricMap = HexMap;

if (typeof module !== 'undefined' && module.exports) {
    module.exports = HexMap;
}
