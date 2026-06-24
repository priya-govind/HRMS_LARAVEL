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
                    Expense Tracker
                </div>
                    <div class="card-body">
              
                         <br/>
                         <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                            <p><strong>Available Balance:</strong> ₹{{ number_format($balance, 2) }}</p>
    <hr>
                    <h4>Transaction History</h4> 
                     <div class="mb-4 pb-3"> 
            <h6 class="mb-3"> Filter Expenses</h6>
            {{-- <pre>{{ print_r(request()->all(), true) }}</pre> --}}
             <form name="filter_expenses" method="get" action="{{ route('transactions.index') }}" id="filter_expenses">
                <div class="card" style="padding:40px;">
                <div class="row g-3">
                        <div class="col-md-1">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}" readonly>
                        </div>
                        <div class="col-md-1">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ request('end_date') }}" readonly>
                        </div>
                        
                          <div class="col-md-2">
                            <label for="work_mode" class="form-label">Expense Type</label>
                            <select class="form-select" id="exp_type" name="exp_type">
                                <option value="">Select</option>
                                <option value="credit" {{ request('exp_type') == 'credit' ? 'selected' : '' }}>Cedit </option>
                                <option value="debit" {{ request('exp_type') == 'debit' ? 'selected' : '' }}>Debit </option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="filter" id="filter" class="btn btn-primary w-100 download_punch_attendance">
                                 <i class="fa-solid fa-filter"></i>Filter
                            </button>
                        </div>
                         <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="export_expense" id="export_expense" value="export_expense"  class="btn btn-primary w-100 download_punch_attendance">
                                Export to Excel<i class="fa fa-download ms-1"></i>
                            </button>
                        </div>
                </div>
                </div>
             </form>
           </div>
           <style>
  .expensetable th {
    font-size: 20px;
  }
  .expensetable td {
    font-size: 16px;
  }
