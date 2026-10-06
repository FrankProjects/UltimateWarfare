/**
 * Construction overview modal logic.
 */
(function () {
    const constructionModal = document.getElementById('constructionModal');
    const constructionTabs = document.getElementById('constructionTabs');
    const constructionContainer = document.getElementById('constructionContainer');

    let currentCategory = 'all';
    let allConstructions = [];
    let timerInterval = null;

    constructionModal.addEventListener('hidden.bs.modal', function () {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    });

    function show() {
        bootstrap.Modal.getOrCreateInstance(constructionModal).show();
        currentCategory = 'all';
        loadConstruction();
    }

    async function loadConstruction() {
        constructionContainer.innerHTML = '<div class="build-loading">Loading construction data...</div>';

        try {
            const response = await fetch('/game/api/construction/overview');
            const result = await response.json();

            if (result.success) {
                allConstructions = result.constructions;
                renderTabs();
                renderConstructions();
                startTimers();
            } else {
                constructionContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load construction data</div>';
            }
        } catch (error) {
            console.error('Error loading constructions:', error);
            constructionContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load construction data</div>';
        }
    }

    function renderTabs() {
        constructionTabs.innerHTML = '';

        const categories = [{ id: 'all', name: 'All' }];
        const seen = {};
        allConstructions.forEach(function (c) {
            if (!seen[c.categoryId]) {
                seen[c.categoryId] = true;
                categories.push({ id: c.categoryId, name: c.categoryName });
            }
        });

        categories.forEach(function (cat) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (String(currentCategory) === String(cat.id) ? ' active' : '');
            tab.textContent = cat.name;
            tab.onclick = function () {
                currentCategory = cat.id;
                renderTabs();
                renderConstructions();
            };
            constructionTabs.appendChild(tab);
        });
    }

    function renderConstructions() {
        const filtered = currentCategory === 'all'
            ? allConstructions
            : allConstructions.filter(function (c) { return c.categoryId === currentCategory; });

        if (filtered.length === 0) {
            constructionContainer.innerHTML = '<div class="build-loading">No units under construction</div>';
            return;
        }

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: left;">Unit</th>';
        html += '<th style="padding: 8px; text-align: center;">Count</th>';
        html += '<th style="padding: 8px; text-align: center;">Region</th>';
        html += '<th style="padding: 8px; text-align: center;">Time Left</th>';
        html += '<th style="padding: 8px; text-align: center;">Action</th>';
        html += '</tr>';

        filtered.forEach(function (c) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px;">' + escapeHtml(c.unitName) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + c.number + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + c.regionX + ', ' + c.regionY + '</td>';
            html += '<td style="padding: 8px; text-align: center;"><span class="construction-timer" data-timeleft="' + c.timeLeft + '">' + formatTime(c.timeLeft) + '</span></td>';
            html += '<td style="padding: 8px; text-align: center;"><button class="construction-cancel-btn" data-id="' + c.id + '">Cancel</button></td>';
            html += '</tr>';
        });

        html += '</table>';
        constructionContainer.innerHTML = html;

        constructionContainer.querySelectorAll('.construction-cancel-btn').forEach(function (btn) {
            btn.onclick = function () {
                cancelConstruction(parseInt(btn.getAttribute('data-id'), 10));
            };
        });
    }

    function startTimers() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        timerInterval = setInterval(function () {
            const timers = constructionContainer.querySelectorAll('.construction-timer');
            timers.forEach(function (el) {
                let timeLeft = parseInt(el.getAttribute('data-timeleft'), 10) - 1;
                if (timeLeft < 0) { timeLeft = 0; }
                el.setAttribute('data-timeleft', String(timeLeft));
                el.textContent = formatTime(timeLeft);
            });
        }, 1000);
    }

    function formatTime(seconds) {
        if (seconds <= 0) { return 'Done'; }
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        const parts = [];
        if (h > 0) { parts.push(h + 'h'); }
        if (m > 0) { parts.push(m + 'm'); }
        parts.push(s + 's');
        return parts.join(' ');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    async function cancelConstruction(constructionId) {
        try {
            const response = await fetch('/game/api/construction/cancel/' + constructionId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                loadConstruction();
                // Update the build data cache and the worldmap unit display
                WorldBuild.applyBuildData(result.buildData);
            } else {
                showNotification(result.message || 'Failed to cancel construction', 'error');
            }
        } catch (error) {
            console.error('Error cancelling construction:', error);
            showNotification('An error occurred while cancelling construction', 'error');
        }
    }

    window.WorldConstruction = {
        show: show
    };
})();
