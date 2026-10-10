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
                worldsContainer.innerHTML = '<div class="build-loading text-negative">Failed to load worlds</div>';
            }
        } catch (error) {
            worldsContainer.innerHTML = '<div class="build-loading text-negative">Error loading worlds</div>';
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
            worldsContainer.innerHTML = '<div class="uw-empty">You are not playing in any worlds yet.</div>';
            return;
        }

        let html = '<table class="uw-table worlds-table">';
        html += '<tr class="uw-table-head"><th>World</th><th>Player Name</th><th class="text-end"></th></tr>';

        worlds.forEach(function (w) {
            html += '<tr>';
            html += '<td>' + escapeHtml(w.worldName) + '</td>';
            html += '<td>' + escapeHtml(w.playerName) + '</td>';
            html += '<td class="text-end"><a href="/game/login/player/' + w.playerId + '" class="world-action-btn">Play</a></td>';
            html += '</tr>';
        });

        html += '</table>';
        worldsContainer.innerHTML = html;
    }

    function renderJoinWorld() {
        const worlds = worldsData.joinableWorlds;

        if (worlds.length === 0) {
            worldsContainer.innerHTML = '<div class="uw-empty">No worlds available to join at this time.</div>';
            return;
        }

        let html = '<table class="uw-table worlds-table">';
        html += '<tr class="uw-table-head"><th>World</th><th>Players</th><th>Description</th><th class="text-end"></th></tr>';

        worlds.forEach(function (w) {
            html += '<tr>';
            html += '<td>' + escapeHtml(w.worldName) + '</td>';
            html += '<td>' + w.currentPlayers + ' / ' + w.maxPlayers + '</td>';
            html += '<td class="uw-cell-meta">' + escapeHtml(w.description) + '</td>';
            html += '<td class="text-end"><a href="/game/select-name/' + w.worldId + '" class="world-action-btn world-join-btn">Join</a></td>';
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
