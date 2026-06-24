<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyExpense;
use App\Models\ExpenseItems;
use App\Models\MonthlyExpenseItems;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyExpenseExport;
use App\Exports\MonthlyExpenseReportExport;



class MonthlyExpenseController extends Controller
{
   public function index(Request $request){
           $balance = MonthlyExpense::latest()->pluck('available_amt')->first();
        //Transaction::sum(DB::raw("CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END"));
        $sub_query = MonthlyExpense::with('items.expenseItem')->where('is_deleted', '!=','1');
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
             if (!empty($request->exp_items)) {
                 $selectedItems = is_array($request->exp_items)
                            ? $request->exp_items
                            : explode(',', $request->exp_items);

                    // Filter transactions that have at least one matching expense item
                    $sub_query->whereHas('items.expenseItem', function ($query) use ($selectedItems) {
                        
                        $query->whereIn('id', $selectedItems);
                    });
              }
        $transactions=$sub_query->orderBy('transaction_date', 'asc')->get();


                //     $sql = vsprintf(
        //         str_replace('?', "'%s'", $tasks->toSql()),
        //         $tasks->getBindings()
        //     );
        //   echo $sql;
        //   die;
        if(!empty($request->export_expense)){
                     if ($transactions->isEmpty()) {
            return redirect()->route('expenses.index')->withError('No Expense Found for the selected criteria.');
        } else {
            $filters = $request->only(['start_date', 'end_date', 'exp_type','exp_items']);
            //dd($filters);
            /*Download based on transactions */
            //return Excel::download(new MonthlyExpenseExport($filters), 'MonthlyExpense_Report.xlsx');
            /**Generate excel based on each expense Items */
            //  $filters = [
            //         'exp_items' => $request->input('exp_items') // e.g., from a dropdown
            //     ];

             return Excel::download(new MonthlyExpenseReportExport($filters), 'expense_items.xlsx');
        }
        } else{
         $expenseItems = ExpenseItems::where('expense_type_status', config('global.active_status'))->get();
        $LoadMultiselectchkbox=true;
         $LoadDateTimepicker=true;
        return view('expenses.monthly_expenses', compact('transactions', 'expenseItems', 'balance','LoadMultiselectchkbox','LoadDateTimepicker'));
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
    
        $amt = MonthlyExpense::latest()->first();
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
                $file->move(public_path('monthly_expenses'), $imageName);
            }
                // Reset last_entry
        MonthlyExpense::where('last_entry', 1)->update(['last_entry' => 0]);
        
        $transaction = MonthlyExpense::create([
            'transaction_type' => $request->transaction_type,
            'trans_amount' =>$request->amount,
            'transaction_date' => $request->transaction_date,
            'remarks' => $request->remarks,
            'available_amt' => $present_balance,
            'payment_type' => $request->payment_type,
            'bill_refer' => $imageName,
            'last_entry' => 1,
        ]);
        if ($request->transaction_type === 'debit') {
            foreach ($request->items as $item) {
                $item_id=$item['expense_item_id'];
                MonthlyExpenseItems::create([
                    'expense_id' => $transaction->id,
                    'expense_item_id' => $item['expense_item_id'],
                    'exp_amount' => $request->sel_exp_amt[$item_id]
                ]);
            }
        }
                return response()->json(['success' => true, 'message' => 'New Transaction Added successfully.']);
    }
public function edit($id)
{
    $trans = MonthlyExpense::with('items')->find($id);
    $selectedItems = $trans->items->pluck('expense_item_id')->toArray();
    return response()->json([
        'id' => $trans->id,
        'transaction_type' => $trans->transaction_type,
        'amount' => $trans->trans_amount,
        'transaction_date' => $trans->transaction_date,
        'payment_type' => $trans->payment_type,
        'remarks' => $trans->remarks,
        'bill_refer' => $trans->bill_refer,
        'items' => $selectedItems,
    ]);
}
public function check_amount_availability(Request $request){
    $lastTxn = MonthlyExpense::where('last_entry', 1)->first();
    $last_avail_balance = $lastTxn ? $lastTxn->available_amt : 0;

    // Adjust balance if editing an existing debit transaction
    if ($request->filled('id')) {
        $originalTxn = MonthlyExpense::find($request->id);
        if ($originalTxn && $originalTxn->transaction_type === 'debit') {
            $last_avail_balance += $originalTxn->available_amt;
        }
    }

    $flag = ($request->amount > $last_avail_balance && $request->trans_type === 'debit') ? 1 : 0;

    return response()->json(['flag' => $flag]);
}
 public function show(Request $request){

        $expense_dtls=ExpenseItems::where('expense_type_status', config('global.active_status'))
                            ->whereIn('id',$request->expense_ids)->pluck('expense_type_name','id');
         return response()->json($expense_dtls);
    }
public function get_details(Request $request)
{
    // Validate and normalize expense_ids input
    $expenseIds = is_array($request->expense_ids)
        ? $request->expense_ids
        : explode(',', (string) $request->expense_ids);

    $expenseIds = array_filter($expenseIds); // remove empty values

    // If no valid expense IDs, return error
    if (empty($expenseIds)) {
        return response()->json(['error' => 'No valid expense IDs provided'], 400);
    }
    // Get monthly expense details with active expense items
    $monthlyItems = MonthlyExpenseItems::with('expenseItem')
        ->where('expense_id', $request->expense_ids)
        ->get()
        ->filter(function ($item) {
            return $item->expenseItem !== null &&
                   $item->expenseItem->expense_type_status === config('global.active_status');
        })
        ->map(function ($item) {
            return [
                'expense_item_id' => $item->expense_item_id,
                'expense_type_name' => $item->expenseItem->expense_type_name,
                'exp_amount' => $item->exp_amount,
            ];
        })
        ->values();

    return response()->json([
        'monthly_expense_details' => $monthlyItems,
    ]);
}
public function update(Request $request, $id)
{
    $txn = MonthlyExpense::with('items')->findOrFail($id);

    // Extract updated expense items
    $expenseIds = $request->input('sel_exp_id', []);
    $expenseAmounts = $request->input('sel_exp_amt', []);
    $newType = $request->transaction_type;

    // Calculate new total amount from remaining items
    $newAmount = 0;
    foreach ($expenseIds as $eid) {
        $amount = isset($expenseAmounts[$eid]) ? floatval($expenseAmounts[$eid]) : 0;
        $newAmount += $amount;
    }

    // Fetch all non-deleted transactions in order
    $allTxns = MonthlyExpense::where('is_deleted', false)
        ->orderBy('transaction_date')
        ->orderBy('id')
        ->get();

    $runningBalance = 0;

    foreach ($allTxns as $t) {
        if ($t->id == $txn->id) {
            // Validate balance before applying
            if ($newType === 'debit' && $newAmount > $runningBalance) {
                return response()->json(['error' => 'Insufficient balance'], 400);
            }

            // Update transaction
            $txn->update([
                'transaction_type' => $newType,
                'trans_amount' => $newAmount,
                'transaction_date' => $request->transaction_date,
                'remarks' => $request->remarks,
                'payment_type' => $request->payment_type,
                'bill_refer' => $request->bill_refer,
                'available_amt' => $newType === 'debit'
                    ? $runningBalance - $newAmount
                    : $runningBalance + $newAmount,
            ]);

            // Sync expense items
            MonthlyExpenseItems::where('expense_id', $txn->id)->delete();
            foreach ($expenseIds as $eid) {
                MonthlyExpenseItems::create([
                    'expense_id' => $txn->id,
                    'expense_item_id' => $eid,
                    'exp_amount' => $expenseAmounts[$eid],
                ]);
            }

            // Update balance after applying this transaction
            $runningBalance = $txn->available_amt;
        } elseif ($t->id > $txn->id) {
            // Apply subsequent transactions
            if ($t->transaction_type === 'credit') {
                $runningBalance += $t->trans_amount;
            } elseif ($t->transaction_type === 'debit') {
                $runningBalance -= $t->trans_amount;
            }
            $t->update(['available_amt' => $runningBalance]);
        } else {
            // Apply earlier transactions
            if ($t->transaction_type === 'credit') {
                $runningBalance += $t->trans_amount;
            } elseif ($t->transaction_type === 'debit') {
                $runningBalance -= $t->trans_amount;
            }
        }
    }

    // Update last_entry flag
    MonthlyExpense::where('last_entry', 1)->update(['last_entry' => 0]);
    $latestTxn = MonthlyExpense::where('is_deleted', false)
        ->orderBy('transaction_date', 'desc')
        ->orderBy('id', 'desc')
        ->first();
    if ($latestTxn) {
        $latestTxn->update(['last_entry' => 1]);
    }

    // Log audit
    $this->logProcessActivity('Transaction Edited with Item Deletions', $txn, 'edit_expense');

    return response()->json(['message' => 'Transaction updated successfully']);
}
public function destroy($id)
{
    $deletedTxn = MonthlyExpense::findOrFail($id);

    if ($deletedTxn->transaction_date != now()->toDateString()) {
        return response()->json(['error' => 'Only today\'s transactions can be deleted'], 403);
    }

    if ($deletedTxn->is_deleted) {
        return response()->json(['error' => 'Transaction already deleted'], 400);
    }

    // Mark as deleted
    $deletedTxn->update(['is_deleted' => 1]);

    // Get all transactions after the deleted one
    $subsequentTxns = MonthlyExpense::where('transaction_date', '>=', $deletedTxn->transaction_date)
        ->where('id', '>', $deletedTxn->id)
        ->where('is_deleted', false)
        ->orderBy('transaction_date')
        ->orderBy('id')
        ->get();

    // Recalculate available_amt
    $previousBalance = MonthlyExpense::where('transaction_date', '<=', $deletedTxn->transaction_date)
        ->where('id', '<', $deletedTxn->id)
        ->where('is_deleted', false)
        ->orderBy('transaction_date', 'desc')
        ->orderBy('id', 'desc')
        ->pluck('available_amt')
        ->first();

    // Adjust for deleted transaction
    if ($deletedTxn->transaction_type === 'credit') {
        $previousBalance -= $deletedTxn->trans_amount;
    } elseif ($deletedTxn->transaction_type === 'debit') {
        $previousBalance += $deletedTxn->trans_amount;
    }

    // Update subsequent transactions
    foreach ($subsequentTxns as $txn) {
        if ($txn->transaction_type === 'credit') {
            $previousBalance += $txn->trans_amount;
        } elseif ($txn->transaction_type === 'debit') {
            $previousBalance -= $txn->trans_amount;
        }

        $txn->update(['available_amt' => $previousBalance]);
    }

    // Update last_entry flag
    MonthlyExpense::where('last_entry', 1)->update(['last_entry' => 0]);
    $latestTxn = MonthlyExpense::where('is_deleted', false)->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->first();
    if ($latestTxn) {
        $latestTxn->update(['last_entry' => 1]);
    }

    // Log audit
    $this->logProcessActivity('Transaction Deleted and Balance Recalculated', $deletedTxn, 'delete_expense');

    return response()->json(['message' => 'Transaction deleted and balances updated']);
}
   }

