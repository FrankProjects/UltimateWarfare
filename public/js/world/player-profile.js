/**
 * Player profile modal logic.
 */
(function () {
    const playerProfileModal = document.getElementById('playerProfileModal');
    const playerProfileContainer = document.getElementById('playerProfileContainer');

    function show(playerName) {
        bootstrap.Modal.getOrCreateInstance(playerProfileModal).show();
        loadProfile(playerName);
    }

    async function loadProfile(playerName) {
        playerProfileContainer.innerHTML = '<div class="build-loading">Loading profile...</div>';

        try {
            const response = await fetch('/game/api/player/profile/' + encodeURIComponent(playerName));
            const result = await response.json();

            if (result.success) {
                renderProfile(result.profile);
            } else {
                playerProfileContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message || 'Failed to load profile') + '</div>';
            }
        } catch (error) {
            console.error('Error loading player profile:', error);
            playerProfileContainer.innerHTML = '<div class="build-loading text-negative">Failed to load profile</div>';
        }
    }

    function renderProfile(profile) {
        let html = '<table class="uw-table">';
        html += '<tr>';
        html += '<td><strong>Player</strong></td>';
        html += '<td>' + escapeHtml(profile.name) + '</td>';
        html += '</tr>';
        html += '<tr>';
        html += '<td><strong>Joined</strong></td>';
        html += '<td>' + escapeHtml(profile.joinDate) + '</td>';
        html += '</tr>';
        html += '<tr>';
        html += '<td><strong>Regions</strong></td>';
        html += '<td>' + profile.regions + '</td>';
        html += '</tr>';
        html += '<tr>';
        html += '<td><strong>Net Worth</strong></td>';
        html += '<td>' + profile.netWorth + '</td>';
        html += '</tr>';

        if (profile.federation) {
            html += '<tr>';
            html += '<td><strong>Federation</strong></td>';
            html += '<td>' + escapeHtml(profile.federation) + '</td>';
            html += '</tr>';
        }

        html += '</table>';
        playerProfileContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldPlayerProfile = {
        show: show
    };
})();
