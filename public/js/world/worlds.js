/**
 * Worlds modal logic (my worlds, join world).
 * Depends on: notifications.js
 */
(function () {
    const worldsModal = document.getElementById('worldsModal');
    const worldsTabs = document.getElementById('worldsTabs');
    const worldsContainer = document.getElementById('worldsContainer');

    let currentTab = 'myWorlds';

    document.getElementById('worldsBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        showWorldsModal();
    });

    function showWorldsModal() {
        currentTab = 'myWorlds';
        bootstrap.Modal.getOrCreateInstance(worldsModal).show();
        loadWorlds();
    }

    function renderTabs() {
        worldsTabs.innerHTML = '';
        const tabs = [
            { id: 'myWorlds', name: 'My Worlds' },
            { id: 'joinWorld', name: 'Join World' }
        ];

        tabs.forEach(function (tab) {
            const el = document.createElement('div');
            el.className = 'build-tab' + (currentTab === tab.id ? ' active' : '');
            el.textContent = tab.name;
            el.onclick = function () {
                currentTab = tab.id;
                renderTabs();
                renderCurrentTab();
            };
            worldsTabs.appendChild(el);
        });
    }

    let worldsData = null;

    async function loadWorlds() {
        worldsContainer.innerHTML = '<div class="build-loading">Loading worlds...</div>';

        try {
            const response = await fetch('/game/api/worlds');
            const result = await response.json();

            if (result.success) {
                worldsData = result;
                renderTabs();
                renderCurrentTab();
            } else {
                worldsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load worlds</div>';
            }
        } catch (error) {
            worldsContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Error loading worlds</div>';
        }
    }

    function renderCurrentTab() {
        if (!worldsData) return;

        if (currentTab === 'myWorlds') {
            renderMyWorlds();
        } else if (currentTab === 'joinWorld') {
            renderJoinWorld();
        }
    }

    function renderMyWorlds() {
        const worlds = worldsData.myWorlds;

        if (worlds.length === 0) {
            worldsContainer.innerHTML = '<div style="text-align: center; color: #aaa; padding: 20px;">You are not playing in any worlds yet.</div>';
            return;
        }

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 1px solid #333;"><th style="padding: 8px; text-align: left; color: #aaa;">World</th><th style="padding: 8px; text-align: left; color: #aaa;">Player Name</th><th style="padding: 8px; text-align: right;"></th></tr>';

        worlds.forEach(function (w) {
            html += '<tr style="border-bottom: 1px solid #222;">';
            html += '<td style="padding: 8px;">' + escapeHtml(w.worldName) + '</td>';
            html += '<td style="padding: 8px;">' + escapeHtml(w.playerName) + '</td>';
            html += '<td style="padding: 8px; text-align: right;"><a href="/game/login/player/' + w.playerId + '" style="background: #2196F3; color: #fff; padding: 5px 15px; border-radius: 4px; text-decoration: none; font-size: 13px;">Play</a></td>';
            html += '</tr>';
        });

        html += '</table>';
        worldsContainer.innerHTML = html;
    }

    function renderJoinWorld() {
        const worlds = worldsData.joinableWorlds;

        if (worlds.length === 0) {
            worldsContainer.innerHTML = '<div style="text-align: center; color: #aaa; padding: 20px;">No worlds available to join at this time.</div>';
            return;
        }

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 1px solid #333;"><th style="padding: 8px; text-align: left; color: #aaa;">World</th><th style="padding: 8px; text-align: left; color: #aaa;">Players</th><th style="padding: 8px; text-align: left; color: #aaa;">Description</th><th style="padding: 8px; text-align: right;"></th></tr>';

        worlds.forEach(function (w) {
            html += '<tr style="border-bottom: 1px solid #222;">';
            html += '<td style="padding: 8px;">' + escapeHtml(w.worldName) + '</td>';
            html += '<td style="padding: 8px;">' + w.currentPlayers + ' / ' + w.maxPlayers + '</td>';
            html += '<td style="padding: 8px; color: #aaa; font-size: 12px;">' + escapeHtml(w.description) + '</td>';
            html += '<td style="padding: 8px; text-align: right;"><a href="/game/select-name/' + w.worldId + '" style="background: #4caf50; color: #fff; padding: 5px 15px; border-radius: 4px; text-decoration: none; font-size: 13px;">Join</a></td>';
            html += '</tr>';
        });

        html += '</table>';
        worldsContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
})();
