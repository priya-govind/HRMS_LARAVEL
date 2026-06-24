<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ExpenseItems;
use App\Models\TransactionAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpenseExport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\ImageMail;
use Illuminate\Support\Facades\Log;



use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
       // dd($request);
        $balance = Transaction::latest()->pluck('available_amt')->first();

            // Step 1: Check start of month
                 $today = now();
                // $isStartOfMonth = $today->day == 1; // or use custom logic

                //  Step 2: Check if entries exist for current month
               $hasEntries = Transaction::whereMonth('created_at', $today->month)
                                        ->whereYear('created_at', $today->year)
                                        ->exists();

                //  Step 3: Insert default if none
                if (!$hasEntries) {
                  $current_trans_id=   Transaction::create([
                        'amount' =>'0.00',
                        'available_amt' => $balance,
                        'transaction_date' => now(),
                    ]);
                    
                    TransactionItem::create([
                        'transaction_id' => $current_trans_id->id,
                        'expense_item_id' =>  9
                    ]);
                   
                }
        //Transaction::sum(DB::raw("CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END"));
        $sub_query = Transaction::with('items.expenseItem')->where('is_deleted', false);
            if(!empty($request->start_date) && !empty($request->end_date)){
                $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
                $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
                 $sub_query->whereBetween('transaction_date', [$start_date, $end_date]);
            } else {
                $sub_query->whereMonth('transaction_date', Carbon::now()->month);
                $sub_query->whereYear('transaction_date', Carbon::now()->year);
            }
             if(!empty($request->exp_type) ){
                 $sub_query->where('transaction_type',$request->exp_type);
             }
        $transactions=$sub_query->orderBy('transaction_date', 'asc')->get();

        $firstDate = $transactions->min('transaction_date');
        $startDate = Carbon::parse($firstDate);
        $month = $startDate->format('M');



        if(!empty($request->export_expense)){
                     if ($transactions->isEmpty()) {
            return redirect()->route('expenses.index')->withError('No Expense Found for the selected criteria.');
        } else {
            $filters = $request->only(['start_date', 'end_date', 'exp_type']);
            //dd($filters);
            return Excel::download(new ExpenseExport($filters), $month.' Month Expense Report.xlsx');
        }
        } else{
         $expenseItems = ExpenseItems::where('expense_type_status', config('global.active_status'))->get();
        $LoadMultiselectchkbox=true;
         $LoadDateTimepicker=true;
        return view('expenses.index', compact('transactions', 'expenseItems', 'balance','LoadMultiselectchkbox','LoadDateTimepicker'));
        }  
    }

    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
        'transaction_type' => 'required|in:credit,debit',
        'amount' => 'required_if:transaction_type,credit|nullable|numeric',
        'transaction_date' => 'required|date',
        'items' => 'required_if:transaction_type,debit|array',
        'items.*.expense_item_id' => 'required|exists:expense_items,id',
        'amount' => 'required',
        'payment_type' => 'required_if:transaction_type,debit',
    ]);

    if ($validator->fails()) {
        dd($validator->errors()); // Debug: dumps errors and stops execution
        // Or log them:
        // \Log::error($validator->errors()->toJson());
        // return redirect()->back()->withErrors($validator)->withInput();
    }
    
        $amt = Transaction::latest()->first();
        $last_avail_balance = $amt ? $amt->available_amt : 0;
        if($request->transaction_type=='credit'){
        $present_balance=$last_avail_balance+$request->amount;
        } else {
        $present_balance=$last_avail_balance-$request->amount;
        }
        $imageName =''; 
        if ($request->hasFile('bill_refer')) {
                $file = $request->file('bill_refer');
                $imageName = time() . '.' . $file->extension();
                $file->move(public_path('bills'), $imageName);
            }
                // Reset last_entry
        Transaction::where('last_entry', 1)->update(['last_entry' => 0]);
        
        $transaction = Transaction::create([
            'transaction_type' => $request->transaction_type,
            'amount' =>$request->amount,
            'transaction_date' => $request->transaction_date,
            'remarks' => $request->remarks,
            'available_amt' => $present_balance,
            'payment_type' => $request->payment_type,
            'bill_refer' => $imageName,
            'last_entry' => 1,
        ]);
        if ($request->transaction_type === 'debit') {
            foreach ($request->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'expense_item_id' => $item['expense_item_id'],
                ]);
            }
        }
                return response()->json(['success' => true, 'message' => 'New Transaction Added successfully.']);
    }
