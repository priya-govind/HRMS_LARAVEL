document.addEventListener('DOMContentLoaded',  () => {
    const input = document.getElementById('search-contacts');
    const results = document.getElementById('search-results');
    const chatContainer = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const allChatsTab = document.getElementById('allChatsTab');
    const unreadChatsTab = document.getElementById('unreadChatsTab');
    let currentView = 'all';
    let selectedUserId = null;
    let selectedUserName = '';
    let selectedUserImage = '';
   // const currentUserId = parseInt(document.querySelector('meta[name="current-user-id"]').content);
    const currentUserImage = document.querySelector('meta[name="current-user-image"]')?.content || '/assets/images/faces/face11.jpg';
    let isFetching = false;
    function fetchUsers(query = '', unreadOnly = false) {
            let path_name=get_uri_segment(3);
            
            let url = `/search-users?q=${encodeURIComponent(query)}&path=`+path_name;
            if (unreadOnly) url += '&unreadOnly=1';
            isFetching = true;
            fetch(url)
                .then(response => response.ok ? response.json() : Promise.reject('Failed to load users'))
                .then(data => {
                    if (!Array.isArray(data)) {
                        console.error("Unexpected response format:", data);
                        return;
                    }
                    if (unreadOnly) {
                        data = data.filter(user => user.unread_count > 0);
                    } 
                    data.sort((a, b) => {
                        if (a.is_today && !b.is_today) return -1;
                        if (!a.is_today && b.is_today) return 1;

                        const dateA = a.last_message_time ? new Date(a.last_message_time) : 0;
                        const dateB = b.last_message_time ? new Date(b.last_message_time) : 0;
                        return dateB - dateA; 
                    });
                    results.innerHTML = '';
                    if (data.length === 0) {
                        results.innerHTML = '<li class="list-group-item">No users found</li>';
                        return;
                    } 
                    data.forEach(user => {
                        const profileImage = user.profile_image ? `/images/${user.profile_image}` : '/assets/images/faces/face1.jpg';
                            const lastMessage = user.last_message || 'No messages yet';
                            const hasAttachment = user.attachment_path && user.attachment_path.trim() !== '';

                            let lastMessageText;

                            if (hasAttachment) {
                                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(user.attachment_path);
                                lastMessageText = isImage ? '🖼️ Image received' : '📎 File received';
                            } else {
                                lastMessageText = (lastMessage !== '[File]' && lastMessage.length > 30)
                                    ? lastMessage.slice(0, 30) + '...'
                                    : lastMessage;
                            }
                        const lastMessageTicks = (user.last_message_sender_id === currentUserId)
                            ? (user.last_message_seen ? ' ✓✓' : ' ✓')
                            : '';
                        const userItem = document.createElement('li');
                        userItem.className = 'list-group-item d-flex justify-content-between align-items-center click-user';
                        userItem.style.cursor = 'pointer';
                        userItem.dataset.userId = user.id;
                        userItem.innerHTML = `
                            <div class="d-flex align-items-center position-relative w-100">
                                <img src="${profileImage}" alt="Profile Image" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div class="d-flex flex-column w-100">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <strong>${user.name}</strong>
                                        <small class="text-muted">${user.last_message_time || ''}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="${user.isBlocked ? 'text-danger' : 'text-muted'}">
                                            ${user.isBlocked ? '<i class="mdi mdi-cancel"></i> Blocked' : lastMessageText + lastMessageTicks}
                                        </small>
                                        ${user.unread_count > 0 && !user.isBlocked
                                            ? `<span class="badge bg-info rounded-circle text-white ms-2 unread-badge" style="width: 15px; height: 15px; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                                ${user.unread_count}
                                            </span>` 
                                            : ''
                                        }
                                    </div>
                                </div>
                            </div>
                        `;                 
                        userItem.addEventListener('click', function () {
                            sessionStorage.setItem("user_id", user.id);
                            sessionStorage.setItem("user_name", user.name);
                            sessionStorage.setItem("user_image", profileImage);

                            const badge = this.querySelector('.unread-badge');
                            if (badge) badge.style.display = 'none';  
                                if (window.location.pathname.includes('dashboard')) {
                                    window.location.href = `/chats?user_name=${user.name}`;
                                } else {
                                    loadChat(user.id, user.name, profileImage);
                                }
                        });

                        results.appendChild(userItem);
                    });
                })
                .catch(error => console.error("Error fetching users:", error))
                .finally(() => {
                    isFetching = false;
                });
        } 
    window.switchChatView = function(view) {
        if (view === currentView) return;
        currentView = view;
        input.value = '';
        allChatsTab.classList.toggle('active', view === 'all');
        unreadChatsTab.classList.toggle('active', view === 'unread');
        fetchUsers('', view === 'unread');
    }; 
    input.addEventListener('input', () => {
        fetchUsers(input.value.trim(), currentView === 'unread');
    });
fetchUsers();
    setInterval(() => {
        if (isFetching) return;
        const query = input.value.trim();
        const unreadOnly = currentView === 'unread';
        fetchUsers(query, unreadOnly);
    }, 18000);
    // load chat window
    function loadChat(userId, userName, userImage) {
        if (!chatContainer) return;
        selectedUserId = userId;
        selectedUserName = userName;
        selectedUserImage = userImage;
        fetch(`/chat/mark-chat-read/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        fetch(`/chat-messages/${userId}`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 403) throw new Error("You have blocked this user.");
                    throw new Error("Failed to load chat");
                }
                return response.json();
            })
           .then(data => {
                const messages = data.messages;
                const isBlocked = data.isBlocked; 
                const isMuted = data.isMuted; 
                
                let html = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex align-items-center">
                        <div style="position: relative;">
                            <img src="${userImage}" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="width: 12px; height: 12px;"></span>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size:20px;">${userName}</h6>
                            <small class="text-dark">Online</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0" type="button" id="chatMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; box-shadow: none">
                            <i class="mdi mdi-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            
                            <li> 
                                <a class="dropdown-item" href="#" id="mute-user">
                                    <i class="mdi me-2 ${isMuted ? 'mdi-bell' : 'mdi-bell-off'}"></i>
                                    ${isMuted ? 'Unmute Notifications' : 'Mute Notifications'}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" id="clear-messages">
                                    <i class="mdi mdi-message-minus me-2"></i> Clear Messages
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="block-user">
                                    <i class="mdi me-2 ${isBlocked ? 'mdi-lock-open' : 'mdi-block-helper'}"></i>
                                    ${isBlocked ? 'Unblock' : 'Block'}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div> 
                <hr class="border-top border-2 border-white">
                <div id="chat-messages-inner" style="verflow-y: auto; max-height: 550px;">`;
                // <li>
                //                 <a class="dropdown-item" href="#" id="delete-chat">
                //                     <i class="mdi mdi-delete me-2"></i> Delete Chat
                //                 </a>
                //             </li>
                if (isBlocked) {
                                // Show unblock box
                                html += `
                                    <div id="no-messages-box" style="border: 1px solid #ccc; padding: 10px; text-align: center; color: black; font-size: 12px; max-width: 300px; margin: 40px auto; background-color: white; border-radius: 10px; font-style: italic;">
                                        Unblock the user to Chat and start talking!
                                    </div>`;
                } else {
                        if (messages.length === 0) {
                            html += `
                                <div id="no-messages-box" style="border: 1px solid #ccc; padding: 10px; text-align: center; color: black; font-size: 12px; max-width: 300px; margin: 40px auto; background-color: white; border-radius: 10px; font-style: italic;">
                                    No messages to display.<br>Begin your conversation now.<br>Stay connected and start talking!
                                </div>`;
                        } else {
                            let lastMessageDate = null;
                        messages.forEach(msg => {
                                const isMe = msg.sender_id === currentUserId;
                                const date = new Date(msg.created_at);
                                const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                const dateStr = date.toDateString() === new Date().toDateString() ? 'Today' : date.toLocaleDateString();

                                if (!lastMessageDate || date.toDateString() !== lastMessageDate.toDateString()) {
                                    html += `<div class="text-center my-3"><span class="badge bg-dark">${dateStr}</span></div>`;
                                }
                                lastMessageDate = date;

                                html += `<div class="d-flex justify-content-${isMe ? 'end' : 'start'} mb-3 align-items-end">`;

                                if (!isMe) {
                                    html += `
                                    <div class="d-flex flex-column align-items-center me-2">
                                        <img src="${selectedUserImage}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                        <small style="font-size: 10px;">${selectedUserName}</small>
                                    </div>
                                    <div class="p-2 rounded-3" style="background-color: #dbd8e6; color: black; min-width: 100px; max-width: 300px;">
                                        ${msg.reply_message ? `
                                            <div class="reply-context p-1 mb-2 rounded message-item" style="background-color: #5ea1d0; font-size: 13px; border-left: 3px solid #999;">
                                                <strong>Replying to:</strong> ${msg.reply_message}
                                            </div>
                                        ` : ''}
                                            ${msg.message && msg.message!=='[File]' ? `<div class="message-text">${msg.message}</div>` : ``}
                                                ${msg.attachment_path && msg.attachment_path.trim() !== '' ? `
                                                    <div class="mt-2 d-flex align-items-center gap-1">
                                                        <i class="mdi mdi-paperclip" style="font-size: 18px; color: ${isMe ? 'white' : 'black'};"></i>
                                                        <a href="/${msg.attachment_path}" download style="font-size: 13px; color: ${isMe ? 'white' : 'black'}; text-decoration: underline;">
                                                            ${msg.attachment_path.split('/').pop()}
                                                        </a>
                                                    </div>
                                                ` : ''}
                                        <div class="text-end mt-1" style="font-size: 10px; color: black;">
                                            ${time}
                                        </div>
                                    </div>
                                    <div class="dropdown ms-2">
                                        <button class="btn btn-sm btn-link p-0 text-dark" type="button" id="messageMenuButton${msg.id}" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 18px; border: none;">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="messageMenuButton${msg.id}">
                                            <li><a class="dropdown-item reply-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-message-reply-text me-2"></i> Reply</a></li>
                                            <li><a class="dropdown-item forward-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-share me-2"></i> Forward</a></li>
                                        </ul> 
                                    </div>`;
                                    //<li><a class="dropdown-item delete-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-delete me-2"></i> Delete3</a></li>
                                    //<li><a class="dropdown-item copy-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-content-copy me-2"></i> Copy</a></li>
                                }

                                if (isMe) {
                                    html += `
                                    <div class="dropdown ms-2">
                                        <button class="btn btn-sm btn-link p-0 text-white" type="button" id="messageMenuButton${msg.id}" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 18px; border: none; margin-right: 10px">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="messageMenuButton${msg.id}">
                                            <li><a class="dropdown-item reply-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-message-reply-text me-2"></i> Reply</a></li>
                                            <li><a class="dropdown-item forward-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-share me-2"></i> Forward</a></li>
                                        </ul> 
                                    </div>
                                    <div class="p-2 rounded-3" style="background-color: #6c54c2; color: white; min-width: 100px; max-width: 300px;">
                                        ${msg.reply_message ? `
                                            <div class="reply-context p-1 mb-2 rounded message-item" style="background-color: #5ea1d0; font-size: 13px; border-left: 3px solid #999;">
                                                <strong>Replying to:</strong> ${msg.reply_message}
                                            </div>
                                        ` : ''}
                                        ${msg.message && msg.message!=='[File]' ? `<div class="message-text">${msg.message}</div>` : ``}
                                            ${msg.attachment_path && msg.attachment_path.trim() !== '' ? `
                                                    <div class="mt-2 d-flex align-items-center gap-1">
                                                        <i class="mdi mdi-paperclip" style="font-size: 18px; color: ${isMe ? 'white' : 'black'};"></i>
                                                        <a href="/${msg.attachment_path}" download style="font-size: 13px; color: ${isMe ? 'white' : 'black'}; text-decoration: underline;">
                                                            ${msg.attachment_path.split('/').pop()}
                                                        </a>
                                                    </div>
                                                ` : ''}
                                        <div class="text-end mt-1" style="font-size: 10px; color: black;">
                                            ${time}
                                            ${msg.seen
                                                ? '<small><i class="mdi mdi-check-all text-white"></i></small>'
                                                : '<small><i class="mdi mdi-check text-white"></i></small>'}
                                        </div>
                                    </div> 
                                    <div class="d-flex flex-column align-items-center ms-2">
                                        <img src="${currentUserImage}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                        <small style="font-size: 10px;">You</small>
                                    </div>`;
                                    //<li><a class="dropdown-item delete-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-delete me-2"></i> Delete2</a></li>
                                    //<li><a class="dropdown-item copy-message" href="#" data-msgid="${msg.id}"><i class="mdi mdi-content-copy me-2"></i> Copy</a></li>
                                }

                                html += `</div>`;
                            });                 
                    }
                }
                html += `</div>`;
                chatContainer.innerHTML = html;
                   if (isBlocked) {
                        if (messageInput) messageInput.disabled = true;
                        if (sendButton) sendButton.disabled = true;
                    } else {
                        if (messageInput) {
                            messageInput.disabled = false;
                            messageInput.focus();
                        }
                        if (sendButton) sendButton.disabled = false;

                        const chatMessagesInner = document.getElementById('chat-messages-inner');
                        if (chatMessagesInner) {
                            chatMessagesInner.scrollTop = chatMessagesInner.scrollHeight;
                        }
                    }
              document.getElementById('delete-chat')?.addEventListener('click', e => {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this chat?')) {
                    fetch(`/chat/message/delete/${selectedUserId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).then(res => {
                        if (res.ok) {
                            alert('Chat deleted.');
                            chatContainer.innerHTML = '';
                        } else {
                            alert('Failed to delete chat.');
                        }
                    });
                }
            });
                // mute notifications
                document.getElementById('mute-user')?.addEventListener('click', e => {
                    e.preventDefault();
                    const el = e.currentTarget;
                    const isCurrentlyMuted = el.innerText.includes('Unmute');
                    const action = isCurrentlyMuted ? 'unmute' : 'mute';
                    if (confirm(`Are you sure you want to ${action} this user?`)) {
                        fetch(`/chat/${action}/${selectedUserId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                alert(data.message);
                                const icon = el.querySelector('i');
                                icon.className = `mdi me-2 ${action === 'mute' ? 'mdi-bell' : 'mdi-bell-off'}`;
                                el.innerHTML = `<i class="${icon.className}"></i> ${action === 'mute' ? 'Unmute Notifications' : 'Mute Notifications'}`;
                            } else {
                                alert(data.message || `Failed to ${action} user.`);
                            }
                        }).catch(err => {
                            console.error(err);
                            alert(`Error trying to ${action} user.`);
                        });
                    }
                });
                // clear messages
                document.getElementById('clear-messages')?.addEventListener('click', e => {
                    e.preventDefault();
                    if (confirm('Clear all messages with this user?')) {
                        fetch(`/chat/messages/clear/${selectedUserId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(res => {
                            if (res.ok) {
                                loadChat(selectedUserId, selectedUserName, selectedUserImage); 
                            } else {
                                alert('Failed to clear messages.');
                            }
                        });
                    }
                });
                // block user
                document.getElementById('block-user')?.addEventListener('click', e => {
                    e.preventDefault();
                    const isCurrentlyBlocked = e.target.innerText.includes('Unblock');
                    const action = isCurrentlyBlocked ? 'unblock' : 'block';
                    if (confirm(`Are you sure you want to ${action} this user?`)) {
                        fetch(`/chat/${action}/${selectedUserId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                alert(data.message);
                                loadChat(selectedUserId, selectedUserName, selectedUserImage);
                            } else {
                                alert(data.message || `Failed to ${action} user.`);
                            }
                        }).catch(err => {
                            console.error(err);
                            alert(`Error trying to ${action} user.`);
                        });
                    }
                });
                // emoji
                document.getElementById('emoji-button').addEventListener('click', function() {
                    console.log('Emoji button clicked');
                    const emojiPanel = document.createElement('div');
                    emojiPanel.style.position = 'absolute';
                    emojiPanel.style.bottom = '50px'; 
                    emojiPanel.style.right = '10px';
                    emojiPanel.style.backgroundColor = 'white';
                    emojiPanel.style.borderRadius = '5px';
                    emojiPanel.style.boxShadow = '0px 0px 10px rgba(0, 0, 0, 0.1)';
                    emojiPanel.style.padding = '10px';
                    emojiPanel.style.zIndex = '9999';
                    const emojis = ['😀', '😂', '😍', '😢', '😎', '🥺', '🤔', '🤗']; 
                    emojis.forEach(emoji => {
                        const emojiButton = document.createElement('button');
                        emojiButton.innerText = emoji;
                        emojiButton.style.fontSize = '20px';
                        emojiButton.style.border = 'none';
                        emojiButton.style.background = 'transparent';
                        emojiButton.style.cursor = 'pointer';
                        emojiButton.addEventListener('click', function() {
                            const inputField = document.getElementById('message-input');
                            inputField.value += emoji;
                            emojiPanel.remove(); 
                        });
                        emojiPanel.appendChild(emojiButton);
                    });
                    document.body.appendChild(emojiPanel);
                });
                const fileButton = document.getElementById('file-button');
                const fileInput = document.getElementById('file-input');

                fileButton.addEventListener('click', () => {
                    console.log('File button clicked');
                    fileInput.click();
                });

                fileInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('attachment', file);
                    formData.append('receiver_id', selectedUserId); // make sure this is defined

                    fetch('/chat/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log('File uploaded and chat saved:', data.chat);
                            // Optionally refresh chat or append new message
                        } else {
                            alert('Upload failed');
                        }
                    })
                    .catch(err => {
                        console.error('Upload error:', err);
                        alert('Upload error');
                    });
                });
                //  Reply to a message
                document.querySelectorAll('.reply-message').forEach(el => {
                    el.addEventListener('click', e => {
                        e.preventDefault();
                        const msgId = el.getAttribute('data-msgid');
                        const msgText = el.closest('.d-flex').querySelector('div[style*="word-break"]')?.innerText || '';

                        const replyBox = document.getElementById('reply-box');
                        replyBox.innerHTML = `
                            <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                                <span><strong>Replying to:</strong> ${msgText}</span>
                                <button class="btn btn-sm btn-danger" onclick="cancelReply()">Cancel</button>
                            </div>
                        `;
                        replyBox.dataset.replyTo = msgId;
                        replyBox.style.display = 'block';
                    });
                });
                // Delete a message
                document.querySelectorAll('.delete-message').forEach(el => {
                    el.addEventListener('click', e => {
                        e.preventDefault();
                        const msgId = e.currentTarget.getAttribute('data-msgid');
                        if (confirm('Are you sure you want to delete this message?')) {
                            fetch(`/chat/message/delete/${msgId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            }).then(res => {
                                if (res.ok) {
                                    loadChat(selectedUserId, selectedUserName, selectedUserImage);
                                } else {
                                    alert('Failed to delete message.');
                                }
                            }).catch(() => alert('Error deleting message.'));
                        }
                    });
                });
                //  Forward a message
                document.querySelectorAll('.forward-message').forEach(el => {
                    el.addEventListener('click', e => {
                        e.preventDefault();
                        const msgId = e.currentTarget.getAttribute('data-msgid');
                        const recipientName = prompt("Enter the username to forward this message to:");
                        if (!recipientName) return;

                        fetch('/chat/message/forward', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                original_message_id: msgId,
                                receiver_name: recipientName
                            })
                        }).then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert('Message forwarded successfully!');
                            } else {
                                alert('Failed to forward message.');
                            }
                        }).catch(() => alert('Error forwarding message.'));
                    });
                });         
                // Copy message to clipboard
                // document.querySelectorAll('.copy-message').forEach(el => {
                //     el.addEventListener('click', e => {
                //         e.preventDefault();
                //         const msgId = el.getAttribute('data-msgid');
                //         const messageElement = el.closest('.d-flex').querySelector('div[style*="word-break"]');

                //         if (messageElement) {
                //             const messageText = messageElement.innerText;

                //             navigator.clipboard.writeText(messageText)
                //                 .then(() => {
                //                     alert('Message copied to clipboard!');
                //                 })
                //                 .catch(err => {
                //                     console.error('Copy failed:', err);
                //                     alert('Failed to copy message.');
                //                 });
                //         }
                //     });
                // });
                document.querySelectorAll('.copy-message').forEach(el => {
                    el.addEventListener('click', e => {
                        e.preventDefault();
                        const bubble = el.closest('.message-item'); // parent bubble
                        const messageElement = bubble.querySelector('.message-text');

                        if (messageElement) {
                            navigator.clipboard.writeText(messageElement.innerText)
                                .then(() => alert('Message copied to clipboard!'))
                                .catch(err => {
                                    console.error('Copy failed:', err);
                                    alert('Failed to copy message.');
                                });
                        }
                    });
                });
                // favorite message
                document.querySelectorAll('.favorite-message').forEach(el => {
                    el.addEventListener('click', e => {
                        e.preventDefault();
                        const msgId = e.currentTarget.getAttribute('data-msgid');
                        fetch(`/chat/message/favorite/${msgId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert('Message added to favorites!');
                            } else {
                                alert('Failed to favorite message.');
                            }
                        }).catch(() => alert('Error favoriting message.'));
                    });
                });
            })
    }  
    let isDropdownOpen = false;
    document.addEventListener('show.bs.dropdown', () => {
        isDropdownOpen = true;
    });
    document.addEventListener('hide.bs.dropdown', () => {
        isDropdownOpen = false;
    }); 