</style>

           <div class="float-end">
                    <button type="button" id="addexpenseButton" class="btn btn-primary btn-sm" style="margin: 0 0 20% 0;">+Add New Transactions</button>
            </div>
                    <table  class="display table table-bordered expensetable" style="border:1px solid grey;">
                        <thead>
                            <tr>
                                <th>Date</th><th>Expenses</th><th>Credit</th><th>Debit</th><th>Current Available Balance</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($transactions->isEmpty())
                            <tr>
                                <td colspan="6" align="center">No Rows Found. </td>
                            </tr>
                            @else
                                @foreach($transactions as $txn)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse( $txn->transaction_date)->format('d-M Y') }}</td>
                                        <td>
                                            @if($txn->transaction_type === 'debit')
                                               
                                                    @foreach($txn->items as $item)
                                                        <span style="padding: 0 0 0 5%;"> {{ $item->expenseItem->expense_type_name }}</span><br/>
                                                    @endforeach
                                                
                                            @elseif($txn->transaction_type === 'credit')
                                                <span style="padding: 0 0 0 5%;">Credit</span>
                                             @elseif($txn->transaction_type === 'reversal')
                                                <span style="padding: 0 0 0 5%;">Reversal Amount</span>
                                            @else
                                                 @foreach($txn->items as $item)
                                                        <span style="padding: 0 0 0 5%;"> {{ $item->expenseItem->expense_type_name }}</span><br/>
                                                    @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if($txn->transaction_type === 'credit' || $txn->transaction_type === 'reversal')
                                                ₹{{ number_format($txn->amount, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($txn->transaction_type === 'debit')
                                            ₹{{ number_format($txn->amount, 2) }}
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($txn->available_amt) }}</td>
                                        <td>
                                            @if(!empty($txn->bill_refer))
                                            <a class="btn btn-info" id="viewImageBtn" data-id="{{ $txn->id }}"  data-image="/bills/{{ $txn->bill_refer }}"><i class="fa-regular fa-image" ></i></a> |  
                                            @endif
                                            @if(($txn->last_entry==1))
                                            <a class="btn btn-success editexpenseButton" data-id="{{ $txn->id }}" data-type="new"><i class="fa fa-edit" title="active"></i></a>
                                            @else
                                                <a class="btn btn-secondary editexpenseButton" data-id="{{ $txn->id }}" data-type="old"><i class="fa fa-eye" title="Editing not allowed"></i></a>
                                            @endif
                                             @if($txn->transaction_type === 'debit' &&  \Carbon\Carbon::parse($txn->transaction_date)->isSameDay(\Carbon\Carbon::today()))
                                                |
                                                <a class="btn btn-danger delete-expense-btn" data-id="{{ $txn->id }}"><i class="fa fa-trash-o" ></i></a> 
                                                @endif
                                        </td>
                                        
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
            
                    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content logic_cont">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="dataModalLabel"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                     <form method="POST" enctype="multipart/form-data" id="expenseForm">
                                        @csrf
                                        <input type="hidden" id="recordId" name="id">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Transaction Type:</label>
                                           <select name="transaction_type" id="transaction_type" class="frm_ctrl_select">
                                                <option value="credit">Credit</option>
                                                <option value="debit">Debit</option>
                                                <option value="reversal" style="display: none;">Reversal </option>
                                            </select>
                                        </div>
                                         <div id="debit-section" style="display:none;">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Select Expense Items:</label>&nbsp;&nbsp;
                                            <select multiple name="items[][expense_item_id]"   class="form-control">
                                                @foreach($expenseItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->expense_type_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                         </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Enter the Amount:</label>&nbsp;&nbsp;
                                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter amount">
                                            
                                        </div>
                                         <div id="debit-section1" style="display:none;">
                                         <div class="mb-3">
                                            <label for="work_mode" class="form-label">Payment Type</label>
                                            <select class="form-select" id="payment_type" name="payment_type">
                                                <option value="">Select</option>
                                                <option value="cash" {{ request('payment_type') == 'cash' ? 'selected' : '' }}>Cash </option>
                                                <option value="gpay" {{ request('payment_type') == 'gpay' ? 'selected' : '' }}>G-pay </option>
                                                <option value="card" {{ request('payment_type') == 'card' ? 'selected' : '' }}>Card </option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="work_mode" class="form-label">Upload Bill</label>
                                        <input type="file" name="bill_refer" id="bill_refer">
                                        <div id="bill-preview" class="mb-3" style="display: none;">
                                        <label class="form-label">Uploaded Bill:</label><br>
                                        <img id="bill-image" src="" alt="Bill Image" style="max-height: 100px;"><br>
                                        {{-- <button type="button" class="btn btn-danger btn-sm mt-2" id="delete-bill">Delete Bill</button> --}}
                                    </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Remarks:</label>&nbsp;&nbsp;
                                            <textarea name="remarks" id="remarks" placeholder="Remarks (optional)" class="form-control"></textarea>
                                        </div>
                                         </div>
                                  
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Transaction Date:</label>&nbsp;&nbsp;
                                            <input type="date" name="transaction_date" id="transaction_date" class="form-control" max="{{ \Carbon\Carbon::now()->toDateString() }}" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="saveBtn">Add</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> 
                      <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this item?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
          </div>
          <!-- Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-body text-center">

                    <!-- Zoom buttons -->
                    <div class="btn-group mb-2">
                    <button class="btn btn-secondary" id="zoomInBtn">+</button>
                    <button class="btn btn-secondary" id="zoomOutBtn">-</button>
                    </div>

                    <!-- Image -->
                    <div id="imageWrapper" style="overflow:auto; max-height:500px;">
                    <img id="popupImage" src="" alt="Bill Image" class="mb-3 d-none" width="50%" height="50%">
                    </div>

                    <!-- PDF -->
                    <div id="pdfWrapper" style="overflow:auto; max-height:500px;">
                    <canvas id="popupPdf" style="display:none; border:1px solid #ccc;"></canvas>
                    </div>

                    <!-- Email input -->
                    <div class="mb-3">
                    <input type="email" id="senderEmail" class="form-control" placeholder="Enter your email address">
                    <div class="invalid-feedback"></div>
                    </div>
                    <button type="button" class="btn btn-primary" id="mailImageBtn">Send Image</button>
                </div>
                </div>
            </div>
            </div>
            <script src="{{ asset('assets/js/pdf_image/pdf.min.js') }}"></script>
            <script>
                 pdfjsLib.GlobalWorkerOptions.workerSrc = "/assets/js/pdf_image/pdf.worker.min.js";
            </script>
            <script src="{{ asset('assets/js/panzoom.min.js') }}"></script>
            <script src="{{ asset('assets/js/expense.js') }}"></script>
               <script src="{{ asset('assets/js/work_mode.js') }}"></script>
               <script>
                function submitExpenseForm() {
                    const formData = new FormData($("#expenseForm")[0]);
                    const recordId = $("#recordId").val();
                    const url = recordId ? `/transactions/${recordId}` : `{{ route('transactions.store') }}`;
                    const method = recordId ? "POST" : "POST";

                    if (recordId) {
                        formData.append('_method', 'PUT');
                    }

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            alert(response.message);
                            const modal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                            modal.hide();
                            window.location.reload();
                            // Refresh table or reset form
                        },
                        error: function (xhr) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, value) {
                                const input = $(`[name="${key}"]`);
                                input.addClass("is-invalid");
                                input.after(`<div class="invalid-feedback">${value[0]}</div>`);
                            });
                        }
                    });
                }
                </script>
          </div>
          @endsection
         