public function check_amount(Request $request)
{
    $lastTxn = Transaction::where('last_entry', 1)->first();
    $last_avail_balance = $lastTxn ? $lastTxn->available_amt : 0;

    // Adjust balance if editing an existing debit transaction
    if ($request->filled('id')) {
        $originalTxn = Transaction::find($request->id);
        if ($originalTxn && $originalTxn->transaction_type === 'debit') {
            $last_avail_balance += $originalTxn->amount;
        }
    }

    $flag = ($request->amount > $last_avail_balance && $request->trans_type === 'debit') ? 1 : 0;

    return response()->json(['flag' => $flag]);
}
    public function edit($id)
    {
        $trans = Transaction::with('items')->find($id);
        $selectedItems = $trans->items->pluck('expense_item_id')->toArray();
        return response()->json([
            'id' => $trans->id,
            'transaction_type' => $trans->transaction_type,
            'amount' => $trans->amount,
            'transaction_date' => $trans->transaction_date,
            'payment_type' => $trans->payment_type,
            'remarks' => $trans->remarks,
            'bill_refer' => $trans->bill_refer,
            'items' => $selectedItems,
        ]);
    }
   

// public function update(Request $request, $id)
// {
//     $validator = Validator::make($request->all(), [
//         'transaction_type' => 'required|in:credit,debit',
//         'amount' => 'required|numeric',
//         'transaction_date' => 'required|date',
//         'items' => 'required_if:transaction_type,debit|array',
//         'items.*.expense_item_id' => 'required|exists:expense_items,id',
//         'payment_type' => 'required_if:transaction_type,debit',
//     ]);

//     if ($validator->fails()) {
//         return redirect()->back()->withErrors($validator)->withInput();
//     }

//     $transaction = Transaction::findOrFail($id);

//     // Calculate new available balance
//     $previousTransaction = Transaction::where('id', '<', $transaction->id)->latest()->first();
//     $previousBalance = $previousTransaction ? $previousTransaction->available_amt : 0;
//     $newBalance = $request->transaction_type === 'credit'
//         ? $previousBalance + $request->amount
//         : $previousBalance - $request->amount;

//     // Handle bill deletion
//     if ($request->has('delete_bill') && $transaction->bill_refer) {
//         $path = public_path('bills/' . $transaction->bill_refer);
//         if (File::exists($path)) {
//             File::delete($path);
//         }
//         $transaction->bill_refer = null;
//     }

//     // Handle new bill upload
//     if ($request->hasFile('bill_refer')) {
//         $file = $request->file('bill_refer');
//         $imageName = time() . '.' . $file->extension();
//         $file->move(public_path('bills'), $imageName);
//         $transaction->bill_refer = $imageName;
//     }

//     // Update transaction fields
//     $transaction->transaction_type = $request->transaction_type;
//     $transaction->amount = $request->amount;
//     $transaction->transaction_date = $request->transaction_date;
//     $transaction->remarks = $request->remarks;
//     $transaction->available_amt = $newBalance;
//     $transaction->payment_type = $request->payment_type;
//     $transaction->save();

//     // Update expense items if debit
//     TransactionItem::where('transaction_id', $transaction->id)->delete();
//     if ($request->transaction_type === 'debit') {
//         foreach ($request->items as $item) {
//             TransactionItem::create([
//                 'transaction_id' => $transaction->id,
//                 'expense_item_id' => $item['expense_item_id'],
//             ]);
//         }
//     }

