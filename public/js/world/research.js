(function () {
    const researchModal = document.getElementById('researchModal');
    const researchTreeContainer = document.getElementById('researchTreeContainer');

    let timerInterval = null;

    const CATEGORIES = {
        espionage: { label: 'Espionage', color: '#9c27b0' },
        economy:   { label: 'Economy',   color: '#4CAF50' },
        military:  { label: 'Military',  color: '#c44'    }
    };

    const FOUNDATION_TIER = { id: 'foundation', label: 'Foundation', gateLabel: 'Drives all tier progression' };

    const TIERS = [
        { id: 'tier1', label: 'Tier 1', gateLabel: 'Requires Research Tier 1' },
        { id: 'tier2', label: 'Tier 2', gateLabel: 'Requires Research Tier 2' },
        { id: 'tier3', label: 'Tier 3', gateLabel: 'Requires Research Tier 3' },
        { id: 'tier4', label: 'Tier 4', gateLabel: 'Requires Research Tier 4' },
        { id: 'tier5', label: 'Tier 5', gateLabel: 'Requires Research Tier 5' }
    ];

    // Each non-foundation entry has: tier (one of TIERS ids), category (one of CATEGORIES),
    // and an optional col (sub-column inside the lane, default 0).
    const TREE_LAYOUT = {
        'research-tier':                                  { tier: 'foundation' },

        // Tier 1
        'factory-blueprint':                              { tier: 'tier1', category: 'economy'  },
        'defensive-network':                              { tier: 'tier1', category: 'military', col: 0 },

        // Tier 2
        'spy-technology':                                 { tier: 'tier2', category: 'espionage' },
        'ore-extraction-improvements':                    { tier: 'tier2', category: 'economy'  },
        'radar-technology':                               { tier: 'tier2', category: 'military', col: 0 },
        'advanced-optics':                                { tier: 'tier2', category: 'military', col: 1 },
        'special-operation-artillery-bombardment':        { tier: 'tier2', category: 'military', col: 2 },

        // Tier 3
        'counter-espionage':                              { tier: 'tier3', category: 'espionage' },
        'advanced-wood-processing':                       { tier: 'tier3', category: 'economy'  },
        'submarine-technology':                           { tier: 'tier3', category: 'military', col: 0 },
        'special-operation-bomber-attack':                { tier: 'tier3', category: 'military', col: 1 },

        // Tier 4
        'efficient-building-technology':                  { tier: 'tier4', category: 'economy'  },
        'naval-bombardment':                              { tier: 'tier4', category: 'military', col: 0 },
        'ballistic-missile-technology':                   { tier: 'tier4', category: 'military', col: 1 },
        'special-operation-strategic-bomber-attack':      { tier: 'tier4', category: 'military', col: 2 },

        // Tier 5
        'nuclear-technology':                             { tier: 'tier5', category: 'military' }
    };

    // Sub-columns each category lane needs (max col + 1 across all of TREE_LAYOUT).
    const LANE_COLS = { espionage: 1, economy: 1, military: 3 };

    const NODE_WIDTH = 220;
    const NODE_HEIGHT = 420;
    const NODE_H_GAP = 16;
    const NODE_V_GAP = 28;
    const LANE_GAP = 24;
    const SIDE_PADDING = 24;
    const TIER_HEADER_HEIGHT = 32;
    const LANE_HEADER_HEIGHT = 36;
    const FOUNDATION_PADDING_BOTTOM = 24;

    researchModal.addEventListener('hidden.bs.modal', function () {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    });

    function showResearchModal() {
        bootstrap.Modal.getOrCreateInstance(researchModal).show();
        loadResearchTree();
    }

    async function loadResearchTree() {
        researchTreeContainer.innerHTML = '<div class="build-loading">Loading research tree...</div>';

        try {
            const response = await fetch('/game/api/research/tree');
            const data = await response.json();

            if (!data.success) {
                researchTreeContainer.innerHTML = '<div style="color: #c44; padding: 20px;">Failed to load research tree.</div>';
                return;
            }

            renderTree(data.research);
        } catch (e) {
            researchTreeContainer.innerHTML = '<div style="color: #c44; padding: 20px;">Error loading research tree.</div>';
        }
    }

    function buildItemMap(researchList) {
        const map = {};
        for (let i = 0; i < researchList.length; i++) {
            map[researchList[i].slug] = researchList[i];
        }
        return map;
    }

    function computeGeometry() {
        const laneWidths = {};
        for (const key in CATEGORIES) {
            const cols = LANE_COLS[key];
            laneWidths[key] = cols * NODE_WIDTH + (cols - 1) * NODE_H_GAP;
        }

        const laneX = {};
        let cursorX = SIDE_PADDING;
        const orderedCategoryKeys = Object.keys(CATEGORIES);
        for (let i = 0; i < orderedCategoryKeys.length; i++) {
            const key = orderedCategoryKeys[i];
            laneX[key] = cursorX;
            cursorX += laneWidths[key];
            if (i < orderedCategoryKeys.length - 1) cursorX += LANE_GAP;
        }
        const totalWidth = cursorX + SIDE_PADDING;

        const foundationHeight = TIER_HEADER_HEIGHT + NODE_HEIGHT + FOUNDATION_PADDING_BOTTOM;
        const tierRowHeight = TIER_HEADER_HEIGHT + NODE_HEIGHT + NODE_V_GAP;
        const gridTop = foundationHeight + LANE_HEADER_HEIGHT;
        const totalHeight = gridTop + tierRowHeight * TIERS.length;

        return {
            laneWidths: laneWidths,
            laneX: laneX,
            totalWidth: totalWidth,
            totalHeight: totalHeight,
            foundationHeight: foundationHeight,
            laneHeaderTop: foundationHeight,
            gridTop: gridTop,
            tierRowHeight: tierRowHeight
        };
    }

    function nodePosition(slug, geom) {
        const layout = TREE_LAYOUT[slug];
        if (!layout) return null;

        if (layout.tier === 'foundation') {
            const x = Math.round((geom.totalWidth - NODE_WIDTH) / 2);
            const y = TIER_HEADER_HEIGHT;
            return positionRect(x, y);
        }

        const tierIdx = indexOfTier(layout.tier);
        if (tierIdx < 0) return null;

        const subCol = layout.col || 0;
        const x = geom.laneX[layout.category] + subCol * (NODE_WIDTH + NODE_H_GAP);
        const y = geom.gridTop + tierIdx * geom.tierRowHeight + TIER_HEADER_HEIGHT;
        return positionRect(x, y);
    }

    function positionRect(x, y) {
        return {
            x: x,
            y: y,
            centerX: x + NODE_WIDTH / 2,
            centerY: y + NODE_HEIGHT / 2,
            right: x + NODE_WIDTH,
            bottom: y + NODE_HEIGHT
        };
    }

    function indexOfTier(tierId) {
        for (let i = 0; i < TIERS.length; i++) {
            if (TIERS[i].id === tierId) return i;
        }
        return -1;
    }

    function renderTree(researchList) {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }

        const itemBySlug = buildItemMap(researchList);
        const geom = computeGeometry();

        const containerWidth = Math.max(geom.totalWidth, 600);
        const containerHeight = geom.totalHeight;

        let html = '<div class="research-tree-inner" style="width: ' + containerWidth + 'px; height: ' + containerHeight + 'px;">';

        // 1) Foundation tier band (full width, gold-tinted) with header + centered node.
        html += '<div class="research-foundation-band" style="top: 0; height: ' + geom.foundationHeight + 'px; width: ' + containerWidth + 'px;">';
        html += '<div class="research-tier-header research-tier-header-foundation">';
        html += '<span class="research-tier-label">' + escapeHtml(FOUNDATION_TIER.label) + '</span>';
        html += '<span class="research-tier-gate">' + escapeHtml(FOUNDATION_TIER.gateLabel) + '</span>';
        html += '</div>';
        html += '</div>';

        // 2) Vertical category swim-lanes spanning the grid below the foundation.
        const gridContentHeight = geom.totalHeight - geom.foundationHeight;
        for (const catKey in CATEGORIES) {
            const cat = CATEGORIES[catKey];
            const x = geom.laneX[catKey];
            const w = geom.laneWidths[catKey];
            html += '<div class="research-lane" data-category="' + catKey + '" ';
            html += 'style="left: ' + x + 'px; top: ' + geom.foundationHeight + 'px; ';
            html += 'width: ' + w + 'px; height: ' + gridContentHeight + 'px;">';
            html += '<div class="research-lane-header" style="background-color: ' + cat.color + ';">';
            html += escapeHtml(cat.label);
            html += '</div>';
            html += '</div>';
        }

        // 3) Tier band overlays — horizontal label strip at the top of each tier row.
        for (let t = 0; t < TIERS.length; t++) {
            const tier = TIERS[t];
            const top = geom.gridTop + t * geom.tierRowHeight;
            html += '<div class="research-tier-band" data-tier="' + tier.id + '" ';
            html += 'style="top: ' + top + 'px; height: ' + geom.tierRowHeight + 'px; width: ' + containerWidth + 'px;">';
            html += '<div class="research-tier-header">';
            html += '<span class="research-tier-label">' + escapeHtml(tier.label) + '</span>';
            html += '<span class="research-tier-gate">' + escapeHtml(tier.gateLabel) + '</span>';
            html += '</div>';
            html += '</div>';
        }

        // 4) SVG overlay for cross-research dependency lines.
        // Skip lines from `research-tier` — tier bands already convey that gating.
        html += '<svg class="research-tree-svg" width="' + containerWidth + '" height="' + containerHeight + '">';
        for (let i = 0; i < researchList.length; i++) {
            const item = researchList[i];
            const childPos = nodePosition(item.slug, geom);
            if (!childPos) continue;
            const prereqs = item.prerequisites || [];
            for (let p = 0; p < prereqs.length; p++) {
                const prereq = prereqs[p];
                if (prereq.slug === 'research-tier') continue;
                const parentPos = nodePosition(prereq.slug, geom);
                if (!parentPos) continue;
                const parentItem = itemBySlug[prereq.slug];
                const prereqMet = parentItem && parentItem.currentLevel >= prereq.minLevel;
                const lineClass = 'research-line ' + (prereqMet ? 'research-line-met' : 'research-line-unmet');

                const x1 = parentPos.centerX;
                const y1 = parentPos.bottom;
                const x2 = childPos.centerX;
                const y2 = childPos.y;
                html += '<line class="' + lineClass + '" x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" />';
            }
        }
        html += '</svg>';

        // 5) Nodes.
        for (const slug in TREE_LAYOUT) {
            const item = itemBySlug[slug];
            if (!item) continue;
            const pos = nodePosition(slug, geom);
            if (!pos) continue;
            const layout = TREE_LAYOUT[slug];
            const categoryKey = layout.category || 'foundation';
            const isFoundation = layout.tier === 'foundation';
            html += renderNode(item, pos, categoryKey, isFoundation);
        }

        html += '</div>';
        researchTreeContainer.innerHTML = html;

        bindNodeHandlers();
        startTimers();
    }

    function renderNode(item, pos, categoryKey, isFoundation) {
        const showsLevels = item.maxLevel > 1;
        const isMaxed = item.status === 'maxed';
        const isResearched = item.currentLevel > 0;

        let cls = 'research-node research-' + item.status;
        if (isFoundation) cls += ' research-foundation';

        let html = '<div class="' + cls + '" data-category="' + categoryKey + '" ';
        html += 'style="left: ' + pos.x + 'px; top: ' + pos.y + 'px;">';
        html += '<img class="research-node-image" src="' + WorldApp.imageBasePath + '/research/' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '">';
        html += '<div class="research-node-name">' + escapeHtml(item.name) + '</div>';

        if (showsLevels) {
            html += renderLevelIndicator(item.currentLevel, item.maxLevel, isMaxed);
        }

        html += '<div class="research-node-desc">' + escapeHtml(item.description) + '</div>';

        if (item.nextCost !== null && item.nextDuration !== null) {
            html += '<div class="research-node-info">';
            html += '<span>$' + formatNumber(item.nextCost) + '</span>';
            html += '<span>' + formatDuration(item.nextDuration) + '</span>';
            html += '</div>';
        }

        if (item.status === 'available') {
            const buttonLabel = (showsLevels && isResearched)
                ? 'Upgrade to Lvl ' + (item.currentLevel + 1)
                : 'Research';
            html += '<div class="research-node-action"><button class="research-start-btn" data-slug="' + item.slug + '">' + escapeHtml(buttonLabel) + '</button></div>';
        } else if (item.status === 'researching') {
            const targetLabel = showsLevels && item.targetLevel !== null
                ? 'Lvl ' + item.targetLevel + ' '
                : '';
            html += '<div class="research-node-action">';
            html += '<span class="research-timer" data-remaining="' + item.remainingSeconds + '">' + escapeHtml(targetLabel) + formatTime(item.remainingSeconds) + '</span>';
            html += '<button class="research-cancel-btn" data-slug="' + item.slug + '">Cancel</button>';
            html += '</div>';
        } else if (isMaxed && !showsLevels) {
            html += '<div class="research-node-status">Completed</div>';
        }

        html += '</div>';
        return html;
    }

    function renderLevelIndicator(currentLevel, maxLevel, isMaxed) {
        let html = '<div class="research-node-level' + (isMaxed ? ' research-node-level-maxed' : '') + '">';
        html += '<span class="research-node-level-text">';
        html += isMaxed ? 'Max Level ' + maxLevel : 'Level ' + currentLevel + ' / ' + maxLevel;
        html += '</span>';
        html += '<span class="research-node-level-bar">';
        for (let i = 1; i <= maxLevel; i++) {
            const segCls = i <= currentLevel
                ? 'research-node-level-seg research-node-level-seg-filled'
                : 'research-node-level-seg';
            html += '<span class="' + segCls + '"></span>';
        }
        html += '</span>';
        html += '</div>';
        return html;
    }

    function bindNodeHandlers() {
        const startBtns = researchTreeContainer.querySelectorAll('.research-start-btn');
        for (let b = 0; b < startBtns.length; b++) {
            startBtns[b].addEventListener('click', function (e) {
                e.stopPropagation();
                startResearch(this.getAttribute('data-slug'));
            });
        }

        const cancelBtns = researchTreeContainer.querySelectorAll('.research-cancel-btn');
        for (let b = 0; b < cancelBtns.length; b++) {
            cancelBtns[b].addEventListener('click', function (e) {
                e.stopPropagation();
                cancelResearch(this.getAttribute('data-slug'));
            });
        }
    }

    function startTimers() {
        const timerElements = researchTreeContainer.querySelectorAll('.research-timer');
        if (timerElements.length === 0) return;

        timerInterval = setInterval(function () {
            for (let t = 0; t < timerElements.length; t++) {
                const el = timerElements[t];
                let remaining = parseInt(el.getAttribute('data-remaining'), 10);
                remaining--;
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    loadResearchTree();
                    return;
                }
                el.setAttribute('data-remaining', remaining);
                const prefix = el.textContent.match(/^Lvl \d+ /);
                el.textContent = (prefix ? prefix[0] : '') + formatTime(remaining);
            }
        }, 1000);
    }

    async function startResearch(slug) {
        try {
            const response = await fetch('/game/api/research/perform/' + slug, { method: 'POST' });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                if (result.newCash !== undefined) {
                    const cashEl = document.getElementById('resourceCash');
                    if (cashEl) {
                        cashEl.textContent = formatNumber(result.newCash);
                    }
                }
                loadResearchTree();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (e) {
            showNotification('Failed to start research.', 'error');
        }
    }

    async function cancelResearch(slug) {
        try {
            const response = await fetch('/game/api/research/cancel/' + slug, { method: 'POST' });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                loadResearchTree();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (e) {
            showNotification('Failed to cancel research.', 'error');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatDuration(seconds) {
        if (seconds <= 0) return '0s';
        const d = Math.floor(seconds / 86400);
        const h = Math.floor((seconds % 86400) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        if (d > 0) return d + 'd ' + h + 'h';
        if (h > 0) return h + 'h ' + m + 'm';
        return m + 'm';
    }

    function formatTime(seconds) {
        if (seconds <= 0) return 'Done';
        const d = Math.floor(seconds / 86400);
        const h = Math.floor((seconds % 86400) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        if (d > 0) return d + 'd ' + h + 'h';
        if (h > 0) return h + 'h ' + m + 'm';
        if (m > 0) return m + 'm ' + s + 's';
        return s + 's';
    }

    window.WorldResearch = {
        show: showResearchModal
    };
})();
