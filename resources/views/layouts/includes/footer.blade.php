@include('layouts.includes.message_popup')
@if(session('role_id')===config('global.superadmin'))
<!-- Robot icon trigger -->
<span id="chatbot-icon" class="mdi mdi-robot-outline" title="Any Help Ask me"  @if ((session('checked_attendance') != true)) style="display:none;" @endif></span>

<!-- Chatbot modal -->

<div id="chatbot-modal" class="chatbot-modal">
  <div class="chatbot-modal-content">
    <div id="chatbot-header">
      Chatbot 
      <span id="chatbot-icon-minus" class="mdi mdi-minus" style="padding: 0 0 0 76%;"></span>
    </div>

    <!-- Messages container -->
    <div id="chatbot-body">
      <!-- messages will be appended here -->
    </div>

    <!-- Footer -->
    <div id="chatbot-footer">
        <b>Hey {{ session('user_name') }}! How can I assist you today?</b>
        <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
            <input id="chatbot-input" type="text" placeholder="Any Help Ask me">
            <button id="chatbot-send" class="btn btn-primary w-30 mdi mdi-send"></button>
        </div>
    </div>
  </div>
</div>
@endif

<footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2026 <a href="https://www.bootstrapdash.com/" target="_blank">BootstrapDash</a>. All rights reserved.</span>
            </div>
          </footer>


            @if ((session('checked_attendance') === false) || (isset($LoadDateTimepicker) && $LoadDateTimepicker) )
                <script src="{{ asset('assets/js/attendance.js') }}"></script>
            @endif
          <script src="{{ asset('assets/js/common.js') }}"></script>
          <script src="{{ asset('assets/js/chatbot.js') }}"></script>
        

