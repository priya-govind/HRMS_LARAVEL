const times = [
  "9:00am - 10:00am", "10:00am - 11:00am", "11:15am - 12:00pm",
  "12:00pm - 1:00pm", "1:30pm - 2:00pm", "2:00pm - 3:00pm",
  "3:00pm - 4:00pm", "4:15pm - 5:00pm", "5:00pm - 6:00pm"
];

function convertTo24Hour(timeStr) {
  const date = new Date(`1970-01-01T${timeStr}`);
  if (isNaN(date)) {
    const [time, modifier] = timeStr.toLowerCase().split(/(am|pm)/);
    let [hours, minutes] = time.trim().split(':').map(Number);
    if (modifier === 'pm' && hours < 12) hours += 12;
    if (modifier === 'am' && hours === 12) hours = 0;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;
  }
  return date.toTimeString().slice(0, 8);
}

function buildForm() {
  const container = $("#formContainer");
  const checkIn = convertTo24Hour(userCheckInTime); // e.g., "09:30:00"
  const checkInDate = new Date(`1970-01-01T${checkIn}`);
  let flag = 0;

  times.forEach((label, index) => {
    const id = `slot${index}`;
    let [from, to] = label.split(' - ').map(t => t.trim());
    let from_time = convertTo24Hour(from);
    const to_time = convertTo24Hour(to);

    const fromDate = new Date(`1970-01-01T${from_time}`);
    const toDate = new Date(`1970-01-01T${to_time}`);

    let displayLabel = label;

    // Adjust the slot if check-in falls within it
    if (fromDate <= checkInDate && checkInDate < toDate) {
      from_time = checkIn;
      const adjustedFrom = formatTo12Hour(from_time);
      displayLabel = `${adjustedFrom} - ${to}`;
    }

    const isDisabled = false;

   // if (!isDisabled) flag++;

    const saved = savedSlots.find(slot =>
      slot.from_time === from_time && slot.to_time === to_time
    );

    const html = `
      <div class="timeslot" id="${id}">
        <label>${displayLabel}</label>
        <input type="hidden" name="from_time[${index}]" value="${from_time}" data-index="${index}">
        <input type="hidden" name="to_time[${index}]" value="${to_time}" data-index="${index}">
        <label>Project ID</label>
        <select class="projectSelect" name="project_id[${index}]" data-index="${index}" data-selected="${saved?.project_id || ''}" ${isDisabled ? 'disabled' : ''}></select>
        <label>Module</label>
        <input type="text" name="module[${index}]" value="${saved?.module || ''}" data-index="${index}" ${isDisabled ? 'disabled' : ''}>
        <label>Description</label>
        <textarea name="description[${index}]" data-index="${index}" ${isDisabled ? 'disabled' : ''}>${saved?.description || ''}</textarea>
      </div>
    `;
    container.append(html);
  });

  container.append(`<input type="hidden" id="slotcnt" name="slotcnt" value="${flag}">`);
}



function initializeSelect2() {
  $(".projectSelect").each(function () {
    const $select = $(this);
    const selectedId = $select.data('selected');

    $select.select2({
      placeholder: "Search for a project",
      ajax: {
        url: "/get_projects_all",
        dataType: "json",
        delay: 250,
        processResults: function (data) {
          return {
            results: data.map(p => ({
              id: p.id,
              text: p.proj_name
            }))
          };
        },
        cache: true
      }
    });

    if (selectedId) {
      $.ajax({
        type: 'GET',
        url: `/get_projects_id/${selectedId}`,
        success: function (data) {
          const option = new Option(data.proj_name, data.id, true, true);
          $select.append(option).trigger('change');
        }
      });
    }
  });
}

function attachAutoSaveListeners() {
  $('#formContainer').on('change keyup', 'input, textarea, select', function () {
    if ($(this).is(':disabled')) return;
    const index = $(this).data('index');

    const from_time = $(`input[name="from_time[${index}]"]`).val();
    const to_time = $(`input[name="to_time[${index}]"]`).val();
    const project_id = $(`select[name="project_id[${index}]"]`).val();
    const module = $(`input[name="module[${index}]"]`).val();
    const description = $(`textarea[name="description[${index}]"]`).val();

    $.ajax({
      url: '/save-timeslot',
      method: 'POST',
      data: {
        from_time,
        to_time,
        project_id,
        module,
        description,
        _token: $('meta[name="csrf-token"]').attr('content'),
        slotcnt:$('#slotcnt').val(),
      },
      success: function () {
        console.log(`Slot ${index + 1} saved.`);
      },
      error: function () {
        console.error(`Error saving slot ${index + 1}`);
      }
    });
  });
}

function applyTimesheetValidation() {
  const rules = {};

  $(".timeslot").each(function (index) {
    const isDisabled = $(this).find("select").is(":disabled");
    if (!isDisabled) {
      rules[`project_id[${index}]`] = { required: true };
      rules[`module[${index}]`] = { required: true };
      rules[`description[${index}]`] = { required: true };
    }
  });

  $("#timesheetForm").validate({
    rules,
    errorPlacement: function (error, element) {
      error.insertAfter(element);
    },
    submitHandler: function () {
      submitForm();
    }
  });
}

function submitForm() {
  const url = '/update_slotcnt/'+$('#slotcnt').val();
  const modalElement = document.getElementById('messageModal');
  const msg = "TimeSheet gets AutoSaved Successfully.";
  callajax_return(url, modalElement);
}

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

$(document).ready(() => {
  buildForm();
  initializeSelect2();
  attachAutoSaveListeners();
  setTimeout(() => {
    applyTimesheetValidation();
  }, 300);
});
