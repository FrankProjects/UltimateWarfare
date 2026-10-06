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
                federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message || 'Failed to load federation data') + '</div>';
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
            federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load federation data</div>';
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
                federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (result.federations.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No federations in this world yet!</div>';
                return;
            }

            let html = '<table style="width: 100%; border-collapse: collapse;">';
            html += '<tr style="border-bottom: 2px solid #8B7355;">';
            html += '<th style="padding: 8px; text-align: center;">#</th>';
            html += '<th style="padding: 8px; text-align: center;">Name</th>';
            html += '<th style="padding: 8px; text-align: center;">Founder</th>';
            html += '<th style="padding: 8px; text-align: center;">Players</th>';
            html += '<th style="padding: 8px; text-align: center;">Regions</th>';
            html += '<th style="padding: 8px; text-align: center;">NetWorth</th>';
            if (!result.hasFederation) {
                html += '<th style="padding: 8px; text-align: center;">Action</th>';
            }
            html += '</tr>';

            result.federations.forEach(function (fed, index) {
                html += '<tr style="border-bottom: 1px solid #555;">';
                html += '<td style="padding: 8px; text-align: center;">' + (index + 1) + '</td>';
                html += '<td style="padding: 8px; text-align: center;"><a href="#" class="fed-view-link" data-id="' + fed.id + '" style="color: #6bafff; text-decoration: underline; cursor: pointer;">' + escapeHtml(fed.name) + '</a></td>';
                html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(fed.founder) + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + fed.players + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + fed.regions.toLocaleString('en-US') + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + fed.netWorth.toLocaleString('en-US') + '</td>';
                if (!result.hasFederation) {
                    html += '<td style="padding: 8px; text-align: center;"><button class="market-action-btn fed-apply-btn" data-id="' + fed.id + '">Apply</button></td>';
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
            federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load federations</div>';
        }
    }

    // ===== Show Federation =====
    async function renderShowFederation(federationId) {
        federationContainer.innerHTML = '<div class="build-loading">Loading federation...</div>';

        try {
            const response = await fetch('/game/api/federation/show/' + federationId);
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message) + '</div>';
                return;
            }

            let html = '<div style="margin-bottom: 15px;">';
            html += '<button class="market-action-btn" id="fedBackToListBtn" style="margin-bottom: 10px;">Back to list</button>';
            html += '<h3 style="color: #f3e6c1; margin: 0;">' + escapeHtml(result.federation.name) + '</h3>';
            html += '</div>';

            html += '<table style="width: 100%; border-collapse: collapse;">';
            html += '<tr style="border-bottom: 2px solid #8B7355;">';
            html += '<th style="padding: 8px; text-align: center;">Name</th>';
            html += '<th style="padding: 8px; text-align: center;">Rank</th>';
            html += '<th style="padding: 8px; text-align: center;">Regions</th>';
            html += '<th style="padding: 8px; text-align: center;">NetWorth</th>';
            html += '</tr>';

            result.members.forEach(function (member) {
                html += '<tr style="border-bottom: 1px solid #555;">';
                html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(member.name) + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + (RANK_NAMES[member.hierarchy] || member.hierarchy) + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + member.regions + '</td>';
                html += '<td style="padding: 8px; text-align: center;">' + member.netWorth.toLocaleString('en-US') + '</td>';
                html += '</tr>';
            });

            html += '</table>';
            federationContainer.innerHTML = html;

            document.getElementById('fedBackToListBtn').onclick = function () {
                renderFederationList();
            };
        } catch (error) {
            console.error('Error loading federation:', error);
            federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load federation</div>';
        }
    }

    // ===== Send Application =====
    function renderSendApplication(federationId) {
        let html = '<div style="max-width: 400px; margin: 0 auto; padding: 10px;">';
        html += '<button class="market-action-btn" id="fedBackToListBtn2" style="margin-bottom: 15px;">Back to list</button>';
        html += '<h3 style="color: #f3e6c1; margin: 0 0 15px 0;">Apply to Join Federation</h3>';
        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">Your application message:</label>';
        html += '<textarea id="fedApplicationText" rows="5" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>';
        html += '</div>';
        html += '<button id="fedSubmitApplicationBtn" class="market-action-btn" style="width: 100%; padding: 10px;">Send Application</button>';
        html += '</div>';

        federationContainer.innerHTML = html;

        document.getElementById('fedBackToListBtn2').onclick = function () {
            renderFederationList();
        };

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
        let html = '<div style="max-width: 400px; margin: 0 auto; padding: 10px;">';
        html += '<h3 style="color: #f3e6c1; margin: 0 0 15px 0;">Create Federation</h3>';
        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">Federation name:</label>';
        html += '<input type="text" id="fedCreateName" maxlength="25" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; box-sizing: border-box;">';
        html += '</div>';
        html += '<button id="fedCreateBtn" class="market-action-btn" style="width: 100%; padding: 10px;">Create Federation</button>';
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

        html += '<h3 style="color: #f3e6c1; margin: 0 0 10px 0;">' + escapeHtml(fed.name) + '</h3>';

        if (fed.leaderMessage) {
            html += '<div style="margin-bottom: 15px; padding: 10px; background: #2a2a2a; border: 1px solid #8B7355; border-radius: 4px;">';
            html += '<strong style="color: #f3e6c1;">Leader Message:</strong><br>';
            html += '<span style="color: #ccc;">' + escapeHtml(fed.leaderMessage) + '</span>';
            html += '</div>';
        }

        // Leave button for non-generals
        if (cachedStatus.hierarchy < 10 && cachedStatus.hierarchy >= 1) {
            html += '<div style="margin-bottom: 15px;">';
            html += '<button class="market-action-btn" id="fedLeaveBtn" style="background-color: #f44336;">Leave Federation</button>';
            html += '</div>';
        }

        // Members table
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center;">Name</th>';
        html += '<th style="padding: 8px; text-align: center;">Rank</th>';
        html += '<th style="padding: 8px; text-align: center;">Regions</th>';
        html += '<th style="padding: 8px; text-align: center;">NetWorth</th>';
        if (cachedStatus.hierarchy >= 10) {
            html += '<th style="padding: 8px; text-align: center;">Action</th>';
        }
        html += '</tr>';

        fed.members.forEach(function (member) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + escapeHtml(member.name) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + (RANK_NAMES[member.hierarchy] || member.hierarchy) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + member.regions + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + member.netWorth.toLocaleString('en-US') + '</td>';
            if (cachedStatus.hierarchy >= 10 && member.id !== cachedStatus.playerId) {
                html += '<td style="padding: 8px; text-align: center;">';
                html += '<button class="market-action-btn fed-kick-btn" data-id="' + member.id + '" style="background-color: #f44336;">Kick</button>';
                html += '</td>';
            } else if (cachedStatus.hierarchy >= 10) {
                html += '<td style="padding: 8px; text-align: center;"></td>';
            }
            html += '</tr>';
        });

        html += '</table>';
        federationContainer.innerHTML = html;

        // Bind leave button
        const leaveBtn = document.getElementById('fedLeaveBtn');
        if (leaveBtn) {
            leaveBtn.onclick = function () {
                if (confirm('Are you sure you want to leave the federation?')) {
                    doPost('/game/api/federation/leave', {}, function () {
                        cachedStatus = null;
                        loadStatus();
                    });
                }
            };
        }

        // Bind kick buttons
        federationContainer.querySelectorAll('.fed-kick-btn').forEach(function (btn) {
            btn.onclick = function () {
                const playerId = parseInt(btn.getAttribute('data-id'), 10);
                if (confirm('Are you sure you want to kick this player?')) {
                    doPost('/game/api/federation/kick/' + playerId, {}, function () {
                        cachedStatus = null;
                        loadStatus();
                    });
                }
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
                federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (result.news.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No federation news yet...</div>';
                return;
            }

            let html = '<table style="width: 100%; border-collapse: collapse;">';
            html += '<tr style="border-bottom: 2px solid #8B7355;">';
            html += '<th style="padding: 8px; text-align: center; width: 150px;">Date</th>';
            html += '<th style="padding: 8px; text-align: left;">Report</th>';
            html += '</tr>';

            result.news.forEach(function (item) {
                html += '<tr style="border-bottom: 1px solid #555;">';
                html += '<td style="padding: 8px; text-align: center;">' + new Date(item.timestamp * 1000).toLocaleString() + '</td>';
                html += '<td style="padding: 8px; text-align: left;">' + escapeHtml(item.news) + '</td>';
                html += '</tr>';
            });

            html += '</table>';
            federationContainer.innerHTML = html;
        } catch (error) {
            console.error('Error loading federation news:', error);
            federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load news</div>';
        }
    }

    // ===== Bank =====
    let bankMode = 'deposit';

    function renderBank() {
        if (!cachedStatus || !cachedStatus.hasFederation) return;

        const fed = cachedStatus.federation;
        const playerRes = cachedStatus.playerResources;
        const canWithdraw = cachedStatus.hierarchy >= 5;

        let html = '<div style="text-align: center; margin-bottom: 15px;">';
        html += '<button class="market-action-btn' + (bankMode === 'deposit' ? '' : '') + '" id="fedBankDepositTab" style="margin-right: 5px;' + (bankMode === 'deposit' ? ' background: #8B7355;' : '') + '">Deposit</button>';
        if (canWithdraw) {
            html += '<button class="market-action-btn" id="fedBankWithdrawTab" style="' + (bankMode === 'withdraw' ? ' background: #8B7355;' : '') + '">Withdraw</button>';
        }
        html += '</div>';

        html += '<table style="width: 100%; border-collapse: collapse; max-width: 500px; margin: 0 auto;">';
        html += '<tr style="border-bottom: 2px solid #8B7355;">';
        html += '<th style="padding: 8px; text-align: center;">Resource</th>';
        html += '<th style="padding: 8px; text-align: center;">You</th>';
        html += '<th style="padding: 8px; text-align: center;">Bank</th>';
        html += '<th style="padding: 8px; text-align: center;">Amount</th>';
        html += '</tr>';

        const resources = ['cash', 'wood', 'steel', 'food'];
        resources.forEach(function (res) {
            html += '<tr style="border-bottom: 1px solid #555;">';
            html += '<td style="padding: 8px; text-align: center;">' + res.charAt(0).toUpperCase() + res.slice(1) + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + (res === 'cash' ? '$ ' : '') + playerRes[res].toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;">' + (res === 'cash' ? '$ ' : '') + fed.bank[res].toLocaleString('en-US') + '</td>';
            html += '<td style="padding: 8px; text-align: center;"><input type="number" id="fedBank_' + res + '" min="0" value="0" style="width: 100px; padding: 4px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; text-align: center;"></td>';
            html += '</tr>';
        });

        html += '<tr><td colspan="4" style="padding: 10px; text-align: center;">';
        html += '<button id="fedBankSubmitBtn" class="market-action-btn" style="padding: 8px 20px;">' + (bankMode === 'deposit' ? 'Deposit' : 'Withdraw') + '</button>';
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

        let html = '<div style="max-width: 500px; margin: 0 auto; padding: 10px;">';
        html += '<h3 style="color: #f3e6c1; margin: 0 0 15px 0;">Send Aid</h3>';

        html += '<div style="margin-bottom: 15px;">';
        html += '<label style="display: block; margin-bottom: 5px; color: #f3e6c1;">To Player:</label>';
        html += '<select id="fedAidPlayer" style="width: 100%; padding: 8px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        members.forEach(function (m) {
            if (m.id === cachedStatus.playerId) return;
            html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>';
        });
        html += '</select>';
        html += '</div>';

        const resources = ['cash', 'wood', 'steel', 'food'];
        resources.forEach(function (res) {
            html += '<div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">';
            html += '<label style="width: 60px; color: #f3e6c1;">' + res.charAt(0).toUpperCase() + res.slice(1) + ':</label>';
            html += '<span style="width: 80px; color: #ccc;">(' + playerRes[res].toLocaleString('en-US') + ')</span>';
            html += '<input type="number" id="fedAid_' + res + '" min="0" value="0" style="flex: 1; padding: 6px; background: #2a2a2a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
            html += '</div>';
        });

        html += '<button id="fedAidSubmitBtn" class="market-action-btn" style="width: 100%; padding: 10px; margin-top: 10px;">Send Aid</button>';
        html += '</div>';

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

        let html = '<div style="max-width: 500px; margin: 0 auto; padding: 10px;">';

        // Change Name
        html += '<div style="margin-bottom: 20px; padding: 10px; background: #2a2a2a; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<h4 style="color: #f3e6c1; margin: 0 0 10px 0;">Change Federation Name</h4>';
        html += '<div style="display: flex; gap: 10px;">';
        html += '<input type="text" id="fedNewName" maxlength="25" value="' + escapeHtml(fed.name) + '" style="flex: 1; padding: 6px; background: #1a1a1a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<button class="market-action-btn" id="fedChangeNameBtn">Change</button>';
        html += '</div></div>';

        // Update Message
        html += '<div style="margin-bottom: 20px; padding: 10px; background: #2a2a2a; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<h4 style="color: #f3e6c1; margin: 0 0 10px 0;">Leader Message</h4>';
        html += '<textarea id="fedNewMessage" rows="4" style="width: 100%; padding: 6px; background: #1a1a1a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px; box-sizing: border-box; resize: vertical;">' + escapeHtml(fed.leaderMessage) + '</textarea>';
        html += '<button class="market-action-btn" id="fedUpdateMessageBtn" style="margin-top: 10px;">Update Message</button>';
        html += '</div>';

        // Change Ranks
        html += '<div style="margin-bottom: 20px; padding: 10px; background: #2a2a2a; border: 1px solid #8B7355; border-radius: 4px;">';
        html += '<h4 style="color: #f3e6c1; margin: 0 0 10px 0;">Change Player Ranks</h4>';
        html += '<div style="display: flex; gap: 10px; margin-bottom: 10px;">';
        html += '<select id="fedRolePlayer" style="flex: 1; padding: 6px; background: #1a1a1a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        members.forEach(function (m) {
            html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>';
        });
        html += '</select>';
        html += '<select id="fedRoleRank" style="flex: 1; padding: 6px; background: #1a1a1a; color: #f3e6c1; border: 1px solid #8B7355; border-radius: 4px;">';
        for (let rank = 10; rank >= 1; rank--) {
            html += '<option value="' + rank + '">' + RANK_NAMES[rank] + '</option>';
        }
        html += '</select>';
        html += '<button class="market-action-btn" id="fedChangeRoleBtn">Change</button>';
        html += '</div></div>';

        // Remove Federation
        html += '<div style="padding: 10px; background: #2a2a2a; border: 1px solid #f44336; border-radius: 4px;">';
        html += '<h4 style="color: #f44336; margin: 0 0 10px 0;">Danger Zone</h4>';
        html += '<button class="market-action-btn" id="fedRemoveBtn" style="background-color: #f44336;">Delete Federation</button>';
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
            if (confirm('Are you sure you want to DELETE this federation? This cannot be undone!')) {
                doPost('/game/api/federation/remove', {}, function () {
                    cachedStatus = null;
                    loadStatus();
                });
            }
        };
    }

    // ===== Applications (General only) =====
    async function renderApplications() {
        federationContainer.innerHTML = '<div class="build-loading">Loading applications...</div>';

        try {
            const response = await fetch('/game/api/federation/applications');
            const result = await response.json();

            if (!result.success) {
                federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message) + '</div>';
                return;
            }

            if (result.applications.length === 0) {
                federationContainer.innerHTML = '<div class="build-loading">No applications at this time</div>';
                return;
            }

            let html = '';
            result.applications.forEach(function (app) {
                html += '<div style="margin-bottom: 15px; padding: 10px; background: #2a2a2a; border: 1px solid #8B7355; border-radius: 4px;">';
                html += '<p><strong style="color: #f3e6c1;">Player:</strong> ' + escapeHtml(app.playerName) + '</p>';
                html += '<p><strong style="color: #f3e6c1;">Application:</strong><br>' + escapeHtml(app.application) + '</p>';
                html += '<div style="margin-top: 10px;">';
                html += '<button class="market-action-btn fed-accept-btn" data-id="' + app.id + '" style="margin-right: 10px;">Accept</button>';
                html += '<button class="market-action-btn fed-reject-btn" data-id="' + app.id + '" style="background-color: #f44336;">Reject</button>';
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
            federationContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load applications</div>';
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
