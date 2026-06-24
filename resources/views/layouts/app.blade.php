<html lang="en">
@include('layouts.includes.header')
@if (isset($LoadBarChart) && $LoadBarChart == true)
    <script src="{{ url('assets/js/fusion_chart/fusioncharts.js') }}"></script>
    <script src="{{ url('assets/js/fusion_chart/fusioncharts.theme.fusion.js') }}"></script>
    {{-- https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js
https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js --}}
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="current-user-image" content="{{ asset('images/' . request()->user()?->image) }}">

<body>
    <div class="container-scroller">


        @yield('content')

        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="chatToast" class="toast border-0 bg-dark" role="alert" aria-live="assertive" aria-atomic="true">

                <!-- Card-style header -->
                <div class="toast-header notify-blink text-white">
                    <i class="fa-solid fa-envelope fa-beat"></i>
                    &nbsp;&nbsp;<strong class="me-auto">New Message</strong>
                    <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>

                <!-- Toast body -->
                <div class="toast-body d-flex align-items-center text-white " id="chatToastBody">
                    <span>New message received! Click to open chat.</span>
                </div>
            </div>
        </div>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="notifyToast" class="toast border-0 bg-dark" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header notify-blink text-white" id="notifyToastHeader">
                    <!-- Bell icon gets its own ID -->
                    <span id="notifyBell" class="bell-animation fa-solid fa-bell text-warning"></span>
                    &nbsp;&nbsp;<strong id="notifyTitle" class="me-auto">New Notification</strong>
                    <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast"
                        id="notify_close" aria-label="Close"></button>
                </div>
                <div class="toast-body d-flex align-items-center text-white" id="notifyToastBody">
                    <span id="notifyMessage">New message received! Click to open chat.</span>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        @include('layouts.includes.footer')
        <!-- partial -->
    </div>
    <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ url('assets/vendors/js/vendor.bundle.base.js') }}"></script>


    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- <script src="{{ url('assets/vendors/chart.js/chart.umd.js') }}"></script> -->

    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ url('assets/js/off-canvas.js') }}"></script>
    <script src="{{ url('assets/js/misc.js') }}"></script>
    <script src="{{ url('assets/js/settings.js') }}"></script>
    <!-- <script src="{{ url('assets/js/todolist.js') }}"></script>
    <script src="{{ url('assets/js/jquery.cookie.js') }}"></script> -->
    <!-- <script src="{{ asset('js/custom_scripts.js') }}"></script> -->
    <!-- <script src="{{ url('assets/js/file-upload.js') }}"></script> -->

    <!-- <script src="{{ url('assets/js/typeahead.js') }}"></script> -->
    <!-- <script src="{{ url('assets/js/select2.js') }}"></script>commented as of no use -->

    <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
    <!-- Your custom script -->
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var currentPath = window.location.pathname;
            document.querySelectorAll('.nav-link').forEach(function(link) {
                // Remove ALL existing 'active' classes first
                if (link.classList.contains('active')) {
                    link.classList.remove('active');
                }
                // Add the 'active' class only if the href matches
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('bolder');
                }
                $('li.nav-item.active ul.sub-menu li.nav-item > a.nav-link')
                    .addClass('highlight-submenu');


            });
        });
        //let url_path_name=get_uri_segment(3);
        //console.log(url_path_name);
        var jq = $.noConflict();
        //if(url_path_name=='dashboard' || url_path_name=='chats'){
        const currentUserImage = @json(request()->user()?->image);
        const currentUserId = @json(auth()->id());
        //}
        @if (isset($LoadBarChart) && $LoadBarChart == true)
            document.addEventListener("DOMContentLoaded", function() {
                // Find the first nav-link inside your project tabs
                var firstTab = document.querySelector('#project-tabs .nav-link');
                if (firstTab) {
                    firstTab.click();
                }
            });
        @endif
    </script>
    @if (isset($loadChat) && !empty($loadChat))
        <script src="{{ asset('assets/js/chats.js') }}"></script>
    @endif

</body>

</html>
