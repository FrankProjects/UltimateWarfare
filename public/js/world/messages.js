/**
 * Messages modal logic (inbox, outbox, read, delete).
 * Depends on: notifications.js
 */
(function () {
    const messagesModal = document.getElementById('messagesModal');
    const messagesModalTitle = document.getElementById('messagesModalLabel');
    const messagesContainer = document.getElementById('messagesContainer');
    const messagesTabs = document.getElementById('messagesTabs');
    const messagesFooter = document.getElementById('messagesFooter');

    let currentTab = 'inbox';
    let currentView = 'list'; // 'list' or 'read'

    document.getElementById('messagesBtn').addEventListener('click', function (e) {
        e.preventDefault();
        // Close the profile dropdown
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        showMessagesModal();
    });

    function showMessagesModal() {
        bootstrap.Modal.getOrCreateInstance(messagesModal).show();
        currentTab = 'inbox';
        currentView = 'list';
        renderTabs();
        loadMessages();
    }

    function renderTabs() {
        messagesTabs.innerHTML = '';
        const tabs = [
            { id: 'inbox', name: 'Inbox' },
            { id: 'outbox', name: 'Outbox' }
        ];

        tabs.forEach(function (tab) {
            const el = document.createElement('div');
            el.className = 'build-tab' + (currentTab === tab.id ? ' active' : '');
            el.textContent = tab.name;
            el.onclick = function () {
                currentTab = tab.id;
                currentView = 'list';
                renderTabs();
                loadMessages();
            };
            messagesTabs.appendChild(el);
        });
    }

    async function loadMessages() {
        messagesContainer.innerHTML = '<div class="build-loading">Loading messages...</div>';
        messagesModalTitle.textContent = currentTab === 'inbox' ? 'Inbox' : 'Outbox';
        messagesFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';

        try {
            const response = await fetch('/game/api/message/' + currentTab);
            const result = await response.json();

            if (result.success) {
                renderMessageList(result.messages);
            } else {
                messagesContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load messages</div>';
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            messagesContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load messages</div>';
        }
    }

    function renderMessageList(messages) {
        if (messages.length === 0) {
            messagesContainer.innerHTML = '<div class="build-loading">No messages</div>';
            return;
        }

        const isInbox = currentTab === 'inbox';
        let html = '<table class="messages-table">';
        html += '<tr class="messages-header">';
        html += '<th>' + (isInbox ? 'From' : 'To') + '</th>';
        html += '<th>Subject</th>';
        html += '<th>Date</th>';
        html += '<th style="width: 60px;">Delete</th>';
        html += '</tr>';

        messages.forEach(function (msg) {
            const rowClass = (isInbox && msg.isNew) ? ' class="message-unread"' : '';
            const adminTag = (isInbox && msg.isAdmin) ? '<span class="message-admin-tag">Admin</span> ' : '';
            html += '<tr' + rowClass + '>';
            html += '<td style="width: 120px;">' + escapeHtml(isInbox ? msg.from : msg.to) + '</td>';
            html += '<td><a href="#" class="message-subject-link" data-id="' + msg.id + '">' + adminTag + escapeHtml(msg.subject) + '</a></td>';
            html += '<td style="width: 140px; color: #aaa; font-size: 12px;">' + msg.date + '</td>';
            html += '<td style="width: 60px; text-align: center;"><a href="#" class="message-delete-link" data-id="' + msg.id + '" title="Delete">&#10006;</a></td>';
            html += '</tr>';
        });

        html += '</table>';
        messagesContainer.innerHTML = html;

        // Bind read links
        messagesContainer.querySelectorAll('.message-subject-link').forEach(function (link) {
            link.onclick = function (e) {
                e.preventDefault();
                readMessage(parseInt(this.getAttribute('data-id')));
            };
        });

        // Bind delete links
        messagesContainer.querySelectorAll('.message-delete-link').forEach(function (link) {
            link.onclick = function (e) {
                e.preventDefault();
                deleteMessage(parseInt(this.getAttribute('data-id')));
            };
        });
    }

    async function readMessage(messageId) {
        messagesContainer.innerHTML = '<div class="build-loading">Loading message...</div>';
        currentView = 'read';

        try {
            const response = await fetch('/game/api/message/' + currentTab + '/' + messageId);
            const result = await response.json();

            if (result.success) {
                renderReadMessage(result.message);
            } else {
                messagesContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">' + escapeHtml(result.message) + '</div>';
            }
        } catch (error) {
            console.error('Error reading message:', error);
            messagesContainer.innerHTML = '<div class="build-loading" style="color: #f44336;">Failed to load message</div>';
        }
    }

    function renderReadMessage(msg) {
        const isInbox = currentTab === 'inbox';
        const adminTag = (msg.isAdmin) ? '<span class="message-admin-tag">Admin</span> ' : '';

        let html = '<div class="message-read">';
        html += '<div class="message-read-header">';
        html += '<p><strong>' + (isInbox ? 'From' : 'To') + ':</strong> ' + escapeHtml(isInbox ? msg.from : msg.to) + '</p>';
        html += '<p><strong>Subject:</strong> ' + adminTag + escapeHtml(msg.subject) + '</p>';
        html += '<p><strong>Date:</strong> ' + msg.date + '</p>';
        html += '</div>';
        html += '<div class="message-read-body">' + escapeHtml(msg.body).replace(/\n/g, '<br>') + '</div>';
        html += '</div>';

        messagesContainer.innerHTML = html;
        messagesModalTitle.textContent = msg.subject;

        // Update footer with back and reply buttons
        let footerHtml = '<button type="button" class="btn btn-secondary" id="backToListBtn">Back</button>';
        if (isInbox) {
            footerHtml += '<button type="button" class="btn btn-primary" id="replyMessageBtn">Reply</button>';
        }
        footerHtml += '<button type="button" class="btn btn-danger" id="deleteReadMessageBtn">Delete</button>';
        messagesFooter.innerHTML = footerHtml;

        document.getElementById('backToListBtn').onclick = function () {
            currentView = 'list';
            renderTabs();
            loadMessages();
        };

        document.getElementById('deleteReadMessageBtn').onclick = function () {
            deleteMessage(msg.id);
        };

        if (isInbox && document.getElementById('replyMessageBtn')) {
            document.getElementById('replyMessageBtn').onclick = function () {
                bootstrap.Modal.getInstance(messagesModal).hide();
                messagesModal.addEventListener('hidden.bs.modal', function handler() {
                    messagesModal.removeEventListener('hidden.bs.modal', handler);
                    showSendMessageModalFromMessages(msg.from, 'Re: ' + msg.subject);
                });
            };
        }
    }

    async function deleteMessage(messageId) {
        try {
            const response = await fetch('/game/api/message/' + currentTab + '/' + messageId, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                currentView = 'list';
                loadMessages();
            } else {
                showNotification(result.message || 'Failed to delete message', 'error');
            }
        } catch (error) {
            console.error('Error deleting message:', error);
            showNotification('An error occurred while deleting the message.', 'error');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Callback for reply - will be set by modals.js if send message modal exists
    function showSendMessageModalFromMessages(playerName, subject) {
        if (window.WorldMessages && window.WorldMessages.onReply) {
            window.WorldMessages.onReply(playerName, subject);
        }
    }

    window.WorldMessages = {
        onReply: null,
        show: showMessagesModal
    };
})();
