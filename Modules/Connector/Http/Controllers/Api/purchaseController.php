<?php

namespace Modules\Connector\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\PurchaseLine;
use App\Transaction;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Events\TransactionPaymentAdded;
use App\Events\TransactionPaymentDeleted;
use App\Exceptions\AdvanceBalanceNotAvailable;
use App\Product;
use App\ReferenceCount;
use App\TaxRate;
use App\TransactionPayment;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Variation; 
use Spatie\Activitylog\Models\Activity;
use Modules\Connector\Transformers\CommonResource;

class purchaseController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => ''
        ];
    }
    public function getpurchase()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        $query = PurchaseLine::join(
            'transactions as t',
            'purchase_lines.transaction_id',
            '=',
            't.id'
        )
            ->join(
                'variations as v',
                'purchase_lines.variation_id',
                '=',
                'v.id'
            )
            ->leftJoin('users', 't.created_by', '=', 'users.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
           
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->select(
                't.id',
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as supplier',
                'c.supplier_business_name',
                'c.mobile as number',
                'c.supplier_business_name',
                't.status',
                't.ref_no',
                't.payment_status',
                't.transaction_date as transaction_date',
                'purchase_lines.purchase_price_inc_tax as unit_sell_price',
                DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                'purchase_lines.quantity_adjusted',
                'u.short_name as unit',
                DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal'),
                DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) AS added_by")
            )
            ->groupBy('purchase_lines.id');
        $filters = request()->only(['per_page']);
        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : $perPage = 10;
        if ($perPage == -1) {
            $data = $query->get();
        } else {
            $data = $query->Paginate($perPage);
            $data->appends(request()->query());
        }

        return CommonResource::collection($data);
    }
    public function store(Request $request)
    {
        // dd($request);

        $user = Auth::user();
        $business_id = $user->business_id;

        $transaction_data = $request->only(['ref_no',  'status', 'payment_status', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id', 'discount_type', 'discount_amount',  'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $exchange_rate = $transaction_data['exchange_rate'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

        $user_id = $request->business_id;
        $enable_product_editing = $request->business_id;
        Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

        $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;
        if ($transaction_data['discount_type'] == 'fixed') {
            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details) * $exchange_rate;
        } elseif ($transaction_data['discount_type'] == 'percentage') {
            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
        } else {
            $transaction_data['discount_amount'] = 0;
        }

        $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'], $currency_details) * $exchange_rate;

        $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'], $currency_details) * $exchange_rate;

        $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;

        $transaction_data['business_id'] = $business_id;

       $transaction_data['created_by'] = $user->id;

        $transaction_data['type'] = 'purchase';

        $transaction_data['payment_status'] = 'due';

        $transaction_data['transaction_date'] = $transaction_data['transaction_date'];

        $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');
        $transaction_data['custom_field_1'] = $request->input('custom_field_1', null);
        $transaction_data['custom_field_2'] = $request->input('custom_field_2', null);
        $transaction_data['custom_field_3'] = $request->input('custom_field_3', null);
        $transaction_data['custom_field_4'] = $request->input('custom_field_4', null);

        $transaction_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
        $transaction_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
        $transaction_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
        $transaction_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
        $transaction_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);


        if ($request->input('additional_expense_value_1') != '') {
            $transaction_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $transaction_data['additional_expense_value_1'] = $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate;
        }

        if ($request->input('additional_expense_value_2') != '') {
            $transaction_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $transaction_data['additional_expense_value_2'] = $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate;
        }

        if ($request->input('additional_expense_value_3') != '') {
            $transaction_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $transaction_data['additional_expense_value_3'] = $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate;
        }

        if ($request->input('additional_expense_value_4') != '') {
            $transaction_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');
            $transaction_data['additional_expense_value_4'] = $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate;
        }
        DB::beginTransaction();

        //Update reference count
        $ref_count = $this->productUtil->setAndGetReference($transaction_data['type']);
        if (empty($transaction_data['ref_no'])) {
            $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
        }


        $transaction = Transaction::create($transaction_data);

// dd($transaction);
        $purchase_lines = [];
        $purchases = $request->input('purchases');

        $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);

        $this->transactionUtil->createOrUpdatePaymentLine($transaction, $request->input('payment'));
        //update payment status
        $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        
        // if (!empty($transaction->purchase_order_ids)) {
        //     $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
        // }
        //Adjust stock over selling if found
        $this->productUtil->adjustStockOverSelling($transaction);

        $this->transactionUtil->activityLog($transaction, 'added');
        DB::commit();
        $output = [
            'success' => 1,
            'msg' => __('purchase.purchase_add_success')
        ];
        return ['status', $output];
    }


    public function update(Request $request, $id)
    {
        
        // if (!auth()->user()->can('purchase.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // try {
            $user = Auth::user();
        $business_id = $user->business_id;
            $transaction = Transaction::findOrFail($id);
            
            //Validate document size
            // $request->validate([
            //     'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            // ]);

            $transaction = Transaction::findOrFail($id);
            $before_status = $transaction->status;
            $business_id = $user->business_id;;
            $enable_product_editing =  $enable_product_editing = $request->business;
            
            $transaction_before = $transaction->replicate();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        
            $update_data = $request->only([ 'ref_no', 'status', 'contact_id',
                            'transaction_date', 'total_before_tax',
                            'discount_type', 'discount_amount', 'tax_id',
                            'tax_amount', 'shipping_details',
                            'shipping_charges', 'final_total',
                            'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);
                            
            $exchange_rate = $update_data['exchange_rate'];
           
            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] =$update_data['transaction_date'];
            
            //unformat input values
            $update_data['total_before_tax'] = $this->productUtil->num_uf($update_data['total_before_tax'], $currency_details) * $exchange_rate;
           
            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }

            $update_data['tax_amount'] = $this->productUtil->num_uf($update_data['tax_amount'], $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($update_data['shipping_charges'], $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($update_data['final_total'], $currency_details) * $exchange_rate;
            //unformat input values ends
         
            $update_data['custom_field_1'] = $request->input('custom_field_1', null);
            $update_data['custom_field_2'] = $request->input('custom_field_2', null);
            $update_data['custom_field_3'] = $request->input('custom_field_3', null);
            $update_data['custom_field_4'] = $request->input('custom_field_4', null);

            $update_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $update_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $update_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $update_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $update_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);

            //upload document
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $purchase_order_ids = $transaction->purchase_order_ids ?? [];
           
            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate: 0;
            $update_data['additional_expense_value_3'] = $request->input('additional_expense_value_3') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_4'] = $request->input('additional_expense_value_4') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate : 0;
            
            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);
            
            //Update transaction payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
         
            $transaction->payment_status = $payment_status;
            
            $purchases = $request->input('purchases');
           
            $delete_purchase_lines = $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing, $before_status);
            // dd($delete_purchase_lines);
            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $new_purchase_order_ids = $transaction->purchase_order_ids ?? [];
            // $purchase_order_ids = array_merge($purchase_order_ids, $new_purchase_order_ids);
            // if (!empty($purchase_order_ids)) {
            //     $this->transactionUtil->updatePurchaseOrderStatus($purchase_order_ids);
            // }

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            // $output = ['success' => 0,
            //                 'msg' => $e->getMessage()
            //             ];
            // return back()->with('status', $output);
        // }

        return  $output;
    }
    public function delete($id)
    {
        // if (!auth()->user()->can('purchase.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // try {
            // if (request()->ajax()) {
                $user = Auth::user();
                $business_id = $user->business_id;

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }
        
                $transaction = Transaction::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with(['purchase_lines'])
                                ->first();

                //Check if lot numbers from the purchase is selected in sale
                if (session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale')
                    ];
                    return $output;
                }
                
                $delete_purchase_lines = $transaction->purchase_lines;
                DB::beginTransaction();

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no
                ];
                $this->transactionUtil->activityLog($transaction, 'purchase_deleted', $log_properities);

                $transaction_status = $transaction->status;
                if ($transaction_status != 'received') {
                    $transaction->delete();
                } else {
                    //Delete purchase lines first
                    $delete_purchase_line_ids = [];
                    foreach ($delete_purchase_lines as $purchase_line) {
                        $delete_purchase_line_ids[] = $purchase_line->id;
                        $this->productUtil->decreaseProductQuantity(
                            $purchase_line->product_id,
                            $purchase_line->variation_id,
                            $transaction->location_id,
                            $purchase_line->quantity
                        );
                    }
                    PurchaseLine::where('transaction_id', $transaction->id)
                                ->whereIn('id', $delete_purchase_line_ids)
                                ->delete();

                    //Update mapping of purchase & Sell.
                    $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
                }

                //Delete Transaction
                $transaction->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();

                DB::commit();

                $output = ['success' => true,
                            'msg' => __('lang_v1.purchase_delete_success')
                        ];
            // }
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            // $output = ['success' => false,
            //                 'msg' => $e->getMessage()
            //             ];
        // }

        return $output;
    }
    public function show_purchase($id)
    {
        // dd($id);
        $user = Auth::user();

        $business_id = $user->business_id;

        $query = PurchaseLine::join(
            'transactions as t',
            'purchase_lines.transaction_id',
            '=',
            't.id'
        )
            ->join(
                'variations as v',
                'purchase_lines.variation_id',
                '=',
                'v.id'
            )
            ->leftJoin('users', 't.created_by', '=', 'users.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
           
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->select(
                't.id',
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as supplier',
                'c.supplier_business_name',
                'c.mobile as number',
                'c.supplier_business_name',
                't.status',
                't.ref_no',
                't.payment_status',
                't.transaction_date as transaction_date',
                'purchase_lines.purchase_price_inc_tax as unit_sell_price',
                DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                'purchase_lines.quantity_adjusted',
                'u.short_name as unit',
                DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal'),
                DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) AS added_by")
            )
            ->groupBy('purchase_lines.id')
            ->find($id);
     
        return $query;
    }
}
