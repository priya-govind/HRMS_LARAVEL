<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Fortgrid Admin</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{url('assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{url('assets/vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{url('assets/vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{url('assets/vendors/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{url('assets/css/all.min.css')}}">
    

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{url('assets/vendors/font-awesome/css/font-awesome.min.css')}}" />
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}">
    <link rel="stylesheet" href="{{url('assets/css/chatbot.css')}}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{url('assets/images/favicon.png')}}" /> 
    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
      @if (isset($LoadDatatables) && $LoadDatatables) 
      <!-- DataTables CSS -->
      
      <link rel="stylesheet" href="{{ asset('assets/css/jquery.dataTables.min.css') }}">
       <!-- DataTables JS -->
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    @endif
    
    
    @if (isset($LoadMultiselectJS) && $LoadMultiselectJS) 
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.multiselect.css') }}">
    <script src="{{ asset('assets/js/jquery.multiselect.js') }}"></script>
    @endif

 
    @if ((session('checked_attendance') === false) || (isset($LoadDateTimepicker) && $LoadDateTimepicker) )
    {{-- <script src="{{ asset('assets/js/jquery-ui.js') }}"></script> --}}
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui-timepicker-addon.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui-timepicker-addon.min.css')}}">
   
@endif
<!-- Bootstrap CSS -->
 <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">


@if (isset($loadSelect2JS) && $loadSelect2JS)  
<!-- timesheet page -->
<!-- Select2 CSS -->
<link href="{{ asset('assets/css/select2.min.css')}}" rel="stylesheet" />
<!-- Select2 JS (after jQuery and Bootstrap) -->
<script src="{{ asset('assets/js/select2.min.js')}}"></script>
<!-- 
this script causing profile,checkout dropdown issue so commented for time sheet page i used
<script src="{{ asset('assets/js/bootstrap.bundle.min.js')}}"></script> --> 
@endif
<script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
@if (isset($LoadMultiselectchkbox) && $LoadMultiselectchkbox)  
<link href="{{ asset('assets/css/coreui.min.css')}}" rel="stylesheet">
<script defer src="{{ asset('assets/js/coreui.bundle.min.js') }}"></script>   
@endif
</head>