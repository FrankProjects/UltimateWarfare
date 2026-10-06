/**
 * Profile modal logic (account info, password change).
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
            { id: 'danger', name: 'Danger Zone' }
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
                profileContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load profile</div>';
            }
        } catch (error) {
            profileContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Error loading profile</div>';
        }
    }

    function renderCurrentTab() {
        if (!profileData) return;

        if (currentTab === 'info') {
            renderInfoTab();
        } else if (currentTab === 'password') {
            renderPasswordTab();
        } else if (currentTab === 'danger') {
            renderDangerTab();
        }
    }

    function renderInfoTab() {
        const status = profileData.banned ? 'Banned' : 'Active';
        const statusColor = profileData.banned ? '#f44336' : '#4caf50';

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr><td style="padding: 8px; color: #aaa;">Username</td><td style="padding: 8px;">' + escapeHtml(profileData.username) + '</td></tr>';
        html += '<tr><td style="padding: 8px; color: #aaa;">Email</td><td style="padding: 8px;">' + escapeHtml(profileData.email) + '</td></tr>';
        html += '<tr><td style="padding: 8px; color: #aaa;">Signup Date</td><td style="padding: 8px;">' + escapeHtml(profileData.signedUpAt) + '</td></tr>';
        html += '<tr><td style="padding: 8px; color: #aaa;">Account Type</td><td style="padding: 8px;">' + escapeHtml(profileData.accountType) + '</td></tr>';
        html += '<tr><td style="padding: 8px; color: #aaa;">Status</td><td style="padding: 8px; color: ' + statusColor + ';">' + status + '</td></tr>';
        html += '</table>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
    }

    function renderPasswordTab() {
        let html = '<div style="max-width: 400px; margin: 0 auto;">';
        html += '<div style="margin-bottom: 12px;"><label style="display: block; color: #aaa; margin-bottom: 4px;">Current Password</label>';
        html += '<input type="password" id="profileOldPassword" style="width: 100%; padding: 8px; background: #1a1a2e; border: 1px solid #444; color: #eee; border-radius: 4px;"></div>';
        html += '<div style="margin-bottom: 12px;"><label style="display: block; color: #aaa; margin-bottom: 4px;">New Password</label>';
        html += '<input type="password" id="profileNewPassword" style="width: 100%; padding: 8px; background: #1a1a2e; border: 1px solid #444; color: #eee; border-radius: 4px;"></div>';
        html += '<div style="margin-bottom: 12px;"><label style="display: block; color: #aaa; margin-bottom: 4px;">Confirm New Password</label>';
        html += '<input type="password" id="profileNewPasswordRepeat" style="width: 100%; padding: 8px; background: #1a1a2e; border: 1px solid #444; color: #eee; border-radius: 4px;"></div>';
        html += '<div id="profilePasswordMessage" style="margin-top: 8px;"></div>';
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-primary" id="savePasswordBtn">Change Password</button>';
        document.getElementById('savePasswordBtn').onclick = changePassword;
    }

    async function changePassword() {
        const oldPassword = document.getElementById('profileOldPassword').value;
        const newPassword = document.getElementById('profileNewPassword').value;
        const newPasswordRepeat = document.getElementById('profileNewPasswordRepeat').value;
        const messageDiv = document.getElementById('profilePasswordMessage');

        if (!oldPassword || !newPassword || !newPasswordRepeat) {
            messageDiv.innerHTML = '<span style="color: #f44336;">All fields are required.</span>';
            return;
        }

        if (newPassword !== newPasswordRepeat) {
            messageDiv.innerHTML = '<span style="color: #f44336;">New passwords do not match.</span>';
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
                messageDiv.innerHTML = '<span style="color: #4caf50;">' + escapeHtml(result.message) + '</span>';
            } else {
                messageDiv.innerHTML = '<span style="color: #f44336;">' + escapeHtml(result.message) + '</span>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<span style="color: #f44336;">An error occurred.</span>';
        }
    }

    function renderDangerTab() {
        let html = '<div style="max-width: 400px; margin: 0 auto;">';
        html += '<h3 style="color: #f44336; margin-top: 0;">Danger Zone</h3>';

        if (!profileData.canSurrender) {
            html += '<p style="color: #aaa;">You cannot surrender for the first 48 hours after joining a world.</p>';
            html += '</div>';
            profileContainer.innerHTML = html;
            profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            return;
        }

        if (profileData.isFederationFounder) {
            html += '<p style="color: #aaa;">You cannot surrender while you are a Federation founder. Please disband your Federation first.</p>';
            html += '</div>';
            profileContainer.innerHTML = html;
            profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            return;
        }

        html += '<p style="color: #aaa;">Surrendering will permanently delete your empire from this world. This action cannot be undone.</p>';
        html += '<div style="margin-bottom: 12px;"><label style="display: block; color: #aaa; margin-bottom: 4px;">Confirm Password</label>';
        html += '<input type="password" id="surrenderPassword" style="width: 100%; padding: 8px; background: #1a1a2e; border: 1px solid #444; color: #eee; border-radius: 4px;"></div>';
        html += '<div id="surrenderMessage" style="margin-top: 8px;"></div>';
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-danger" id="surrenderBtn">Surrender</button>';
        document.getElementById('surrenderBtn').onclick = surrender;
    }

    async function surrender() {
        const password = document.getElementById('surrenderPassword').value;
        const messageDiv = document.getElementById('surrenderMessage');

        if (!password) {
            messageDiv.innerHTML = '<span style="color: #f44336;">Password is required.</span>';
            return;
        }

        if (!confirm('Are you sure you want to surrender? Your empire will be permanently deleted. This cannot be undone!')) {
            return;
        }

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
                messageDiv.innerHTML = '<span style="color: #f44336;">' + escapeHtml(result.message) + '</span>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<span style="color: #f44336;">An error occurred.</span>';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
})();