//     return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
// }
public function sendImageMail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'image' => 'required|string',
    ]);

    Mail::to($request->email)->send(new ImageMail($request->image));

    return response()->json(['status' => 'sent']);
}
public function update(Request $request, $id)
{
    $transaction = Transaction::findOrFail($id);

    if ($transaction->transaction_type === 'credit') {
        if ($transaction->last_entry !== 1) {
            return response()->json(['error' => 'Only the latest credit transaction can be edited.'], 403);
        }
    }

    if ($transaction->transaction_type === 'debit' || $transaction->last_entry === 1) {
        // Soft delete original
        // dd($transaction);
        $transaction->update(['is_deleted' => true]);
        Log::info("Soft deleting transaction ID: " . $transaction->id);

        // Log audit
                TransactionAudit::create([
                    'transaction_id' => $transaction->id,
                    'action' => 'edit',
                    'original_data' => $transaction->toArray(),
                    'edited_by' => optional(auth()->user())->id,
                ]);

        // Recalculate balance
        $last = Transaction::where('last_entry', 1)->first();
       

        // Reset last_entry
        Transaction::where('last_entry', 1)->update(['last_entry' => 0]);
        if ($transaction->transaction_type === 'debit'){
             $adjustedBalance = $last->available_amt + $transaction->amount;
                // Validate with required bill
                $request->validate([
                    'amount' => 'required|numeric|min:1',
                    'bill_refer' => 'required|file|mimes:jpg,jpeg,png,pdf',
                ]);
                // Upload bill
                $imageName = '';
                if ($request->hasFile('bill_refer')) {
                    $file = $request->file('bill_refer');
                    $imageName = time() . '.' . $file->extension();
                    $file->move(public_path('bills'), $imageName);
                }
                // Create new transaction
                $newTxn = Transaction::create([
                    'transaction_type' => 'debit',
                    'amount' => $request->amount,
                    'transaction_date' => Carbon::now()->toDateString(),
                    'remarks' => $request->remarks,
                    'available_amt' => $adjustedBalance - $request->amount,
                    'payment_type' => $request->payment_type,
                    'bill_refer' => $imageName,
                    'last_entry' => 1,
                ]);

                foreach ($request->items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $newTxn->id,
                        'expense_item_id' => $item['expense_item_id'],
                    ]);
                }
return response()->json(['success' => true, 'message' => 'Debit transaction updated successfully.']);
        } else {
             $adjustedBalance = $last->available_amt - $transaction->amount;
                $request->validate([
                                    'amount' => 'required|numeric|min:1',
                                ]);
                   $newTxn = Transaction::create([
                    'transaction_type' => 'credit',
                    'amount' => $request->amount,
                    'transaction_date' => Carbon::now()->toDateString(),
                    'available_amt' => $adjustedBalance + $request->amount,
                     'last_entry' => 1,
                      ]);
            return response()->json(['success' => true, 'message' => 'Credit transaction updated successfully.']);
        }
    }
}
// public function destroy($id){
//   $expense = Transaction::find($id);
//     if ($expense) {
//             $last = Transaction::orderBy('id', 'desc')->first();
        
//             $adjustedBalance = $last->available_amt + $expense->amount;

//             $newTxn = Transaction::create([
//                 'transaction_type' => 'reversal',
//                 'amount' => $expense->amount,
//                 'remarks' => 'Reversal Amount Deleted Transaction - id =>'.$id,
//                 'available_amt' => $adjustedBalance,
//                 'bill_refer' =>$expense->bill_refer,
//                 'last_entry' => 0,
//             ]);

//     $log_name='delete_expense';
//     $this->logProcessActivity('Expense Deleted', $expense,$log_name);
//         $expense->update(['is_deleted' => 1]);
//         TransactionItem::where('transaction_id', $id)->delete();
//     }
//   return response()->json(['message' => 'Record Deleted successfully!'],200);

// }


public function destroy($id)
{
    $deletedTxn = Transaction::findOrFail($id);

    if ($deletedTxn->transaction_date != now()->toDateString()) {
        return response()->json(['error' => 'Only today\'s transactions can be deleted'], 403);
    }

    if ($deletedTxn->is_deleted) {
        return response()->json(['error' => 'Transaction already deleted'], 400);
    }

    // Mark as deleted
    $deletedTxn->update(['is_deleted' => 1]);

    // Get all transactions after the deleted one
    $subsequentTxns = Transaction::where('transaction_date', '>=', $deletedTxn->transaction_date)
        ->where('id', '>', $deletedTxn->id)
        ->where('is_deleted', false)
        ->orderBy('transaction_date')
        ->orderBy('id')
        ->get();

    // Recalculate available_amt
    $previousBalance = Transaction::where('transaction_date', '<=', $deletedTxn->transaction_date)
        ->where('id', '<', $deletedTxn->id)
        ->where('is_deleted', false)
        ->orderBy('transaction_date', 'desc')
        ->orderBy('id', 'desc')
        ->pluck('available_amt')
        ->first();

    // Adjust for deleted transaction
    if ($deletedTxn->transaction_type === 'credit') {
        $previousBalance -= $deletedTxn->amount;
    } elseif ($deletedTxn->transaction_type === 'debit') {
        $previousBalance += $deletedTxn->amount;
    }

    // Update subsequent transactions
    foreach ($subsequentTxns as $txn) {
        if ($txn->transaction_type === 'credit') {
            $previousBalance += $txn->amount;
        } elseif ($txn->transaction_type === 'debit') {
            $previousBalance -= $txn->amount;
        }

        $txn->update(['available_amt' => $previousBalance]);
    }

    // Update last_entry flag
    Transaction::where('last_entry', 1)->update(['last_entry' => 0]);
    $latestTxn = Transaction::where('is_deleted', false)->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->first();
    if ($latestTxn) {
        $latestTxn->update(['last_entry' => 1]);
    }

    // Log audit
    $this->logProcessActivity('Transaction Deleted and Balance Recalculated', $deletedTxn, 'delete_expense');

    return response()->json(['message' => 'Transaction deleted and balances updated']);
}
}