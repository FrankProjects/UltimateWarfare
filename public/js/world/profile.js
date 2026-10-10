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
        } else if (currentTab === 'danger') {
            renderDangerTab();
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
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
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
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-primary" id="savePasswordBtn">Change Password</button>';
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

    function renderDangerTab() {
        let html = '<div class="profile-form">';
        html += '<h3 class="text-negative">Danger Zone</h3>';

        if (!profileData.canSurrender) {
            html += '<p class="text-muted-uw">You cannot surrender for the first 48 hours after joining a world.</p>';
            html += '</div>';
            profileContainer.innerHTML = html;
            profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            return;
        }

        if (profileData.isFederationFounder) {
            html += '<p class="text-muted-uw">You cannot surrender while you are a Federation founder. Please disband your Federation first.</p>';
            html += '</div>';
            profileContainer.innerHTML = html;
            profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
            return;
        }

        html += '<p class="text-muted-uw">Surrendering will permanently delete your empire from this world. This action cannot be undone.</p>';
        html += '<div class="uw-field"><label class="uw-label">Confirm Password</label>';
        html += '<input type="password" id="surrenderPassword" class="form-control"></div>';
        html += '<div id="surrenderMessage" class="profile-message"></div>';
        html += '</div>';

        profileContainer.innerHTML = html;
        profileFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-danger" id="surrenderBtn">Surrender</button>';
        document.getElementById('surrenderBtn').onclick = surrender;
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
