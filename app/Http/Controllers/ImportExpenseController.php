<?php

namespace App\Http\Controllers;

use App\Unit;
use App\User;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Business;
use App\Variation;
use App\Transaction;
use App\TransactionPayment;
use App\TypesOfService;
use App\ExpenseCategory;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
// use Maatwebsite\Excel\Excel;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class ImportExpenseController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

     public function store(Request $request)
    {
        // try {
               $file = $request->file;
               if ($file) {
                
                $parsed_array = Excel::toArray([], $file);

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                // dd($business_id);
                // $business_locations = BusinessLocation::where('business_id', $business_id)->get();
                $user_id = $request->session()->get('user.id');

                $is_valid = true;
                $error_msg = '';

                $total_rows = count($imported_data);
                // dd($imported_data);

                // DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    //Check if any column is missing
                    if (count($value) < 22) {
                        $is_valid =  false;
                        $error_msg = "Some of the columns are missing. Please, use latest CSV file template.";
                        break;
                    }
                   

                    $row_no = $key + 1;
                    $expense_array = [];

                    // dd($key);
                    
                   // transaction date
                    $transaction_date = trim($value[0]);
                    $expense_array['transaction_date'] = !empty($transaction_date) ? $transaction_date : \Carbon\Carbon::now();

                    // Check if transaction_date is still empty after the ternary operation
                    if (empty($expense_array['transaction_date'])) {
                        $is_valid = false;
                        $error_msg = "Transaction date is required in row no. $row_no";
                        break;
                    }
                    // referenece no.
                    //Update reference count
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('expense', $business_id);
                    //Generate reference number
                    $transaction_data['ref_no'] = $this->transactionUtil->generateReferenceNumber('expense', $ref_count, $business_id);

                    $expense_array['ref_no'] = $transaction_data['ref_no'];

                    
 
                    
                    //Add Category
                    //Check if category exists else create new
                    $category_name = trim($value[2]);
                    if (!empty($category_name)) {
                    // dd('jjjin');

                        $category = ExpenseCategory::firstOrCreate(
                            ['business_id' => $business_id, 'name' => $category_name],
                            ['parent_id' => 0]
                        );

                        $expense_array['expense_category_id'] = $category->id;
                    }


                    //Add Sub-Category
                    $sub_category_name = trim($value[3]);
                    if (!empty($sub_category_name)) {
                        $sub_category = ExpenseCategory::firstOrCreate(
                            ['business_id' => $business_id, 'name' => $sub_category_name],
                            ['parent_id' => $category->id]
                        );

                        $expense_array['expense_sub_category_id'] = !empty($sub_category->id) ? $sub_category->id : null;
                    }


                   
                    // $expense_array['location_id'] = $location_id;
                    if (!empty(trim($value[4]))) {
                    
                        // Find the location based on the name
                        $businessLocation = BusinessLocation::where('business_id', $business_id)
                                                           ->where('name', $value[4])
                                                           ->first();

                        // If the location exists, set location_id based on its position in the list
                        if ($businessLocation) {
                    
                            $expense_array['location_id'] = $businessLocation->id;
                        }else{
                            $is_valid = false;
                            $error_msg = "Business Location is required in row no. $row_no";
                            break;
                        }
                    
                    }

                    $expense_array['type'] = 'expense';


                    // payment status
                    $payment_status = strtolower(trim($value[5]));
                    if (in_array($payment_status, ['due','paid', null])) {
                        $expense_array['payment_status'] = $payment_status;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for PAYMENT STATUS in row no. $row_no";
                        break;
                    }


                     //total before tax
                     if (isset($value[7])) {
                        $expense_array['total_before_tax'] = trim($value[7]);
                    } else {
                        $is_valid =  false;
                        $error_msg = "final total is required in row no. $row_no";
                        break;
                    }

                    $tax_rate = 0;
                    // Add tax
                    $tax_name = trim($value[6]);
                    if (!empty($tax_name)) {
                        $tax = TaxRate::where('business_id', $business_id)
                                    ->where(function ($query) use ($tax_name) {
                                        $query->where('name', $tax_name);
                                    })->first();

                        $tax_rate = ($value[7] * (float)$tax->amount)/100; 

                        if (!empty($tax)) {
                                            
                            $expense_array['tax_id'] = $tax->id;
                        } else{
                            $is_valid =  false;
                            $error_msg = "tax is not registered. $row_no";
                            break;

                        }
                    } else {
                        $expense_array['tax_id'] = null;
                    }

                     // dd($expense_array);

                                   

                    $final_total = $value[7] + $tax_rate;


                    $expense_array['tax_amount'] = $tax_rate;
                    $expense_array['final_total'] = $final_total;

                    //expense for
                    $expense_for = trim($value[8]);
                    if (!empty($expense_for)) {
                        $user = User::where('business_id', $business_id)
                                        ->where('first_name', $expense_for)
                                        ->first();
                        if (!empty($user)) {
                            $expense_array['expense_for'] = $user->id;
                            
                        }elseif(empty($user)){
  $expense_array['expense_for'] = '';
                        } else {
                            $is_valid = false;
                            $error_msg = "User with name $expense_for in row no. $row_no not found. ";
                            break;
                        }
                    }

                    //contact (customer or supplier)
                    $contacts = trim($value[9]);
                    if (!empty($contacts)) {
                        $contact = Contact::where('business_id', $business_id)
                                        ->where('name', $contacts)
                                        ->first();
                        if (!empty($contact)) {
                            $expense_array['contact_id'] = $contact->id;
                            
                        } elseif (empty($contact)) {
                            $expense_array['contact_id'] = '';
                            
                        } else {
                            $is_valid = false;
                            $error_msg = "User with name $contacts in row no. $row_no not found. ";
                            break;
                        }
                    }


                    // additional notes
                    $expense_array['additional_notes'] = isset($value[10]) ? $value[10] : '';
                    // dd($expense_array);

                    //  created by
                    // $expense_array['created_by'] = $user_id;
                    $created_by = trim($value[11]);
                    if (!empty($created_by)) {
                        $user1 = User::where('business_id', $business_id)
                                        ->where('first_name', $created_by)
                                        ->first();
                            // dd($user1);
                        if (!empty($user1)) {
                            $expense_array['created_by'] = $user1->id;
                            
                        } else {
                            $is_valid = false;
                            $error_msg = "User with name $created_by in row no. $row_no not found. ";
                            break;
                        }
                    }
                    // is recurrinng
                    $is_recurring = trim($value[12]);
                    if (in_array($is_recurring, [0,1, null])) {
                        $expense_array['is_recurring'] = $is_recurring;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for IS RECURRING in row no. $row_no";
                        break;
                    }

                    //recurring interval
                    $recur_interval = trim($value[13]);
                    if (isset($value[13])) {
                        // dd('in');
                        $expense_array['recur_interval'] = trim($value[13]);
                    } else {

                        $expense_array['recur_interval'] = null;
                    }

                    // recurring interval type
                    $recurring_interval_type = strtolower(trim($value[14]));
                    if (in_array($recurring_interval_type, ['days','months','years', null])) {
                        $expense_array['recur_interval_type'] = $recurring_interval_type;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for RECURRINNG INTERVAL TYPE in row no. $row_no";
                        break;
                    }


                    // no. of repetitions
                    if (isset($value[15])) {
                        $expense_array['recur_repetitions'] = trim($value[15]);
                    } else {
                        $expense_array['recur_repetitions'] = null;
                    }

                    $expense_array['status'] = 'final';
                    $expense_array['business_id'] = $business_id;

                    // dd( $expense_array['payment_status']);

                    if ($payment_status === 'paid') {

                        // payment amount
                        // if (isset($value[16])) {
                            $payment_amount = trim($value[16]);
                            $payment_array['amount'] = !empty($payment_amount) ? $payment_amount : $final_total ;

                        // } else {
                        //     $is_valid = false;
                        //     $error_msg = "Payment amount is required in row no. $row_no";
                        //     break;
                        // }
    
                        // payment method
                        if (isset($value[17])) {
                            $payment_method = trim($value[17]);
                            $payment_array['method'] = $payment_method;
                        } else {
                            $is_valid = false;
                            $error_msg = "Payment method is required in row no. $row_no";
                            break;
                        }

                        // payment paid on
                        $payment_paid_on = trim($value[18]);
                        $payment_array['paid_on'] = !empty($payment_paid_on) ? $payment_paid_on :  \Carbon\Carbon::now();

                        $is_return = trim($value[19]);
                        if (in_array($is_recurring, [0,1, null])) {
                            $payment_array['is_return'] = $is_return;
                        } else {
                            $is_valid =  false;
                            $error_msg = "Invalid value for IS return in row no. $row_no";
                            break;
                        }

                        $payment_array['note'] = !empty($value[20]) ? $value[20] : '';

                        $prefix_type = 'sell_payment';

                        $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type, $business_id);
                        //Generate reference number
                        $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count, $business_id);

                        $payment_array['payment_ref_no'] = $payment_ref_no; 



                        $payment_array['business_id'] = $business_id; 
                        $payment_array['card_type'] = 'credit'; 
                        // $payment_array['created_by'] = $user->id; 

                        $transaction = Transaction::create($expense_array);
                        
                        // dd('transaction',$transaction);
                        $payment_array['transaction_id'] = $transaction->id;

                        $transaction_payments = TransactionPayment::create($payment_array);


                       

                    }else{

                        
                        $expense_array['payment_status'] = 'due';
                        $transaction = Transaction::create($expense_array);


                    }

                    // dd('out');


                     // Insert payment details into transaction_payments table
                    //  if(empty($payment_array))
                    //  {
                    //      $expense_array['payment_status'] = 'due';
                    //      $transaction = Transaction::create($expense_array);
                    //  }
                    //  else{
                    //      $expense_array['payment_status'] = 'paid';

                    //      $transaction = Transaction::create($expense_array);
                         
                    //      // dd('transaction',$transaction);
                    //      $payment_array['transaction_id'] = $transaction->id;

                    //      $transaction_payments = TransactionPayment::create($payment_array);
                       
                    // }

 
                   
                    
                }
            }

                

                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

            

            //     $output = ['success' => 1,
            //     'msg' => __('expense.file_imported_successfully')
            // ];
            

            return redirect('expenses')->with('status');
}



}
