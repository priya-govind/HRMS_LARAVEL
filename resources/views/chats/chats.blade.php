@extends('layouts.app')
<script>
    //   var selectedUserId = @json($userId);
    //var selectedUserName = @json($userName);

  // const currentUserImage = @json(request()->user()?->image);
   //const currentUserId =@json(auth()->id());
</script>
<script>
    var csrfToken = "{{ csrf_token() }}"; 
</script>
@section('content')  
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
             <div class="page-header flex-wrap d-flex justify-content-between align-items-center">
            <h1><i class="mdi mdi-chat-processing menu-icon"></i> Chats</h1>
        </div>
<div class="position-fixed toast-position">
    @if (session('success'))
        <div class="toast custom-toast toast-success show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="mdi mdi-check-circle-outline me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="toast custom-toast toast-error show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>
<div class="row">
    <div class="col-md-4">
        <div class="card" style="border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);">
            <div class="card-body">
                <h5 class="card-title">Chats</h5>

                <div class="input-group mb-3" style="box-shadow: inset 2px 2px 6px #d1d9e6, inset -2px -2px 6px #ffffff; border-radius: 10px;">
                    <span class="input-group-text bg-white" style="border-radius: 10px 0 0 10px;">
                        <i class="mdi mdi-magnify"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search Contacts or Messages..." id="search-contacts" style="border-radius: 0 10px 10px 0; color: black; border: 1px solid gray">
                </div>

                <div class="d-flex justify-content-start align-items-center mb-3" style="gap: 70px; margin-left: 70px; font-size: 14px; color:rgb(72, 37, 153);">
                    <div id="allChatsTab" class="tab-header active" style="cursor: pointer; color:rgb(72, 37, 153);" onclick="switchChatView('all')">
                        <i class="mdi mdi-message-processing"></i> All messages
                    </div>
                    <div id="unreadChatsTab" class="tab-header" style="cursor: pointer; font-weight: normal; color:rgb(72, 37, 153);" onclick="switchChatView('unread')">
                         <i class="mdi mdi-message-minus"></i>  Unread messages
                    </div>
                </div>
                <ul class="list-group" id="search-results"></ul>
            </div>
        </div>
    </div>  
<div class="col-md-8">
<div class="card" style="
    border: none; 
    box-shadow: inset 2px 2px 6pxrgb(192, 189, 219), inset -2px -2px 6px #4599cd; 
    height: 750px; 
    display: flex; 
    flex-direction: column;
    background: -webkit-gradient(linear, left top, left bottom, from(#e6e5f2), to(#4599cd));
    background: linear-gradient(to bottom,rgb(192, 189, 219), #4599cd);">
    <div class="card-body flex-grow-1" style="overflow-y: auto;">
            <div id="chat-messages" class="chat-messages">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0">Chat Window</h5>
        </div>
        <p class="card-text"> <i class="mdi mdi-account-arrow-right-outline me-2"></i>Select users to have a conversation</p>
      </div>
    </div>
    <div id="reply-box" style="display: none;"></div>
    <div class="input-group p-3" style="border-top: 1px solid #ddd; align-items: center; position: relative;">
        <input type="text" id="message-input" class="form-control rounded-pill ps-5" placeholder="Type a message..." disabled style="position: relative;">
        <span style="position: absolute; left: 35px; top: 50%; transform: translateY(-50%); color: white; cursor: pointer; font-size: 20px; z-index: 2;">
            <i class="mdi mdi-microphone" style="color: gray"></i>
        </span>
        <button class="btn btn-default rounded-circle ms-2" id="send-button" disabled style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background-color: rgb(72, 37, 153); color:white">
            <i class="mdi mdi-send" style="font-size: 20px;"></i>
        </button>
        <div class="d-flex ms-3">
            <button class="btn btn-default rounded-circle" id="emoji-button" style="width: 45px; height: 45px; color: white; display: flex; align-items: center; justify-content: center;">
                <i class="mdi mdi-emoticon-happy" style="font-size: 20px;"></i>
            </button>
            <button class="btn btn-default rounded-circle ms-2" id="file-button" style="width: 45px; height: 45px; color: white; display: flex; align-items: center; justify-content: center;">
                <i class="mdi mdi-folder" style="font-size: 20px;"></i>
            </button>
            <input type="file" id="file-input" class="d-none" accept="image/*, .pdf, .docx, .txt">
            <!--<div class="dropdown ms-2">
                <button class="btn btn-default rounded-circle" style="width: 45px; height: 45px; color: white;" type="button" id="more-options-button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical" style="font-size: 20px;"></i>
                </button>
               <ul class="dropdown-menu" aria-labelledby="more-options-button">
                    <li><a class="dropdown-item" href="#" id="location-option"><i class="mdi mdi-map-marker me-2"></i>Location</a></li>
                    <li><a class="dropdown-item" href="#" id="gallery-option"><i class="mdi mdi-camera-image me-2"></i>Gallery</a></li>
                    <li><a class="dropdown-item" href="#" id="audio-option"><i class="mdi mdi-music-circle me-2"></i>Audio</a></li>
                    <li><a class="dropdown-item" href="#" id="document-option"><i class="mdi mdi-file-document me-2"></i>Document</a></li>
                    <li><a class="dropdown-item" href="#" id="camera-option"><i class="mdi mdi-camera me-2"></i>Camera</a></li>
                </ul> 
            </div>-->
        </div> 
    </div> 
                </div>
              </div>
            </div>
          </div>
          @endsection