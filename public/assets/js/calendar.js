let calendar; // Define globally so it's accessible from change handler

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: {
      url: '/calendar-events',
      method: 'GET',
      extraParams: function () {
        return {
          emp_id: $('#employee_id').val()
        };
      },
      failure: () => {
        alert('There was an error while fetching events!');
      }
    },
eventContent: function(arg) {
   const currentView = calendar.view.type;
  const checkIn = arg.event.extendedProps.checkin;
  const checkOut = arg.event.extendedProps.checkout;
  const dateStr = arg.event.startStr;
  const isToday = arg.event.extendedProps.is_today;
  
  const hasTasks = arg.event.extendedProps.has_tasks;
  // Only show button if both check-in and checkout are present
  const showButton = checkIn && checkOut && checkOut !== '-';

  let html = `<div class="fc-event-title">CheckIn ${checkIn}<br>Checkout ${checkOut}</div>`;
  //if (!isToday && checkIn && checkOut  && hasTasks ) {
  if (!isToday && checkIn && checkOut  && hasTasks ) {
    html += ` <button class="btn btn-sm btn-outline-info view-tasks-btn blinking" style="position:absolute;margin-top: 71px;height: 35px;" data-date="${dateStr}" style="margin-top:4px;">View Timesheet</button>`;
  } 
  if (isToday && (currentView != 'dayGridMonth' || currentView === 'timeGridDay')) {
    if(hasTasks){
      if ($('#user_id').val()==$('#employee_id').val()){
        html += `<button class="btn btn-sm btn-outline-primary go-timesheet-btn blinking" data-date="${dateStr}" style="margin-top:21px;">Edit Timesheet</button>`;
      } else {
         html += ` <button class="btn btn-sm btn-outline-info view-tasks-btn blinking" style="position:absolute;margin-top: 71px;height: 35px;" data-date="${dateStr}" style="margin-top:4px;">View Timesheet</button>`;
      }
      
    } else {
       if ($('#user_id').val()==$('#employee_id').val()){
    html += `<button class="btn btn-sm btn-outline-primary go-timesheet-btn blinking" data-date="${dateStr}" style="margin-top:21px;">Add Timesheet</button>`;
       }
    }
    
  }

  return { html };
},
 eventDidMount: function(info) {
  const currentView = calendar.view.type;
  //if (currentView === 'dayGridMonth') return; // 
  const btn = info.el.querySelector('.view-tasks-btn');
  if (btn) {
    const rawDate = btn.getAttribute('data-date');
    const date = rawDate.split('T')[0]; // Clean date
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      fetchTasksForDate(date);
    });
  }
  const goBtn = info.el.querySelector('.go-timesheet-btn');
  if (goBtn) {
    goBtn.addEventListener('click', function(e) {
      e.stopPropagation();         
     // window.location.href = '/timesheet_log'; 
      window.open('/timesheet_log', '_blank');

    });
  }
},

    // viewDidMount: function (arg) {
    //   if (arg.view.type.startsWith('timeGrid')) {
    //     const headerEls = document.querySelectorAll('.fc-col-header-cell');
    //     headerEls.forEach((el) => {
    //       const dateStr = el.getAttribute('data-date');
    //       if (dateStr && !el.querySelector('.view-tasks-btn')) {
    //         const button = document.createElement('button');
    //         button.innerText = 'View Tasks';
    //         button.className = 'view-tasks-btn';
    //         button.onclick = function () {
    //           fetchTasksForDate(dateStr);
    //         };
    //         el.appendChild(button);
    //       }
    //     });
    //   }
    // }
  });

  calendar.render();
});
function formatDateDMY(dateInput) {
  const dateObj = new Date(dateInput);
  if (isNaN(dateObj.getTime())) return '';
  const day = String(dateObj.getDate()).padStart(2, '0');
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const year = dateObj.getFullYear();
  return `${day}-${month}-${year}`;
}
function formatDateYMD(dateInput) {
  const dateObj = new Date(dateInput);
  if (isNaN(dateObj.getTime())) return '';
  const day = String(dateObj.getDate()).padStart(2, '0');
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const year = dateObj.getFullYear();
  return `${year}-${month}-${day}`;
}

