<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Datepair.js Time Range Picker</title>

  <!-- jQuery -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <!-- Bootstrap CSS & JS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

  <!-- Bootstrap Datepicker -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

  <!-- jQuery Timepicker -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>

  <!-- Datepair.js -->
  {{-- For time limiting during setting permission --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datepair.js/0.4.16/datepair.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datepair.js/0.4.16/jquery.datepair.min.js"></script>

  <style>
    body {
      padding: 40px;
    }
    .form-group {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Datepair.js Time Range Picker</h2>
    <div id="datepairExample">
      <div class="form-group">
        <label>Start Date</label>
        <input type="text" class="form-control date start" placeholder="Start Date">
      </div>
      <div class="form-group">
        <label>Start Time</label>
        <input type="text" class="form-control time start" placeholder="Start Time">
      </div>
        <input type="hidden" class="form-control date end" placeholder="End Date">
      <div class="form-group">
        <label>End Time</label>
        <input type="text" class="form-control time end" placeholder="End Time">
      </div>
    </div>
  </div>

  <script>
    $(function() {
      // Initialize datepickers
      $('#datepairExample .date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true
      });

      // Start time: between 09:00 AM and 03:00 PM
      $('.time.start').timepicker({
        showDuration: true,
        timeFormat: 'g:ia',
        step: 15,
        minTime: '9:00am',
        maxTime: '5:00pm'
      });

      // End time: initially full office hours
      $('.time.end').timepicker({
        showDuration: true,
        timeFormat: 'g:ia',
        step: 15,
        minTime: '9:00am',
        maxTime: '6:00pm'
      });

      // Initialize Datepair
      var container = document.getElementById('datepairExample');
      var datepair = new Datepair(container);

      // Update end time limits based on selected start time
      $('.time.start').on('changeTime', function () {
        var startTime = $(this).timepicker('getTime');

        if (startTime) {
          var minTime = new Date(startTime);
          var maxTime = new Date(startTime.getTime() + 2 * 60 * 60 * 1000); // +3 hours

          // Cap maxTime at 6:00 PM
          var sixPM = new Date();
          sixPM.setHours(18, 0, 0, 0);
          if (maxTime > sixPM) {
            maxTime = sixPM;
          }

          $('.time.end').timepicker('option', {
            minTime: minTime,
            maxTime: maxTime
          });

          // Reset end time if out of range
          var currentEnd = $('.time.end').timepicker('getTime');
          if (currentEnd < minTime || currentEnd > maxTime) {
            $('.time.end').timepicker('setTime', minTime);
          }
        }
      });
    });
  </script>

</body>
</html>
