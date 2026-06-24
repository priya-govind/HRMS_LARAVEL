$(function() {
    let awaitingCommand = null;
    let awaitingFields = [];
    let collectedValues = {};

    // Toggle minimize/maximize
    $('#chatbot-icon').on('click', function() {
        $('#chatbot-modal').fadeToggle();
        $('#chatbot-icon').toggle();
        $('#chatbot-input').focus();

        // 🔹 Load history when opening
        $.get('/chatbot/history', function(history) {
            $("#chatbot-body").empty();
            history.forEach(function(entry) {
                let req = entry.request_payload ? JSON.parse(entry.request_payload) : {};
                let res = entry.response_payload ? JSON.parse(entry.response_payload) : {};
                if (req.text) appendMessage('user', req.text);
                renderBotResponse(res);
            });
        });
    });

    $('#chatbot-icon-minus').on('click', function() {
        $('#chatbot-modal').fadeToggle();
        $('#chatbot-icon').toggle();
    });

    // Send message
    $('#chatbot-send').on('click', function() {
        let text = $('#chatbot-input').val().trim();
        if (!text) return;

        appendMessage('user', text);
        $('#chatbot-input').val('');

        let payload;

        if (awaitingFields.length > 0) {
            // Collect next required field
            let currentField = awaitingFields[Object.keys(collectedValues).length];

            // 🔹 Validate date format if field is a date
            if (currentField.name.toLowerCase().includes('date')) {
                let dateRegex = /^\d{2}\/\d{2}\/\d{4}$/;
                if (!dateRegex.test(text)) {
                    appendMessage('bot', "⚠️ Please enter date in dd/mm/yyyy format.");
                    return;
                }
            }

            collectedValues[currentField.name] = text;

            // If more fields left → ask next
            if (Object.keys(collectedValues).length < awaitingFields.length) {
                let nextField = awaitingFields[Object.keys(collectedValues).length];
                appendMessage('bot', nextField.label);
                return;
            }

            // All fields collected → send payload
            payload = {
                text: awaitingCommand,
                ...collectedValues,
                sender: 'user',
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Reset
            awaitingCommand = null;
            awaitingFields = [];
            collectedValues = {};
        } else {
            // Normal flow
            payload = {
                text: text,
                sender: 'user',
                _token: $('meta[name="csrf-token"]').attr('content')
            };
        }

        $.ajax({
            url: '/chatbot/message',
            type: 'POST',
            data: payload,
            success: function(data) {
                renderBotResponse(data);

                // If bot reply includes required_fields, set up prompts
                if (data.required_fields && Array.isArray(data.required_fields)) {
                    awaitingCommand = data.command; // always returned by controller
                    awaitingFields = data.required_fields;
                    collectedValues = {};
                    // ❌ removed duplicate appendMessage here
                }
            },
            error: function(xhr) {
                appendMessage('bot', "Error: " + xhr.responseText);
            }
        });
    });

    // Handle menu button clicks
    $(document).on('click', '.menu-btn', function() {
        let parentId = $(this).data('id');
        let command = $(this).data('command');
        $.ajax({
            url: '/chatbot/message',
            method: 'POST',
            data: { text: command, parent_id: parentId, sender: 'user', _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                renderBotResponse(response);
            },
            error: function(xhr) {
                appendMessage('bot', "Error: " + xhr.responseText);
            }
        });
    });

    // Centralized helper for messages
    function appendMessage(type, content) {
        let className = type === 'user' ? 'user-message' : 'bot-message';
        let newMsg = $("<div class='" + className + "'>" + content + "</div>");
        $("#chatbot-body").append(newMsg);

        newMsg.hide().fadeIn(500);
        newMsg.addClass("new");
        setTimeout(() => newMsg.removeClass("new"), 2000);

        scrollChatToBottom();
    }

    // Render menus
    function renderMenu(menuItems) {
        let container = $('<div class="bot-menus"></div>');
        $.each(menuItems, function(i, item) {
            let parentBtn = $('<button class="menu-btn" data-id="'+item.parent_id+'" data-command="'+item.command+'">'+item.name+'</button>');
            container.append(parentBtn);
        });
        container.hide().fadeIn(500).addClass("new");
        setTimeout(() => container.removeClass("new"), 2000);
        $("#chatbot-body").append(container);
        scrollChatToBottom();
    }

    // Render bot responses
    function renderBotResponse(response) {
        if (response.reply) appendMessage('bot', response.reply);
        if (response.menus) renderMenu(response.menus);

        if (response.submenus && response.submenus.length > 0) {
            let submenuHtml = "<div class='bot-submenus'>";
            response.submenus.forEach(function(submenu) {
                submenuHtml += "<button class='menu-btn' data-command='" + submenu.command + "'>" + submenu.label + "</button>";
            });
            submenuHtml += "</div>";
            let newSubmenu = $(submenuHtml);
            $("#chatbot-body").append(newSubmenu.hide().fadeIn(500).addClass("new"));
            setTimeout(() => newSubmenu.removeClass("new"), 2000);
        }

        if (response.question) appendMessage('bot', response.question);

        // Handle required fields prompt
        if (response.required_fields && Array.isArray(response.required_fields)) {
            awaitingCommand = response.command; // controller always sends command
            awaitingFields = response.required_fields;
            collectedValues = {};
            // if (awaitingFields.length > 0) {
            //     appendMessage('bot', awaitingFields[0].label);
            // }
        }

        if (response.data && Array.isArray(response.data) && response.data.length > 0) {
            let headers = Object.keys(response.data[0]);
            let tableHtml = "<table class='bot-table'><thead><tr>";
            headers.forEach(function(header) {
                tableHtml += "<th>" + header + "</th>";
            });
            tableHtml += "</tr></thead><tbody>";

            response.data.forEach(function(row) {
                let isAbsent = row.Status && row.Status.toLowerCase() === 'absent';
                let isStillIn = row.CheckOut && row.CheckOut.toLowerCase().includes('still checked in');
                let rowClass = isAbsent ? "absent-row" : (isStillIn ? "still-row" : "");

                tableHtml += "<tr class='" + rowClass + "'>";
                headers.forEach(function(header) {
                    tableHtml += "<td>" + (row[header] ?? '') + "</td>";
                });
                tableHtml += "</tr>";
            });

            tableHtml += "</tbody></table>";
            let newTable = $(tableHtml);
            $("#chatbot-body").append(newTable.hide().fadeIn(500).addClass("new"));
            setTimeout(() => newTable.removeClass("new"), 2000);
        }

        scrollChatToBottom();
    }

    function scrollChatToBottom() {
        let chatWindow = $("#chatbot-body");
        chatWindow.stop().animate({
            scrollTop: chatWindow[0].scrollHeight
        }, 400);
    }

    // Allow Enter key to send message
    $('#chatbot-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#chatbot-send').click();
        }
    });
});