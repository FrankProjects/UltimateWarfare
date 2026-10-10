/**
 * Federation modal logic.
 */
(function () {
    const federationModal = document.getElementById('federationModal');
    const federationTabs = document.getElementById('federationTabs');
    const federationContainer = document.getElementById('federationContainer');

    let currentTab = 'overview';
    let cachedStatus = null;


    const RANK_NAMES = {
        10: 'General',
        9: 'Staff General',
        8: 'Lieutenant General',
        7: 'Major General',
        6: 'Brigadier General',
        5: 'Colonel',
        4: 'Major',
        3: 'Captain',
        2: 'Sergeant',
        1: 'Recruit'
    };

    function show() {
        bootstrap.Modal.getOrCreateInstance(federationModal).show();
        federationContainer.innerHTML = '<div class="build-loading">Loading federation data...</div>';
        federationTabs.innerHTML = '';
        cachedStatus = null;
        loadStatus();
    }

    federationModal.addEventListener('hidden.bs.modal', function () {
        cachedStatus = null;
    });

    async function loadStatus() {
        try {
            const response = await fetch('/game/api/federation/status');
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message || 'Failed to load federation data') + '</div>';
                return;
            }

            cachedStatus = result;

            if (!result.federationEnabled) {
                federationTabs.innerHTML = '';
                federationContainer.innerHTML = '<div class="build-loading">Federations are disabled in this world!</div>';
                return;
            }

            if (!result.hasFederation) {
                currentTab = 'federations';
            } else {
                currentTab = 'overview';
            }

            renderTabs();
            loadTab();
        } catch (error) {
            console.error('Error loading federation status:', error);
            federationContainer.innerHTML = '<div class="build-loading text-negative">Failed to load federation data</div>';
        }
    }

    function renderTabs() {
        federationTabs.innerHTML = '';
        let tabs = [];

        if (!cachedStatus.hasFederation) {
            tabs = [
                { id: 'federations', name: 'Federations' },
                { id: 'create', name: 'Create' }
            ];
        } else {
            tabs = [
                { id: 'overview', name: 'Overview' },
                { id: 'news', name: 'News' },
                { id: 'bank', name: 'Bank' },
                { id: 'aid', name: 'Send Aid' }
            ];

            if (cachedStatus.hierarchy >= 10) {
                tabs.push({ id: 'settings', name: 'Settings' });
                tabs.push({ id: 'applications', name: 'Applications' });
            }
        }

        tabs.forEach(function (t) {
            const tab = document.createElement('div');
            tab.className = 'build-tab' + (currentTab === t.id ? ' active' : '');
            tab.textContent = t.name;
            if (t.id === 'applications' && cachedStatus.applicationCount > 0) {
                const badge = document.createElement('span');
                badge.className = 'build-tab-badge';
                badge.textContent = cachedStatus.applicationCount;
                tab.appendChild(badge);
            }
            tab.onclick = function () {
                currentTab = t.id;
                renderTabs();
                loadTab();
            };
            federationTabs.appendChild(tab);
        });
    }

    function loadTab() {
        switch (currentTab) {
            case 'federations': renderFederationList(); break;
            case 'create': renderCreateForm(); break;
            case 'overview': renderOverview(); break;
            case 'news': renderNews(); break;
            case 'bank': renderBank(); break;
            case 'aid': renderSendAid(); break;
            case 'settings': renderSettings(); break;
            case 'applications': renderApplications(); break;
        }
    }

    // ===== Federation List =====
    async function renderFederationList() {
        federationContainer.innerHTML = '<div class="build-loading">Loading federations...</div>';

        try {
            const response = await fetch('/game/api/federation/list');
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (result.federations.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No federations in this world yet!</div>';
                return;
            }

            let html = '<table class="uw-table">';
            html += '<tr class="uw-table-head">';
            html += '<th class="text-center">#</th>';
            html += '<th class="text-center">Name</th>';
            html += '<th class="text-center">Founder</th>';
            html += '<th class="text-center">Players</th>';
            html += '<th class="text-center">Regions</th>';
            html += '<th class="text-center">NetWorth</th>';
            if (!result.hasFederation) {
                html += '<th class="text-center">Action</th>';
            }
            html += '</tr>';

            result.federations.forEach(function (fed, index) {
                html += '<tr>';
                html += '<td class="text-center">' + (index + 1) + '</td>';
                html += '<td class="text-center"><a href="#" class="fed-view-link uw-link text-own" data-id="' + fed.id + '">' + escapeHtml(fed.name) + '</a></td>';
                html += '<td class="text-center">' + escapeHtml(fed.founder) + '</td>';
                html += '<td class="text-center">' + fed.players + '</td>';
                html += '<td class="text-center">' + fed.regions.toLocaleString('en-US') + '</td>';
                html += '<td class="text-center">' + fed.netWorth.toLocaleString('en-US') + '</td>';
                if (!result.hasFederation) {
                    html += '<td class="text-center"><button class="market-action-btn fed-apply-btn" data-id="' + fed.id + '">Apply</button></td>';
                }
                html += '</tr>';
            });

            html += '</table>';
            federationContainer.innerHTML = html;

            federationContainer.querySelectorAll('.fed-view-link').forEach(function (link) {
                link.onclick = function (e) {
                    e.preventDefault();
                    renderShowFederation(parseInt(link.getAttribute('data-id'), 10));
                };
            });

            federationContainer.querySelectorAll('.fed-apply-btn').forEach(function (btn) {
                btn.onclick = function () {
                    renderSendApplication(parseInt(btn.getAttribute('data-id'), 10));
                };
            });
        } catch (error) {
            console.error('Error loading federation list:', error);
            federationContainer.innerHTML = '<div class="build-loading text-negative">Failed to load federations</div>';
        }
    }

    // ===== Show Federation =====
    async function renderShowFederation(federationId) {
        federationContainer.innerHTML = '<div class="build-loading">Loading federation...</div>';

        try {
            const response = await fetch('/game/api/federation/show/' + federationId);
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message) + '</div>';
                return;
            }

            let html = '<div class="uw-field">';
            html += '<h3 class="fed-title">' + escapeHtml(result.federation.name) + '</h3>';
            html += '</div>';

            html += '<table class="uw-table">';
            html += '<tr class="uw-table-head">';
            html += '<th class="text-center">Name</th>';
            html += '<th class="text-center">Rank</th>';
            html += '<th class="text-center">Regions</th>';
            html += '<th class="text-center">NetWorth</th>';
            html += '</tr>';

            result.members.forEach(function (member) {
                html += '<tr>';
                html += '<td class="text-center">' + escapeHtml(member.name) + '</td>';
                html += '<td class="text-center">' + (RANK_NAMES[member.hierarchy] || member.hierarchy) + '</td>';
                html += '<td class="text-center">' + member.regions + '</td>';
                html += '<td class="text-center">' + member.netWorth.toLocaleString('en-US') + '</td>';
                html += '</tr>';
            });

            html += '</table>';
            federationContainer.innerHTML = html;
        } catch (error) {
            console.error('Error loading federation:', error);
            federationContainer.innerHTML = '<div class="build-loading text-negative">Failed to load federation</div>';
        }
    }

    // ===== Send Application =====
    function renderSendApplication(federationId) {
        let html = '<div class="uw-form">';
        html += '<h3 class="uw-form-title">Apply to Join Federation</h3>';
        html += '<div class="uw-field">';
        html += '<label class="uw-label">Your application message:</label>';
        html += '<textarea id="fedApplicationText" rows="5" class="form-control"></textarea>';
        html += '</div>';
        html += '<button id="fedSubmitApplicationBtn" class="market-action-btn uw-btn-block">Send Application</button>';
        html += '</div>';

        federationContainer.innerHTML = html;

        document.getElementById('fedSubmitApplicationBtn').onclick = async function () {
            const text = document.getElementById('fedApplicationText').value.trim();
            if (text === '') {
                showNotification('Please write an application message', 'error');
                return;
            }

            const btn = document.getElementById('fedSubmitApplicationBtn');
            btn.disabled = true;
            btn.textContent = 'Sending...';

            try {
                const response = await fetch('/game/api/federation/apply/' + federationId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ application: text })
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    renderFederationList();
                } else {
                    showNotification(result.message || 'Failed to send application', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Send Application';
                }
            } catch (error) {
                console.error('Error sending application:', error);
                showNotification('An error occurred', 'error');
                btn.disabled = false;
                btn.textContent = 'Send Application';
            }
        };
    }

    // ===== Create Federation =====
    function renderCreateForm() {
        let html = '<div class="uw-form">';
        html += '<h3 class="uw-form-title">Create Federation</h3>';
        html += '<div class="uw-field">';
        html += '<label class="uw-label">Federation name:</label>';
        html += '<input type="text" id="fedCreateName" maxlength="25" class="form-control">';
        html += '</div>';
        html += '<button id="fedCreateBtn" class="market-action-btn uw-btn-block">Create Federation</button>';
        html += '</div>';

        federationContainer.innerHTML = html;

        document.getElementById('fedCreateBtn').onclick = async function () {
            const name = document.getElementById('fedCreateName').value.trim();
            if (name === '') {
                showNotification('Please enter a federation name', 'error');
                return;
            }

            const btn = document.getElementById('fedCreateBtn');
            btn.disabled = true;
            btn.textContent = 'Creating...';

            try {
                const response = await fetch('/game/api/federation/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name })
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    cachedStatus = null;
                    loadStatus();
                } else {
                    showNotification(result.message || 'Failed to create federation', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Create Federation';
                }
            } catch (error) {
                console.error('Error creating federation:', error);
                showNotification('An error occurred', 'error');
                btn.disabled = false;
                btn.textContent = 'Create Federation';
            }
        };
    }

    // ===== Overview =====
    function renderOverview() {
        if (!cachedStatus || !cachedStatus.hasFederation) return;

        const fed = cachedStatus.federation;
        let html = '';

        html += '<h3 class="uw-section-title">' + escapeHtml(fed.name) + '</h3>';

        if (fed.leaderMessage) {
            html += '<div class="uw-panel">';
            html += '<strong class="text-light-uw">Leader Message:</strong><br>';
            html += '<div class="text-soft fed-multiline">' + escapeHtml(fed.leaderMessage) + '</div>';
            html += '</div>';
        }

        // Leave button for non-generals
        if (cachedStatus.hierarchy < 10 && cachedStatus.hierarchy >= 1) {
            html += '<div class="uw-field">';
            html += '<button class="market-action-btn market-action-btn-danger" id="fedLeaveBtn">Leave Federation</button>';
            html += '</div>';
        }

        // Members table
        html += '<table class="uw-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Name</th>';
        html += '<th class="text-center">Rank</th>';
        html += '<th class="text-center">Regions</th>';
        html += '<th class="text-center">NetWorth</th>';
        if (cachedStatus.hierarchy >= 10) {
            html += '<th class="text-center">Action</th>';
        }
        html += '</tr>';

        fed.members.forEach(function (member) {
            html += '<tr>';
            html += '<td class="text-center">' + escapeHtml(member.name) + '</td>';
            html += '<td class="text-center">' + (RANK_NAMES[member.hierarchy] || member.hierarchy) + '</td>';
            html += '<td class="text-center">' + member.regions + '</td>';
            html += '<td class="text-center">' + member.netWorth.toLocaleString('en-US') + '</td>';
            if (cachedStatus.hierarchy >= 10 && member.id !== cachedStatus.playerId) {
                html += '<td class="text-center">';
                html += '<button class="market-action-btn market-action-btn-danger fed-kick-btn" data-id="' + member.id + '">Kick</button>';
                html += '</td>';
            } else if (cachedStatus.hierarchy >= 10) {
                html += '<td class="text-center"></td>';
            }
            html += '</tr>';
        });

        html += '</table>';
        federationContainer.innerHTML = html;

        // Bind leave button
        const leaveBtn = document.getElementById('fedLeaveBtn');
        if (leaveBtn) {
            leaveBtn.onclick = function () {
                showConfirm('Are you sure you want to leave the federation?', function () {
                    doPost('/game/api/federation/leave', {}, function () {
                        cachedStatus = null;
                        loadStatus();
                    });
                }, { title: 'Leave Federation', confirmText: 'Leave' });
            };
        }

        // Bind kick buttons
        federationContainer.querySelectorAll('.fed-kick-btn').forEach(function (btn) {
            btn.onclick = function () {
                const playerId = parseInt(btn.getAttribute('data-id'), 10);
                showConfirm('Are you sure you want to kick this player?', function () {
                    doPost('/game/api/federation/kick/' + playerId, {}, function () {
                        cachedStatus = null;
                        loadStatus();
                    });
                }, { title: 'Kick Player', confirmText: 'Kick' });
            };
        });
    }

    // ===== News =====
    async function renderNews() {
        federationContainer.innerHTML = '<div class="build-loading">Loading news...</div>';

        try {
            const response = await fetch('/game/api/federation/news');
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (result.news.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No federation news yet...</div>';
                return;
            }

            let html = '<table class="uw-table">';
            html += '<tr class="uw-table-head">';
            html += '<th class="text-center reports-col-date">Date</th>';
            html += '<th class="text-start">Report</th>';
            html += '</tr>';

            result.news.forEach(function (item) {
                html += '<tr>';
                html += '<td class="text-center">' + new Date(item.timestamp * 1000).toLocaleString() + '</td>';
                html += '<td class="text-start">' + escapeHtml(item.news) + '</td>';
                html += '</tr>';
            });

            html += '</table>';
            federationContainer.innerHTML = html;
        } catch (error) {
            console.error('Error loading federation news:', error);
            federationContainer.innerHTML = '<div class="build-loading text-negative">Failed to load news</div>';
        }
    }

    // ===== Bank =====
    let bankMode = 'deposit';

    function renderBank() {
        if (!cachedStatus || !cachedStatus.hasFederation) return;

        const fed = cachedStatus.federation;
        const playerRes = cachedStatus.playerResources;
        const canWithdraw = cachedStatus.hierarchy >= 5;

        let html = '<div class="fed-bank-tabs">';
        html += '<button class="market-action-btn" id="fedBankDepositTab">Deposit</button>';
        if (canWithdraw) {
            html += '<button class="market-action-btn" id="fedBankWithdrawTab">Withdraw</button>';
        }
        html += '</div>';

        html += '<table class="uw-table fed-bank-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Resource</th>';
        html += '<th class="text-center">You</th>';
        html += '<th class="text-center">Bank</th>';
        html += '<th class="text-center">Amount</th>';
        html += '</tr>';

        const resources = ['cash', 'wood', 'steel', 'food'];
        resources.forEach(function (res) {
            html += '<tr>';
            html += '<td class="text-center">' + res.charAt(0).toUpperCase() + res.slice(1) + '</td>';
            html += '<td class="text-center">' + (res === 'cash' ? '$ ' : '') + playerRes[res].toLocaleString('en-US') + '</td>';
            html += '<td class="text-center">' + (res === 'cash' ? '$ ' : '') + fed.bank[res].toLocaleString('en-US') + '</td>';
            html += '<td class="text-center"><input type="number" id="fedBank_' + res + '" min="0" value="0" class="form-control form-control-sm fed-bank-input"></td>';
            html += '</tr>';
        });

        html += '<tr><td colspan="4" class="text-center fed-bank-submit-cell">';
        html += '<button id="fedBankSubmitBtn" class="market-action-btn fed-bank-submit">' + (bankMode === 'deposit' ? 'Deposit' : 'Withdraw') + '</button>';
        html += '</td></tr>';
        html += '</table>';

        federationContainer.innerHTML = html;

        document.getElementById('fedBankDepositTab').onclick = function () {
            bankMode = 'deposit';
            renderBank();
        };

        const withdrawTab = document.getElementById('fedBankWithdrawTab');
        if (withdrawTab) {
            withdrawTab.onclick = function () {
                bankMode = 'withdraw';
                renderBank();
            };
        }

        document.getElementById('fedBankSubmitBtn').onclick = function () {
            const resData = {};
            resources.forEach(function (res) {
                const val = document.getElementById('fedBank_' + res).value;
                if (val && parseInt(val, 10) > 0) {
                    resData[res] = val;
                }
            });

            const url = bankMode === 'deposit' ? '/game/api/federation/bank/deposit' : '/game/api/federation/bank/withdraw';
            doPost(url, { resources: resData }, function () {
                cachedStatus = null;
                loadStatus();
            });
        };
    }

    // ===== Send Aid =====
    function renderSendAid() {
        if (!cachedStatus || !cachedStatus.hasFederation) return;

        const members = cachedStatus.federation.members;
        const playerRes = cachedStatus.playerResources;

        const recipients = members.filter(function (m) {
            return m.id !== cachedStatus.playerId;
        });

        if (recipients.length === 0) {
            federationContainer.innerHTML = '<div class="build-loading">There are no other members to send aid to</div>';
            return;
        }

        let html = '<div class="fed-aid-recipient">';
        html += '<label for="fedAidPlayer" class="uw-label">Send to</label>';
        html += '<select id="fedAidPlayer" class="form-select form-select-sm">';
        recipients.forEach(function (m) {
            html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '<table class="uw-table fed-bank-table">';
        html += '<tr class="uw-table-head">';
        html += '<th class="text-center">Resource</th>';
        html += '<th class="text-center">You</th>';
        html += '<th class="text-center">Amount</th>';
        html += '</tr>';

        const resources = ['cash', 'wood', 'steel', 'food'];
        resources.forEach(function (res) {
            html += '<tr>';
            html += '<td class="text-center">' + res.charAt(0).toUpperCase() + res.slice(1) + '</td>';
            html += '<td class="text-center">' + (res === 'cash' ? '$ ' : '') + playerRes[res].toLocaleString('en-US') + '</td>';
            html += '<td class="text-center"><input type="number" id="fedAid_' + res + '" min="0" value="0" class="form-control form-control-sm fed-bank-input"></td>';
            html += '</tr>';
        });

        html += '<tr><td colspan="3" class="text-center fed-bank-submit-cell">';
        html += '<button id="fedAidSubmitBtn" class="market-action-btn fed-bank-submit">Send Aid</button>';
        html += '</td></tr>';
        html += '</table>';

        federationContainer.innerHTML = html;

        document.getElementById('fedAidSubmitBtn').onclick = function () {
            const playerId = parseInt(document.getElementById('fedAidPlayer').value, 10);
            const resData = {};
            resources.forEach(function (res) {
                const val = document.getElementById('fedAid_' + res).value;
                if (val && parseInt(val, 10) > 0) {
                    resData[res] = val;
                }
            });

            doPost('/game/api/federation/send-aid', { playerId: playerId, resources: resData }, function () {
                cachedStatus = null;
                loadStatus();
            });
        };
    }

    // ===== Settings (General only) =====
    function renderSettings() {
        if (!cachedStatus || !cachedStatus.hasFederation || cachedStatus.hierarchy < 10) return;

        const fed = cachedStatus.federation;
        const members = fed.members;

        let html = '<div class="uw-form uw-form-wide">';

        // Change Name
        html += '<div class="uw-panel uw-panel-spaced">';
        html += '<h4 class="uw-section-title">Change Federation Name</h4>';
        html += '<div class="uw-row">';
        html += '<input type="text" id="fedNewName" maxlength="25" value="' + escapeHtml(fed.name) + '" class="form-control">';
        html += '<button class="market-action-btn" id="fedChangeNameBtn">Change</button>';
        html += '</div></div>';

        // Update Message
        html += '<div class="uw-panel uw-panel-spaced">';
        html += '<h4 class="uw-section-title">Leader Message</h4>';
        html += '<textarea id="fedNewMessage" rows="4" class="form-control">' + escapeHtml(fed.leaderMessage) + '</textarea>';
        html += '<button class="market-action-btn uw-btn-spaced" id="fedUpdateMessageBtn">Update Message</button>';
        html += '</div>';

        // Change Ranks
        html += '<div class="uw-panel uw-panel-spaced">';
        html += '<h4 class="uw-section-title">Change Player Ranks</h4>';
        html += '<div class="uw-row uw-row-spaced">';
        html += '<select id="fedRolePlayer" class="form-select">';
        members.forEach(function (m) {
            html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>';
        });
        html += '</select>';
        html += '<select id="fedRoleRank" class="form-select">';
        for (let rank = 10; rank >= 1; rank--) {
            html += '<option value="' + rank + '">' + RANK_NAMES[rank] + '</option>';
        }
        html += '</select>';
        html += '<button class="market-action-btn" id="fedChangeRoleBtn">Change</button>';
        html += '</div></div>';

        // Remove Federation
        html += '<div class="uw-panel uw-panel-danger">';
        html += '<h4 class="uw-section-title text-negative">Danger Zone</h4>';
        html += '<button class="market-action-btn market-action-btn-danger" id="fedRemoveBtn">Delete Federation</button>';
        html += '</div>';

        html += '</div>';
        federationContainer.innerHTML = html;

        document.getElementById('fedChangeNameBtn').onclick = function () {
            const name = document.getElementById('fedNewName').value.trim();
            doPost('/game/api/federation/change-name', { name: name }, function () {
                cachedStatus = null;
                loadStatus();
            });
        };

        document.getElementById('fedUpdateMessageBtn').onclick = function () {
            const message = document.getElementById('fedNewMessage').value;
            doPost('/game/api/federation/update-message', { message: message }, function () {
                cachedStatus = null;
                loadStatus();
            });
        };

        document.getElementById('fedChangeRoleBtn').onclick = function () {
            const playerId = parseInt(document.getElementById('fedRolePlayer').value, 10);
            const role = parseInt(document.getElementById('fedRoleRank').value, 10);
            doPost('/game/api/federation/change-role', { playerId: playerId, role: role }, function () {
                cachedStatus = null;
                loadStatus();
            });
        };

        document.getElementById('fedRemoveBtn').onclick = function () {
            showConfirm('Are you sure you want to DELETE this federation? This cannot be undone!', function () {
                doPost('/game/api/federation/remove', {}, function () {
                    cachedStatus = null;
                    loadStatus();
                });
            }, { title: 'Delete Federation', confirmText: 'Delete' });
        };
    }

    // ===== Applications (General only) =====
    async function renderApplications() {
        federationContainer.innerHTML = '<div class="build-loading">Loading applications...</div>';

        try {
            const response = await fetch('/game/api/federation/applications');
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading text-negative">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (cachedStatus && cachedStatus.applicationCount !== result.applications.length) {
                cachedStatus.applicationCount = result.applications.length;
                renderTabs();
            }

            if (result.applications.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No applications at this time</div>';
                return;
            }

            let html = '';
            result.applications.forEach(function (app) {
                html += '<div class="uw-panel">';
                html += '<p><strong class="text-light-uw">Player:</strong> ' + escapeHtml(app.playerName) + '</p>';
                html += '<p><strong class="text-light-uw">Application:</strong></p>';
                html += '<p class="fed-multiline">' + escapeHtml(app.application) + '</p>';
                html += '<div class="fed-application-actions">';
                html += '<button class="market-action-btn fed-accept-btn" data-id="' + app.id + '">Accept</button>';
                html += '<button class="market-action-btn market-action-btn-danger fed-reject-btn" data-id="' + app.id + '">Reject</button>';
                html += '</div></div>';
            });

            federationContainer.innerHTML = html;

            federationContainer.querySelectorAll('.fed-accept-btn').forEach(function (btn) {
                btn.onclick = function () {
                    const appId = parseInt(btn.getAttribute('data-id'), 10);
                    doPost('/game/api/federation/application/accept/' + appId, {}, function () {
                        cachedStatus = null;
                        loadStatus();
                    });
                };
            });

            federationContainer.querySelectorAll('.fed-reject-btn').forEach(function (btn) {
                btn.onclick = function () {
                    const appId = parseInt(btn.getAttribute('data-id'), 10);
                    doPost('/game/api/federation/application/reject/' + appId, {}, function () {
                        renderApplications();
                    });
                };
            });
        } catch (error) {
            console.error('Error loading applications:', error);
            federationContainer.innerHTML = '<div class="build-loading text-negative">Failed to load applications</div>';
        }
    }

    // ===== Helper: POST request =====
    async function doPost(url, data, onSuccess) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                if (onSuccess) onSuccess();
            } else {
                showNotification(result.message || 'Action failed', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.WorldFederation = {
        show: show
    };
})();
