<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <a class="navbar-brand brand-logo" href="{{route('dashboard')}}"><img src="{{url('assets/images/fort_logo.png')}}" alt="logo" style="width:74%;height:53%;"/></a>
          <a class="navbar-brand brand-logo-mini" href="{{route('dashboard')}}"><img src="{{url('assets/images/fort_logo.png')}}" alt="logo" style="width:74%;height:53%;" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          {{-- <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button> --}}
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="nav-profile-img">
                  <img src="{{url('images/'.session('dp_image'))}}" alt="image">
                  <span class="availability-status online"></span>
                </div>
                <div class="nav-profile-text">
                  <p class="mb-1 text-white">{{ session('user_name')  }}</p>
                </div>
              </a>
              <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="{{ route('my_profile') }}">
                  <i class="mdi mdi-account me-2  text-primary  "></i> My Profile </a>
              @if(!in_array(session('role_id'),config('global.mgmt_team')))
                  {{-- <a class="dropdown-item" href="{{ route('attendance.applied_leaves')}}">
                  <i class="mdi mdi-calendar-account  text-primary  "></i>Applied Leaves </a> --}}
                <div class="dropdown-divider"></div> 
                <a class="dropdown-item" href="{{ route('timesheet_log')}}">
                  <span class="mdi mdi-clock-edit-outline text-primary"></span> TimeSheet </a>
                  <div class="dropdown-divider"></div>
              @endif
                <a class="dropdown-item" id="chkout_user">
                  <i class="mdi mdi-logout me-2 text-primary"></i> Checkout </a>
                  <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}">
                  <span class="mdi mdi-power  me-2 text-primary"></span> Signout </a> 
              </div>
            </li>
            <li class="nav-item d-none d-lg-block full-screen-link">
              <a class="nav-link">
                <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
              </a>
            </li>
          
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="mdi mdi-email-outline"></i>
                <span class="start-100 translate-middle badge rounded-circle bg-warning text-white fw-bold shadow-sm" id="chat_cnt">{{ $chat_cnt }}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                <h6 class="p-3 mb-0">Chat Messages</h6>
                @if ($chat_msg->isEmpty()) 
                <div class="dropdown-divider"></div>
                 <a class="dropdown-item preview-item">
                   <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <p class="text-gray mb-0"> No New Messages </p>
                   </div>
                 </a>
                @else
                  @foreach ($chat_msg as $msg )
                    <div class="dropdown-divider"></div>
                        <a class="dropdown-item preview-item" href="{{ url('chats?user_name='.$msg->sender->name) }}">
                          <div class="preview-thumbnail">
                            <img src="{{url('images/'.$msg->sender->image)}}" alt="image" class="profile-pic">
                          </div>
                          <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                            <h6 class="preview-subject ellipsis mb-1 font-weight-normal">{{ $msg->sender->name }} send you a message</h6>
                            <p class="text-gray mb-0"> 1 Minutes ago </p>
                          </div>
                        </a>
                    <div class="dropdown-divider"></div>
                  @endforeach
                @endif
              </div>
            </li>
            
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-bell-outline"></i>
<span class="start-100 translate-middle badge rounded-circle bg-danger text-white fw-bold shadow-sm" id="notificationCount" style="font-size: 0.75rem;">
  {{ $topbar_drp_cnt}}
    </span>              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                <h6 class="p-3 mb-0">Notifications</h6>
                <div class="dropdown-divider"></div>
                @if($topbar_drp->isEmpty())
                 <span class="dropdown-item preview-item">     
                <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                        <p class="text-gray ellipsis mb-0"> No Notifications </p>
                      </div>
                    </span>
               @else 
                    @foreach($topbar_drp as $tst)
                    <a class="dropdown-item preview-item" href="{{ route('notifications.view_notifications', ['id' => $tst['id']]) }}">
                      <div class="preview-thumbnail">
                        <div class="preview-icon bg-success">
                          <i class="mdi mdi-calendar"></i>
                        </div>
                      </div>
                      <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                        <h6 class="preview-subject font-weight-normal mb-1">{{ $tst['subject'] }}</h6>
                        <p class="text-gray ellipsis mb-0"> {{ strip_tags($tst['message']) }}  </p>
                      </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    @endforeach;
                    
                <h6 class="p-3 mb-0 text-center">
                  <a href="{{ route('notifications.alert_notifications')}}">See all notifications</a></h6>
                @endif

              </div>
            </li>
            <li class="nav-item nav-logout d-none d-lg-block">
              <a class="nav-link" href="{{ route('logout') }}">
                <i class="mdi mdi-power"></i>
              </a>
            </li>
          </ul>
          {{-- <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button> --}}
        </div>
      </nav>