function fetchTasksForDate(date) {
  $.ajax({
    url: '/timesheet_fetch',
    method: 'GET',
    data: {
      date: date,
      emp_id: $('#employee_id').val()
    },
    success: function (tasks) {
      let html = `<h5 class="mb-3">Tasks Done on <strong>${formatDateDMY(date)}</strong></h5>`;
      const curDate = formatDateYMD(date); // or YYYY-MM-DD depending on your backend
      const curdate_view=formatDateDMY(date);
const empId = $('#employee_id').val();
// Replace placeholders
const viewUrl = window.routes.viewTimesheet
    .replace('__DATE__', curDate)
    .replace('__USER__', empId);

html += `<div class="float-end">
               <a  class="btn btn-primary btn-sm" href="${viewUrl}" target="_blank" title="View Details">
              View Details
              </a> <button class="btn btn-primary btn-sm" id="exportBtn" data-id="${curdate_view}">
                Download as Excel
              </button>
             
            </div><br/><br/><br/>`;

      if (!tasks.length) {
        html += '<p class="text-muted">No tasks completed.</p>';
      } else {
        html += `
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
              <thead class="thead-dark">
                <tr>
                  <th>Day</th>
                  <th>Timings</th>
                  <th>Project</th>
                  <th>Module</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
        `;

        tasks.forEach(task => {
const fullDesc = task.description || '';
const shortDesc = fullDesc.length > 20 ? fullDesc.slice(0, 20) + '...' : fullDesc;

          html += `
            <tr>
              <td>${task.day}</td>
              <td>${task.timings}</td>
              <td>${task.project}</td>
              <td>${task.module.module_name}</td>`;

          html += `<td>${task.description}`;
          if (fullDesc.length > 20) {
            html += `<a href="${viewUrl}" class="desc-more" style="color:blue; text-decoration:underline;" target="_blank">Read more</a>`;
          }
           html += `</td>`;
          html += `</tr>`;
        });

        html += `</tbody></table></div>`;
      }
$('#taskModalLabel').text('Tasks Done');
      $('#taskModal .modal-body').html(html);
      const modal = new bootstrap.Modal(document.getElementById('taskModal'));
      modal.show();
    },
    error: function () {
      alert('Failed to fetch tasks.');
    }
  });
}
$(document).ready(function () {
  $('#drp_emp_id').change(function () {
    const selectedEmpId = $(this).val();
    $('#employee_id').val(selectedEmpId);
    if (calendar) calendar.refetchEvents();
  });

  $("input:radio[name=status_type]").change(function () {
    const stat = $(this).val();
    $('#drp_emp_id').prop('selectedIndex', 0);

    if (stat == 2) {
      $('#emp_stat').show();
    } else {
      $('#emp_stat').hide();
      $('#employee_id').val($('#user_id').val());
      if (calendar) calendar.refetchEvents();
    }
  });
  $(document).on('click', '#exportBtn', function (e) {
    e.preventDefault();
     var given_date = $(this).data('id');
    const statusType = $("input[name='status_type']:checked").val();
    
    let emp_id='';
     let emp_name='';
    if (statusType == 2) {
       emp_id=$('#drp_emp_id').val();
       emp_name = $('#drp_emp_id').find('option:selected').text();
    } 
    let url = '';
      if(emp_id!='' && emp_name!='Select Employee'){
        url = `${window.routes.timesheetExport}?GivenDate=${given_date}&emp_id=${emp_id}&emp_name=${emp_name}`;
      } else {
        url = `${window.routes.timesheetExport}?GivenDate=${given_date}`;
      }
    window.location.href = url;
});
});

