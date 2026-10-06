/**
 * CameraController - Handles camera/viewport and user interaction
 */
class CameraController {
    constructor(canvas, config = {}) {
        this.canvas = canvas;
        this.config = config;
        
        this.camera = {
            x: 0,
            y: 0,
            zoom: 1.0,
            minZoom: config.minZoom || 0.5,
            maxZoom: config.maxZoom || 2.0
        };

        this.isDragging = false;
        this.hasDragged = false;
        this.lastMousePos = { x: 0, y: 0 };
        
        this.onRenderRequest = null;
        this.onTileHover = null;
        this.onTileClick = null;
    }

    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        this.setupMouseEvents();
        this.setupTouchEvents();
        this.canvas.style.cursor = 'grab';
    }

    setupMouseEvents() {
        // Mouse wheel zoom
        this.canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomDelta = e.deltaY > 0 ? 0.9 : 1.1;
            const newZoom = this.camera.zoom * zoomDelta;

            if (newZoom >= this.camera.minZoom && newZoom <= this.camera.maxZoom) {
                const rect = this.canvas.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;

                this.camera.x -= (mouseX - this.canvas.width / 2) * (zoomDelta - 1);
                this.camera.y -= (mouseY - this.canvas.height / 2) * (zoomDelta - 1);
                this.camera.zoom = newZoom;

                this.requestRender();
            }
        }, { passive: false });

        // Mouse dragging
        this.canvas.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.hasDragged = false;
            this.lastMousePos = { x: e.clientX, y: e.clientY };
            this.canvas.style.cursor = 'grabbing';
        });

        this.canvas.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                const dx = e.clientX - this.lastMousePos.x;
                const dy = e.clientY - this.lastMousePos.y;

                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                    this.hasDragged = true;
                }

                this.camera.x += dx;
                this.camera.y += dy;

                this.lastMousePos = { x: e.clientX, y: e.clientY };
                this.requestRender();
            } else if (this.onTileHover) {
                this.onTileHover(e);
            }
        });

        this.canvas.addEventListener('mouseup', () => {
            this.isDragging = false;
            this.canvas.style.cursor = 'grab';
        });

        this.canvas.addEventListener('mouseleave', () => {
            this.isDragging = false;
            this.canvas.style.cursor = 'default';
        });

        // Click handling
        this.canvas.addEventListener('click', (e) => {
            if (!this.hasDragged && this.onTileClick) {
                this.onTileClick(e);
            }
            this.hasDragged = false;
        });
    }

    setupTouchEvents() {
        let lastTouchDistance = 0;

        this.canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.hasDragged = false;
                this.lastMousePos = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            } else if (e.touches.length === 2) {
                lastTouchDistance = this.getTouchDistance(e.touches);
            }
            e.preventDefault();
        }, { passive: false });

        this.canvas.addEventListener('touchmove', (e) => {
            if (e.touches.length === 1 && this.isDragging) {
                const dx = e.touches[0].clientX - this.lastMousePos.x;
                const dy = e.touches[0].clientY - this.lastMousePos.y;

                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                    this.hasDragged = true;
                }

                this.camera.x += dx;
                this.camera.y += dy;

                this.lastMousePos = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
                this.requestRender();
            } else if (e.touches.length === 2) {
                const currentDistance = this.getTouchDistance(e.touches);
                const zoomDelta = currentDistance / lastTouchDistance;

                const newZoom = this.camera.zoom * zoomDelta;
                if (newZoom >= this.camera.minZoom && newZoom <= this.camera.maxZoom) {
                    this.camera.zoom = newZoom;
                    this.requestRender();
                }

                lastTouchDistance = currentDistance;
            }
            e.preventDefault();
        }, { passive: false });

        this.canvas.addEventListener('touchend', () => {
            this.isDragging = false;
        });
    }

    getTouchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    requestRender() {
        if (this.onRenderRequest) {
            this.onRenderRequest();
        }
    }

    centerCamera() {
        this.camera.x = 0;
        this.camera.y = 0;
    }

    resetView(worldX, worldY) {
        this.camera.zoom = 1.0;
        if (worldX !== undefined && worldY !== undefined) {
            this.camera.x = -worldX;
            this.camera.y = -worldY;
        } else {
            this.centerCamera();
        }
        this.requestRender();
    }

    zoomIn() {
        const newZoom = this.camera.zoom * 1.2;
        if (newZoom <= this.camera.maxZoom) {
            this.camera.zoom = newZoom;
            this.requestRender();
        }
    }

    zoomOut() {
        const newZoom = this.camera.zoom * 0.8;
        if (newZoom >= this.camera.minZoom) {
            this.camera.zoom = newZoom;
            this.requestRender();
        }
    }

    /**
     * Apply camera transformations to context
     */
    applyTransform(ctx) {
        ctx.translate(this.canvas.width / 2, this.canvas.height / 2);
        ctx.translate(this.camera.x, this.camera.y);
        ctx.scale(this.camera.zoom, this.camera.zoom);
    }

    /**
     * Get world-space bounding rectangle of the current viewport
     */
    getVisibleBounds() {
        const halfW = this.canvas.width / 2;
        const halfH = this.canvas.height / 2;
        return {
            minX: (-halfW - this.camera.x) / this.camera.zoom,
            maxX: (halfW - this.camera.x) / this.camera.zoom,
            minY: (-halfH - this.camera.y) / this.camera.zoom,
            maxY: (halfH - this.camera.y) / this.camera.zoom
        };
    }

    /**
     * Convert screen position to world position
     */
    screenToWorld(screenX, screenY) {
        const worldX = (screenX - this.canvas.width / 2 - this.camera.x) / this.camera.zoom;
        const worldY = (screenY - this.canvas.height / 2 - this.camera.y) / this.camera.zoom;
        return { x: worldX, y: worldY };
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CameraController;
}
