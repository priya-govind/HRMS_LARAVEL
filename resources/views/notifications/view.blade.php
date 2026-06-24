@extends('layouts.app')
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
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    Notification Details
                </div>
                <div class="float-end">
                    <a href="{{ route('notifications.alert_notifications') }}" class="btn btn-primary btn-sm">Back</a>
                </div>
              
            </div>
                    <div class="card-body">
                        <div class="form-group">
                    <table class="display table table-bordered">
                     
                                <tr>
                                    <td> Subject</td>
                                    <td>{{ $notify->subject }}</td>
                                </tr>
                                <tr>
                                    <td>Message</td>
                                    <td>{!! $notify->message !!}
                                        </td>
                                </tr>
                                   <tr>
                                    <td>Sender</td>
                                    <td>{!! nl2br($notify->sender_name) !!}</td>
                                </tr>
                                <tbody>
                                  
                                </tbody>
                        </table>
                        </div>
                    </div>
                </div>
          </div>
          @endsection
         