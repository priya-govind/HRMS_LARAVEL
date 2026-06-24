<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="earlyCheckoutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Early Checkout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="checkout_form" name="checkout_form">
      <div class="modal-body">
          <div id="success-message3" class="alert alert-success"  role="alert"  style="display: none;"></div>
          <div id="error-message3" class="alert alert-danger" style="display: none;"></div>
        <p class="text-black">Complete the Time Sheet to Checkout. If You apply for Permission or leave Click "Yes" to Proceed Further...</p>
              <div id="permit_div" class="m-3 text-center">
                  <button class="btn m-2 btn-success show_permit_popup" value="yes">Yes</button>
                  <button class="btn m-2 btn-danger show_permit_popup" value="no">No</button>
              </div>
               <input type="hidden" id="redirect_url" name="redirect_url">
              <div id="permit_form" class="d-none">
                    <div class="m-3">
                        <label for="name" class="form-label">LeaveType:</label>&nbsp;&nbsp;
                                      <input type="radio" class="form-check-input leave_type" name="leave_type" value="1" required=""> Leave
                                      <input type="radio" class="form-check-input leave_type" name="leave_type" value="2" required=""> Permission
                    </div>
                    <div class="m-3">
                        <label for="earlyReasonInput">Please provide a reason for early checkout:</label>
                            <textarea id="earlyReasonInput" name="earlyReasonInput" class="form-control" rows="3"></textarea>
                    </div>  
              </div>
      </div>
      <div class="modal-footer d-none text-end" id="show_footer">
        <button type="submit" id="submitEarlyReason" class="btn btn-primary">Submit</button>
      </div>
    </form>
    </div>
  </div>
</div>