<script type="text/javascript">
       @if(isset($LoadBarChart) && $LoadBarChart==true)
              var chartDataSets = @json($projects);
              var firstProject = "{{ array_keys($projects)[0] }}";
                renderChart(firstProject);          
            function renderChart(projectName) {
                if (!chartDataSets[projectName]) {
                    console.error("Project not found:", projectName);
                    return;
                }

                var categories = [];
                var dataset = [];

                var modules = Object.keys(chartDataSets[projectName]);
                var employees = [];

                // Collect all employees across modules
                modules.forEach(mod => {
                    Object.keys(chartDataSets[projectName][mod]).forEach(emp => {
                        if (!employees.includes(emp)) employees.push(emp);
                    });
                });

                categories.push({
                    "category": employees.map(emp => ({ "label": emp }))
                });
                modules.forEach(mod => {
                    dataset.push({
                        seriesname: mod,
                        data: employees.map(emp => {
                            var entry = chartDataSets[projectName][mod] && chartDataSets[projectName][mod][emp];
                            if (entry && entry.value > 0) {
                                return {
                                    value: entry.value,
                                    displayValue: shortenLabel(mod) + "<br/>(" + entry.value + " hrs)", //  text on bar
                                    toolText: mod //  tooltip shows only module name
                                };
                            } else {
                                return {
                                    value: "", 
                                    displayValue: "", //  no label on bar
                                    toolText: mod     //  still shows module name only
                                };
                            }
                        })
                    });
                });
                var chartObj = new FusionCharts({
                    type: 'stackedcolumn2d',
                    renderAt: 'timesheet-chart',
                    width: '100%',
                    height: '600',
                    dataFormat: 'json',
                    dataSource: {
                        "chart": {
                            "caption": projectName ,
                            "xAxisName": "Employees",
                            "yAxisName": "Hours",
                            "theme": "fusion",
                            "showValues": "1",
                            "showZeroValues": "0", //  hides 0 labels
                            "maxColWidth": "80",    //  wider bars
                           // "showTooltip": "0" ,    //  disables tooltip
                            "showLegend": "0",
                            "labelDisplay": "wrap",   //  wraps long labels
                            "slantLabels": "0",       //  keeps them horizontal
                            "rotateLabels": "0" 
                        },
                        "categories": categories,
                        "dataset": dataset
                    }
                });
                chartObj.render();
            }
            function shortenLabel(text, maxLength = 10) {
    return text.length > maxLength ? text.substring(0, maxLength) + "…" : text;
}

        // renderChart('All Projects');
          @endif
     @if (session('show_birthday_alert') === true) 
    window.onload = function () {
        const modalEl2 = document.getElementById('BirthAlertModal');
        if (modalEl2) {
            fetch('/check_birthday_alert')
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const messages = data.map(entry =>
                            `${entry.names} have birthday on ${entry.day} (${entry.date})!<br/>`
                        ).join(' ');

                        $('#info').html(messages);

                        const myModal1 = new bootstrap.Modal(modalEl2, {
                            backdrop: 'static',
                            keyboard: false
                        });
                        myModal1.show();
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }
    };
    @endif
        document.addEventListener('DOMContentLoaded', () => {
     @if (session('checked_attendance') === true)            
    if (!window.location.pathname.includes('/chats')) {
       // const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            function checkNewMessages() {
                fetch('/quick_search-users?q=', {
                    method: 'GET',
                })
                .then(res => res.json())
                .then(response => {
                    // Access the chat_info array from the response
                    const chatInfo = response.chat_info || [];

                    // Filter unread users
                    const unreadUsers = chatInfo.filter(u => u.unread_count > 0);

                    if (unreadUsers.length > 0) {
                        const firstUser = unreadUsers[0];
                        if (!firstUser.isMuted && !firstUser.isBlocked) {
                            const toastBody = document.getElementById('chatToastBody');
                            toastBody.innerHTML = `
                                <img src="images/${firstUser.profile_image}" 
                                    alt="Profile Image" 
                                    class="rounded-circle me-2" 
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                New message from ${firstUser.name}.<br/> Click to open chat.
                            `;
                            if(response.total_cnt===0){
                                $('#chat_cnt').html(response.total_cnt).addClass('d-none'); 
                            } else {
                               $('#chat_cnt').html(response.total_cnt).removeClass('d-none');
                            }
                           // $('#chat_cnt').html(response.total_cnt);
                            $('#chat_cnt').addClass('load_cnt');
                            const toastEl = document.getElementById('chatToast');
                            const toast = new bootstrap.Toast(toastEl, { autohide: false });
                            toast.show();
                                const toastEl2 = document.getElementById('notifyToast');
                                const toast2 = new bootstrap.Toast(toastEl2, { autohide: false });
                                toast2.hide();
                            // Redirect when toast body is clicked
                            toastBody.onclick = () => {
                                window.location.href = `/chats?user_name=${encodeURIComponent(firstUser.name)}`;
                            };
                        }
                    }
                })
                .catch(err => console.error('Error checking messages:', err));
            }


        // Run every 3 minutes
        setInterval(checkNewMessages, 120000);
    }
    @endif
if (!window.location.pathname.includes('/notifications')) {
    function checkNewNotifications() {
        fetch('/check_notifications', { method: 'GET' })
            .then(res => res.json())
            .then(response => {
                const notify_info = response.notify || [];
                if (notify_info.length > 0) {
                    const notification = notify_info[0]; // first unread notification

                    // Update toast body text
                    const toastBody = document.getElementById('notifyToastBody');
                    const notifyMessage = document.getElementById('notifyMessage');
                    notifyMessage.innerHTML = notification.message;

                    // Update badge count
                    $('#notificationCount').html(response.un_read_cnt);
                    if (response.un_read_cnt > 0) {
                        $('#notificationCount').addClass('load_cnt');
                    } else {
                        $('#notificationCount').removeClass('load_cnt');
                    }

                    // Show toast
                    const toastEl = document.getElementById('notifyToast');
                    const toast = new bootstrap.Toast(toastEl, { autohide: false });
                    toast.show();

                    // Hide chat toast if present
                    const toastEl2 = document.getElementById('chatToast');
                    if (toastEl2) {
                        const toast2 = new bootstrap.Toast(toastEl2, { autohide: false });
                        toast2.hide();
                    }
                    // Redirect when toast body text is clicked
                    document.getElementById('notifyMessage').onclick = () => {
                        window.location.href = '/notifications/view_notifications/' + notification.id;
                    };
                                        // Redirect when bell icon is clicked
                    document.getElementById('notifyBell').onclick = () => {
                        window.location.href = '/notifications/view_notifications/' + notification.id;
                    };
                    // Redirect when "New Notification" title is clicked
                    document.getElementById('notifyTitle').onclick = () => {
                        window.location.href = '/notifications/view_notifications/' + notification.id;
                    };

                    // Close button behavior
                    document.getElementById('notify_close').onclick = () => {
                        $.ajax({
                            url: '/notifications/make_read/' + notification.id,
                            type: 'GET',
                            success: function () {
                                toast.hide();
                            }
                        });
                    };
                }
            })
            .catch(err => console.error('Error checking notifications:', err));
    }
}
checkNewNotifications();
// Optionally poll every minute
setInterval(checkNewNotifications, 60000);

});
   

</script>