// send message 
//  Send message with reply support
    if(sendButton){
        sendButton.addEventListener('click', () => {
            const message = messageInput.value.trim();
            if (!message || !selectedUserId) return alert('Select a user and type a message.');

            const replyToId = document.getElementById('reply-box')?.dataset.replyTo || null;
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const tempMessageId = `msg-${Date.now()}`;

            const messageHtml = `
                <div class="d-flex justify-content-end mb-3 align-items-end" id="${tempMessageId}">
                    <div class="dropdown me-2">
                        <button class="btn btn-sm btn-link p-0 text-white" type="button" id="messageMenuButton${tempMessageId}" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 18px; border: none;">
                            <i class="mdi mdi-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="messageMenuButton${tempMessageId}">
                            <li><a class="dropdown-item reply-message" href="#" data-msgid="${tempMessageId}"><i class="mdi mdi-message-reply-text me-2"></i> Reply</a></li>
                            <li><a class="dropdown-item forward-message" href="#" data-msgid="${tempMessageId}"><i class="mdi mdi-share me-2"></i> Forward</a></li>
                        </ul>
                    </div>
                    <div class="p-2 rounded-3" style="background-color: #6c54c2; color: white;">
                        <div>${message}</div>
                        <div class="text-end mt-1" style="font-size: 10px; color: black;">
                            ${time}
                            <small class="ms-2 tick-icon">
                                <i class="mdi mdi-check text-muted"></i>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-center ms-2">
                        <img src="${currentUserImage}" class="rounded-circle" style="width: 32px; height: 32px;">
                        <small style="font-size: 10px;">You</small>
                    </div>
                </div>`;
                //<li><a class="dropdown-item delete-message" href="#" data-msgid="${tempMessageId}"><i class="mdi mdi-delete me-2"></i> Delete</a></li>
                //<li><a class="dropdown-item copy-message" href="#" data-msgid="${tempMessageId}"><i class="mdi mdi-content-copy me-2"></i> Copy</a></li>

            const chatMessagesInner = document.getElementById('chat-messages-inner');
            chatMessagesInner?.insertAdjacentHTML('beforeend', messageHtml);
            chatMessagesInner.scrollTop = chatMessagesInner.scrollHeight;
            messageInput.value = '';
            messageInput.focus();
            cancelReply(); // clear reply box after sending
        console.log('Sending reply_to:', replyToId);
            fetch('/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    receiver_id: selectedUserId,
                    message,
                    reply_to: replyToId
                })
            }).then(res => res.json())
            .then(data => {
                console.log('Message stored:', data.message);
                if (data.success) {
                    const userItem = document.querySelector(`[data-user-id="${selectedUserId}"]`);
                    if (userItem) {
                        const lastMessageText = message.length > 30 ? message.slice(0, 30) + '...' : message;
                        const lastMessageTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        userItem.querySelector('.text-muted').innerHTML = `${lastMessageText} <small>${lastMessageTime}</small>`;
                        const badge = userItem.querySelector('.unread-badge');
                            if (badge) {
                            badge.textContent = user.unread_count;
                            badge.style.display = user.unread_count > 0 ? 'inline-block' : 'none';
                        }

                    }
                } else {
                    console.error('Send failed:', data.error);
                }
            }).catch(err => console.error('Error:', err));
        });
    }

    const params = new URLSearchParams(window.location.search);
    const urlUserName = params.get("user_name");

        if (urlUserName) {
            // Fetch user details by name
            fetch(`/search-users?q=${encodeURIComponent(urlUserName)}`)
                .then(res => res.json())
                .then(users => {
                    const user = users.find(u => u.name === urlUserName);
                    if (user) {
                        const profileImage = user.profile_image 
                            ? `/images/${user.profile_image}` 
                            : '/assets/images/faces/face1.jpg';

                        // Store in sessionStorage for next time
                        sessionStorage.setItem("user_id", user.id);
                        sessionStorage.setItem("user_name", user.name);
                        sessionStorage.setItem("user_image", profileImage);

                        // Load chat for this user
                        loadChat(user.id, user.name, profileImage);
                    }
                });
        } else {
            // Fallback: use stored values
            const storedId = sessionStorage.getItem("user_id");
            const storedName = sessionStorage.getItem("user_name");
            const storedImage = sessionStorage.getItem("user_image");

            if (storedId && storedName && storedImage) {
                loadChat(storedId, storedName, storedImage);
            }
        }
        setInterval(() => {
            if (selectedUserId && !isDropdownOpen) {
                loadChat(selectedUserId, selectedUserName, selectedUserImage);
            }
        }, 18000);  

}); 