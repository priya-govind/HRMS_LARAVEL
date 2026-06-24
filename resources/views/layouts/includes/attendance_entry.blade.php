  <!-- @if(session('irregular_chkout') === 1)
        <div class="modal fade" id="IrregularChkoutModal" tabindex="-1" aria-labelledby="IrregularChkoutModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="IrregularChkoutModalLabel">Irregular Checkout Remainder</h5>
                     
                    </div>
                    <form id="irregularchkoutForm" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                      <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <div class="modal-body">
                        <span style="color:red;"><b>Note: 
                          <ul>
                            <li>You have not checked out properly on your last working day.</li>
                           @if(session('incomplete_timesheet') == 1)
                            <li>Please First Complete Timesheet and checkout  to Proceed Further..</li>
                            @else
                            <li>Please First Checkout to Proceed Further..</li>
                            @endif
                          </ul>
                        <br/>
                        Your Last Checkin Date: <b>{{ session('last_checkin_dt') }}</b></span>
                      <br/><br/>
                      @if(session('incomplete_timesheet') == 1)
                       <div class="mb-3">
                          <label class="form-label">Worked For:</label>
                          <select name="worked_period" id="worked_period" class="form-control">
                            <option>Select</option>
                            <option value="1">Half Day</option>
                            <option value="2">Full Day</option>
                          </select>
                        </div>
                      @endif
                        <div class="mb-3">
                          <label class="form-label">Check Out time:</label>
                          <input type="text" id="chkoutTime" name="chkoutTime" class="form-control" readonly="true">
                          <input type="hidden" id="last_checkin_dt" name="last_checkin_dt" readonly="true" aria-invalid="false" value="{{ session('last_checkin_dt') }}">
                          <input type="hidden" id="last_checkin_time" name="last_checkin_time" readonly="true" aria-invalid="false"  value="{{ session('last_checkin_time') }}">
                          <input type="hidden" id="waiver_set" name="waiver_set"  value="{{ session('waiver_set') }}">
                      </div>
                      <div class="mb-3" id="reason_chkout" @if(session('waiver_set') == true) style="display:none;" @endif>
                          <label for="comments" class="form-label">Comments</label>
                          <textarea name="chkout_reason" id="chkout_reason" class="form-control"></textarea>
                      </div>
                  </div>
                    <div class="modal-footer">
                    <img class="sender_load" src="{{url('assets/images/small_load.gif')}}" style="margin: 0 25% 0 0;display:none;"/>
                    <button type="submit" id="chkoutAttend" class="btn btn-primary">Confirm</button>
                  </div>
                     </form>
                     
                </div>
              </div>
             </div>
    @endif  && session('irregular_chkout') === 0-->
            @if (session('checked_attendance') === false)
            <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Check In </h5>
                  </div>
                  <div class="modal-body">
                    <form id="statusForm" method="POST" enctype="multipart/form-data">
                       @csrf
                      <div id="success-message" class="alert alert-success" role="alert" style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                        <div class="mb-3">
                          <label class="form-label">Check In time:</label>
                          <input type="text" id="chkinDate" name="chkinDate" class="form-control" readonly="true">
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Working Mode</label>
                          <select class="form-select" id="working_mode" name="working_mode" required>
                            <option value="">-- Choose --</option>
                            @foreach($work_mode as $mode)
                              <option value="{{ $mode->id }}"> {{ $mode->work_mode_name }}</option>
                            @endforeach
                          </select>
                        </div>
                         <div class="mb-3">
                           <input type="checkbox" name="sys_prob" id="sys_prob" value="1"> <label class="form-label">System Problem</label>
                         </div>
                        <div class="mb-3" id="reason_dv" style="display:none;">
                          <label for="comments" class="form-label">Comments</label>
                          <textarea name="comments" id="comments" class="form-control"></textarea>
                        </div>

                         <div class="mb-3">
                          <span style="color:red;"><b>Note: If CheckIn time is lesser than current time or <br/>Employees who login after 9:15 AM should enter comments compulsory.</b></span>
                        </div>
                  </div>
                  <div class="modal-footer">
                    <img class="sender_load" src="{{url('assets/images/small_load.gif')}}" style="margin: 0 25% 0 0;display:none;"/>
                    <button type="submit" id="statusAttend" class="btn btn-primary">Confirm</button>
                  </div>
                   </form>
                </div>
              </div>
            </div>
            @endif