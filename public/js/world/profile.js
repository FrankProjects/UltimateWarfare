/**
 * Profile modal logic (account info, password change, personal data, surrender).
 * Depends on: notifications.js
 */
(function () {
    const profileModal = document.getElementById('profileModal');
    const profileTabs = document.getElementById('profileTabs');
    const profileContainer = document.getElementById('profileContainer');
    const profileFooter = document.getElementById('profileFooter');

    let currentTab = 'info';
    let profileData = null;

    document.getElementById('profileBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        showProfileModal();
    });

    function showProfileModal() {
        currentTab = 'info';
        bootstrap.Modal.getOrCreateInstance(profileModal).show();
        loadProfile();
    }

    function renderTabs() {
        profileTabs.innerHTML = '';
        const tabs = [
            { id: 'info', name: 'Account Info' },
            { id: 'password', name: 'Password' },
            { id: 'data', name: 'Your Data' },
            { id: 'surrender', name: 'Surrender' }
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
            profileTabs.appendChild(el);
        });
    }

    async function loadProfile() {
        profileContainer.innerHTML = '<div class="build-loading">Loading profile...</div>';

        try {
            const response = await fetch('/game/api/profile');
            const result = await response.json();

            if (result.success) {
                profileData = result.data;
                renderTabs();
                renderCurrentTab();
            } else {
                profileContainer.innerHTML = '<div class="build-loading text-negative">Failed to load profile</div>';
            }
        } catch (error) {
            profileContainer.innerHTML = '<div class="build-loading text-negative">Error loading profile</div>';
        }
    }

    function renderCurrentTab() {
        if (!profileData) return;

        if (currentTab === 'info') {
            renderInfoTab();
        } else if (currentTab === 'password') {
            renderPasswordTab();
        } else if (currentTab === 'data') {
            renderDataTab();
        } else if (currentTab === 'surrender') {
            renderSurrenderTab();
        }
    }

    function renderInfoTab() {
        const status = profileData.banned ? 'Banned' : 'Active';
        const statusClass = profileData.banned ? 'text-negative' : 'text-positive';

        let html = '<table class="uw-table uw-table-plain">';
        html += '<tr><td class="uw-table-label">Username</td><td>' + escapeHtml(profileData.username) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Email</td><td>' + escapeHtml(profileData.email) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Signup Date</td><td>' + escapeHtml(profileData.signedUpAt) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Account Type</td><td>' + escapeHtml(profileData.accountType) + '</td></tr>';
        html += '<tr><td class="uw-table-label">Status</td><td class="' + statusClass + '">' + status + '</td></tr>';
        html += '</table>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '';
    }

    function renderPasswordTab() {
        let html = '<div class="profile-form">';
        html += '<div class="uw-field"><label class="uw-label">Current Password</label>';
        html += '<input type="password" id="profileOldPassword" class="form-control"></div>';
        html += '<div class="uw-field"><label class="uw-label">New Password</label>';
        html += '<input type="password" id="profileNewPassword" class="form-control"></div>';
        html += '<div class="uw-field"><label class="uw-label">Confirm New Password</label>';
        html += '<input type="password" id="profileNewPasswordRepeat" class="form-control"></div>';
        html += '<div id="profilePasswordMessage" class="profile-message"></div>';
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '<button type="button" class="btn btn-primary" id="savePasswordBtn">Change Password</button>';
        document.getElementById('savePasswordBtn').onclick = changePassword;
    }

    async function changePassword() {
        const oldPassword = document.getElementById('profileOldPassword').value;
        const newPassword = document.getElementById('profileNewPassword').value;
        const newPasswordRepeat = document.getElementById('profileNewPasswordRepeat').value;
        const messageDiv = document.getElementById('profilePasswordMessage');

        if (!oldPassword || !newPassword || !newPasswordRepeat) {
            messageDiv.innerHTML = '<span class="text-negative">All fields are required.</span>';
            return;
        }

        if (newPassword !== newPasswordRepeat) {
            messageDiv.innerHTML = '<span class="text-negative">New passwords do not match.</span>';
            return;
        }

        try {
            const response = await fetch('/game/api/profile/change-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    oldPassword: oldPassword,
                    newPassword: newPassword,
                    newPasswordRepeat: newPasswordRepeat
                })
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                document.getElementById('profileOldPassword').value = '';
                document.getElementById('profileNewPassword').value = '';
                document.getElementById('profileNewPasswordRepeat').value = '';
                messageDiv.innerHTML = '<span class="text-positive">' + escapeHtml(result.message) + '</span>';
            } else {
                messageDiv.innerHTML = '<span class="text-negative">' + escapeHtml(result.message) + '</span>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<span class="text-negative">An error occurred.</span>';
        }
    }

    function renderDataTab() {
        let html = '<div class="uw-panel">';
        html += '<h4 class="uw-section-title">Download my data</h4>';
        html += '<p class="text-muted-uw">Download all personal data we store about you (account details and IP log) as a JSON file.</p>';
        html += '<a class="uw-btn" href="/game/api/profile/export-data" download>Download my data</a>';
        html += '</div>';

        html += '<div class="uw-panel uw-panel-danger">';
        html += '<h4 class="uw-section-title text-negative">Delete account</h4>';
        html += '<p class="text-muted-uw">Permanently delete your account, your empires in every world and your personal data. '
            + 'Your forum posts are kept as "[deleted user]". This action cannot be undone.</p>';
        html += '<a class="uw-btn uw-btn-danger" href="/game/account/delete">Delete account</a>';
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '';
    }

    function renderSurrenderTab() {
        let html = '<div class="uw-panel uw-panel-danger">';
        html += '<h4 class="uw-section-title text-negative">Surrender empire</h4>';
        html += '<p class="text-muted-uw">Surrendering gives up your empire in this world: all your regions, units and resources are removed. '
            + 'Your account stays, so you can join this or another world again with a new empire. This action cannot be undone.</p>';

        if (!profileData.canSurrender) {
            html += '<p class="text-negative">You cannot surrender for the first 48 hours after joining a world.</p>';
        } else if (profileData.isFederationGeneral) {
            html += '<p class="text-negative">You cannot surrender while you are a Federation General. Please hand over or disband your Federation first.</p>';
        } else {
            html += '<div class="profile-form profile-form-left">';
            html += '<div class="uw-field"><label class="uw-label" for="surrenderPassword">Confirm Password</label>';
            html += '<input type="password" id="surrenderPassword" class="form-control"></div>';
            html += '<div id="surrenderMessage" class="profile-message"></div>';
            html += '<button type="button" class="uw-btn uw-btn-danger uw-btn-spaced" id="surrenderBtn">Surrender</button>';
            html += '</div>';
        }
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '';

        const surrenderBtn = document.getElementById('surrenderBtn');
        if (surrenderBtn) surrenderBtn.onclick = surrender;
    }

    function surrender() {
        const password = document.getElementById('surrenderPassword').value;
        const messageDiv = document.getElementById('surrenderMessage');

        if (!password) {
            messageDiv.innerHTML = '<span class="text-negative">Password is required.</span>';
            return;
        }

        showConfirm('Are you sure you want to surrender? Your empire will be permanently deleted. This cannot be undone!', function () {
            submitSurrender(password, messageDiv);
        }, { title: 'Surrender', confirmText: 'Surrender' });
    }

    async function submitSurrender(password, messageDiv) {
        try {
            const response = await fetch('/game/api/profile/surrender', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: password })
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                window.location.href = result.redirect;
            } else {
                messageDiv.innerHTML = '<span class="text-negative">' + escapeHtml(result.message) + '</span>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<span class="text-negative">An error occurred.</span>';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
})();
