<?php

namespace App\Http\Controllers;

use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Events\PurchaseCreatedOrModified;
use App\Product;
use App\PurchaseLine;
use App\TaxRate;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\TransactionPayment;
use Illuminate\Support\Facades\Auth;
use \Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil,TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('purchase.view') && ! auth()->user()->can('purchase.create') && ! auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchases($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (! empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }
            if (! empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            if (! empty(request()->status)) {
                $purchases->where('transactions.status', request()->status);
            }

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (! auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }

            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">'.
                                __('messages.actions').
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can('purchase.view')) {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>'.__('messages.view').'</a></li>';
                    }
                    if (auth()->user()->can('purchase.view')) {
                        $html .= '<li><a href="#" class="print-invoice" data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'printInvoice'], [$row->id]).'"><i class="fas fa-print" aria-hidden="true"></i>'.__('messages.print').'</a></li>';
                    }
                    if (auth()->user()->can('purchase.update')) {
                        $html .= '<li><a href="'.action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row->id]).'"><i class="fas fa-edit"></i>'.__('messages.edit').'</a></li>';
                    }
                    if (auth()->user()->can('purchase.delete')) {
                        $html .= '<li><a href="'.action([\App\Http\Controllers\PurchaseController::class, 'destroy'], [$row->id]).'" class="delete-purchase"><i class="fas fa-trash"></i>'.__('messages.delete').'</a></li>';
                    }

                    $html .= '<li><a href="'.action([\App\Http\Controllers\LabelsController::class, 'show']).'?purchase_id='.$row->id.'" data-toggle="tooltip" title="'.__('lang_v1.label_help').'"><i class="fas fa-barcode"></i>'.__('barcode.labels').'</a></li>';

                    if (auth()->user()->can('purchase.view') && ! empty($row->document)) {
                        $document_name = ! empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
                        $html .= '<li><a href="'.url('uploads/documents/'.$row->document).'" download="'.$document_name.'"><i class="fas fa-download" aria-hidden="true"></i>'.__('purchase.download_document').'</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="'.url('uploads/documents/'.$row->document).'" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>'.__('lang_v1.view_document').'</a></li>';
                        }
                    }

                    if (auth()->user()->can('purchase.payments') ||
                        auth()->user()->can('edit_purchase_payment') ||
                        auth()->user()->can('delete_purchase_payment')) {
                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid') {
                            $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->id]).'" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>'.__('purchase.add_payment').'</a></li>';
                        }

                        $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->id]).
                        '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>'.__('purchase.view_payments').'</a></li>';
                    }

                    if (auth()->user()->can('purchase.update')) {
                        $html .= '<li><a href="'.action([\App\Http\Controllers\PurchaseReturnController::class, 'add'], [$row->id]).
                        '"><i class="fas fa-undo" aria-hidden="true" ></i>'.__('lang_v1.purchase_return').'</a></li>';
                    }

                    if (auth()->user()->can('purchase.update') || auth()->user()->can('purchase.update_status')) {
                        $html .= '<li><a href="#" data-purchase_id="'.$row->id.
                        '" data-status="'.$row->status.'" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>'.__('lang_v1.update_status').'</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\NotificationController::class, 'getTemplate'], ['transaction_id' => $row->id, 'template_for' => 'new_order']).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> '.__('lang_v1.new_order_notification').'</a></li>';
                    } elseif ($row->status == 'received') {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\NotificationController::class, 'getTemplate'], ['transaction_id' => $row->id, 'template_for' => 'items_received']).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> '.__('lang_v1.item_received_notification').'</a></li>';
                    } elseif ($row->status == 'pending') {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\NotificationController::class, 'getTemplate'], ['transaction_id' => $row->id, 'template_for' => 'items_pending']).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> '.__('lang_v1.item_pending_notification').'</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    return ! empty($row->return_exists) ? $row->ref_no.' <small class="label bg-red label-round no-print" title="'.__('lang_v1.some_qty_returned').'"><i class="fas fa-undo"></i></small>' : $row->ref_no;
                })
                ->editColumn(
                    'final_total',
                    '<span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn(
                    'status',
                    '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="update_status no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);

                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    $due_html = '<strong>'.__('lang_v1.purchase').':</strong> <span class="payment_due" data-orig-value="'.$due.'">'.$this->transactionUtil->num_f($due, true).'</span>';

                    if (! empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $due_html .= '<br><strong>'.__('lang_v1.purchase_return').':</strong> <a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]).'" class="view_purchase_return_payment_modal"><span class="purchase_return" data-orig-value="'.$return_due.'">'.$this->transactionUtil->num_f($return_due, true).'</span></a>';
                    }

                    return $due_html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('purchase.view')) {
                            return  action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    }, ])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase.index')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();

        // dd($taxes);
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];
        // dd($common_settings);
        return view('purchase.create')
            ->with(compact('taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'common_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
            }

            $transaction_data = $request->only(['ref_no', 'status', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);

            $exchange_rate = $transaction_data['exchange_rate'];

            //Reverse exchange rate and save it.
            //$transaction_data['exchange_rate'] = $transaction_data['exchange_rate'];

            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'document' => 'file|max:'.(config('constants.document_size_limit') / 1000),
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            //unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
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
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);

            //upload document
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
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }

            $transaction = Transaction::create($transaction_data);

            $purchase_lines = [];

            $purchases = $request->input('purchases');

            $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);

            //Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));

            //update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if (! empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $this->transactionUtil->activityLog($transaction, 'added');

            PurchaseCreatedOrModified::dispatch($transaction);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('purchase.purchase_add_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('purchases')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      
        // if (!auth()->user()->can('purchase.view')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(
                                    'contact',
                                    'purchase_lines',
                                    'purchase_lines.product',
                                    'purchase_lines.product.unit',
                                    'purchase_lines.product.second_unit',
                                    'purchase_lines.variations',
                                    'purchase_lines.variations.product_variation',
                                    'purchase_lines.sub_unit',
                                    'location',
                                    'payment_lines',
                                    'tax'
                                )
                                ->firstOrFail();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (! empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (! empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        $statuses = $this->productUtil->orderStatuses();
        // dd($taxes, $purchase, $payment_methods, $purchase_taxes, $activities, $statuses, $purchase_order_nos, $purchase_order_dates);
        return view('purchase.show')
                ->with(compact('taxes', 'purchase', 'payment_methods', 'purchase_taxes', 'activities', 'statuses', 'purchase_order_nos', 'purchase_order_dates'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (! $this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]), ]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                'msg' => __('lang_v1.return_exist'), ]);
        }

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
        $purchase = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.product.unit',
                        'purchase_lines.product.second_unit',
                        //'purchase_lines.product.unit.sub_units',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'location',
                        'purchase_lines.sub_unit',
                        'purchase_lines.purchase_order_line'
                    )
                    ->first();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if (! empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                                        ->where('type', 'purchase_order')
                                        ->where('contact_id', $purchase->contact_id)
                                        ->where(function ($q) use ($purchase) {
                                            $q->where('status', '!=', 'completed');

                                            if (! empty($purchase->purchase_order_ids)) {
                                                $q->orWhereIn('id', $purchase->purchase_order_ids);
                                            }
                                        })
                                        ->pluck('ref_no', 'id');
        }

        return view('purchase.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings'
            ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        // try {
            $transaction = Transaction::findOrFail($id);

            //Validate document size
            $request->validate([
                'document' => 'file|max:'.(config('constants.document_size_limit') / 1000),
            ]);

            $transaction = Transaction::findOrFail($id);
            $before_status = $transaction->status;
            $business_id = request()->session()->get('user.business_id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $transaction_before = $transaction->replicate();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only(['ref_no', 'status', 'contact_id',
                'transaction_date', 'total_before_tax',
                'discount_type', 'discount_amount', 'tax_id',
                'tax_amount', 'shipping_details',
                'shipping_charges', 'final_total',
                'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', ]);

            $exchange_rate = $update_data['exchange_rate'];

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);

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
            if (! empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $purchase_order_ids = $transaction->purchase_order_ids ?? [];

            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate : 0;
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

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $new_purchase_order_ids = $transaction->purchase_order_ids ?? [];
            $purchase_order_ids = array_merge($purchase_order_ids, $new_purchase_order_ids);
            if (! empty($purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($purchase_order_ids);
            }

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            PurchaseCreatedOrModified::dispatch($transaction);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('purchase.purchase_update_success'),
            ];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        //     $output = ['success' => 0,
        //         'msg' => $e->getMessage(),
        //     ];

        //     return back()->with('status', $output);
        // }

        return redirect('purchases')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist'),
                    ];

                    return $output;
                }

                $transaction = Transaction::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with(['purchase_lines'])
                                ->first();

                //Check if lot numbers from the purchase is selected in sale
                if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale'),
                    ];

                    return $output;
                }

                $delete_purchase_lines = $transaction->purchase_lines;
                DB::beginTransaction();

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no,
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

                PurchaseCreatedOrModified::dispatch($transaction, true);

                DB::commit();

                $output = ['success' => true,
                    'msg' => __('lang_v1.purchase_delete_success'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    /**
     * Retrieves supliers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliers()
    {
        if (request()->ajax()) {
            $term = request()->q;
            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                            ->active();

            $suppliers = $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%'.$term.'%')
                                ->orWhere('supplier_business_name', 'like', '%'.$term.'%')
                                ->orWhere('contacts.contact_id', 'like', '%'.$term.'%');
            })
                        ->select(
                            'contacts.id',
                            DB::raw('IF(name="", supplier_business_name, name) as text'),
                            'supplier_business_name as business_name',
                            'contacts.mobile',
                            'contacts.address_line_1',
                            'contacts.address_line_2',
                            'contacts.city',
                            'contacts.state',
                            'contacts.country',
                            'contacts.zip_code',
                            'contacts.contact_id',
                            'contacts.pay_term_type',
                            'contacts.pay_term_number',
                            'contacts.balance'
                        )
                        ->onlySuppliers()
                        ->get();

            return json_encode($suppliers);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {   
        
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%'.$term.'%');
                    $query->orWhere('sku', 'like', '%'.$term.'%');
                    $query->orWhere('sub_sku', 'like', '%'.$term.'%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (! empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
            
            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                = [
                    'variation_id' => $product->variation_id,
                    'variation_name' => $product->variation,
                    'sub_sku' => $product->sub_sku,
                ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (! empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && ! $only_variations) {
                        $result[] = ['id' => $i,
                            'text' => $value['name'].' - '.$value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text.' ('.$variation['variation_name'].')';
                        }
                        $i++;
                        $result[] = ['id' => $i,
                            'text' => $text.' - '.$variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                           
                        ];
                    }
                    $i++;
                }
            }

            return json_encode($result);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');
            $supplier_id = $request->input('supplier_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (! empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                                    ->with(['unit', 'second_unit'])
                                    ->first();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                                ->with([
                                    'product_variation',
                                    'variation_location_details' => function ($q) use ($location_id) {
                                        $q->where('location_id', $location_id);
                                    },
                                ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $variations = $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();

                $last_purchase_line = $this->getLastPurchaseLine($variation_id, $location_id, $supplier_id);

                $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];

                return view('purchase.partials.purchase_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order',
                        'last_purchase_line',
                        'common_settings'
                    ));
            }
        }
    }

    /**
     * Finds last purchase line of a variation for the supplier for a location
     */
    private function getLastPurchaseLine($variation_id, $location_id, $supplier_id = null)
    {
        $query = PurchaseLine::join('transactions as t', 'purchase_lines.transaction_id',
                        '=', 't.id')
                        ->where('t.location_id', $location_id)
                        ->where('t.type', 'purchase')
                        ->where('t.status', 'received')
                        ->where('purchase_lines.variation_id', $variation_id);

        if (! empty($supplier_id)) {
            $query = $query->where('t.contact_id', '=', $supplier_id);
        }
        $purchase_line = $query->orderBy('transaction_date', 'desc')
                            ->select('purchase_lines.*')
                            ->first();

        return $purchase_line;
    }

    public function importPurchaseProducts(Request $request)
    {
        try {
            $file = $request->file('file');

            $parsed_array = Excel::toArray([], $file);
            //Remove header row
            $imported_data = array_splice($parsed_array[0], 1);

            $business_id = $request->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $row_count = $request->input('row_count');

            $formatted_data = [];
            $row_index = 0;
            $error_msg = '';
            foreach ($imported_data as $key => $value) {
                $row_index = $key + 1;
                $temp_array = [];

                if (! empty($value[0])) {
                    $variation = Variation::where('sub_sku', trim($value[0]))
                                        ->with([
                                            'product_variation',
                                            'variation_location_details' => function ($q) use ($location_id) {
                                                $q->where('location_id', $location_id);
                                            },
                                        ])
                                        ->first();

                    $temp_array['variation'] = $variation;

                    if (empty($variation)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $product = Product::where('id', $variation->product_id)
                                    ->where('business_id', $business_id)
                                    ->with(['unit'])
                                    ->first();

                    if (empty($product)) {
                        $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                        break;
                    }

                    $temp_array['product'] = $product;

                    $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product->id);

                    $temp_array['sub_units'] = $sub_units;
                } else {
                    $error_msg = __('lang_v1.product_not_found_exception', ['row' => $row_index, 'sku' => $value[0]]);
                    break;
                }

                if (! empty($value[0])) {
                    $temp_array['quantity'] = $value[1];
                } else {
                    $error_msg = __('lang_v1.quantity_required', ['row' => $row_index]);
                    break;
                }

                $temp_array['unit_cost_before_discount'] = ! empty($value[2]) ? $value[2] : $variation->default_purchase_price;
                $temp_array['discount_percent'] = ! empty($value[3]) ? $value[3] : 0;

                $tax_id = null;

                if (! empty($value[4])) {
                    $tax_name = trim($value[4]);
                    $tax = TaxRate::where('business_id', $business_id)
                                ->where('name', 'like', "%{$tax_name}%")
                                ->first();

                    $tax_id = $tax->id ?? $tax_id;
                }

                $temp_array['tax_id'] = $tax_id;
                $temp_array['lot_number'] = ! empty($value[5]) ? $value[5] : null;
                $temp_array['mfg_date'] = ! empty($value[6]) ? $this->productUtil->format_date($value[6]) : null;
                $temp_array['exp_date'] = ! empty($value[7]) ? $this->productUtil->format_date($value[7]) : null;

                $formatted_data[] = $temp_array;
            }

            if (! empty($error_msg)) {
                return [
                    'success' => false,
                    'msg' => $error_msg,
                ];
            }

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $html = view('purchase.partials.imported_purchase_product_rows')
                        ->with(compact('formatted_data', 'taxes', 'currency_details', 'hide_tax', 'row_count'))->render();

            return [
                'success' => true,
                'msg' => __('lang_v.imported'),
                'html' => $html,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getPurchaseOrderLines($purchase_order_id)
    {
        $business_id = request()->session()->get('user.business_id');

        $purchase_order = Transaction::where('business_id', $business_id)
                        ->where('type', 'purchase_order')
                        ->with(['purchase_lines', 'purchase_lines.variations',
                            'purchase_lines.product', 'purchase_lines.product.unit', 'purchase_lines.variations.product_variation', ])
                        ->findOrFail($purchase_order_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();

        $sub_units_array = [];
        foreach ($purchase_order->purchase_lines as $pl) {
            $sub_units_array[$pl->id] = $this->productUtil->getSubUnits($business_id, $pl->product->unit->id, false, $pl->product_id);
        }
        $hide_tax = request()->session()->get('business.enable_inline_tax') == 1 ? '' : 'hide';
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $row_count = request()->input('row_count');

        $html = view('purchase.partials.purchase_order_lines')
                ->with(compact(
                    'purchase_order',
                    'taxes',
                    'hide_tax',
                    'currency_details',
                    'row_count',
                    'sub_units_array'
                ))->render();

        return [
            'html' => $html,
            'po' => $purchase_order,
        ];
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRefNumber(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->input('contact_id');
        $ref_no = $request->input('ref_no');
        $purchase_id = $request->input('purchase_id');

        $count = 0;
        if (! empty($contact_id) && ! empty($ref_no)) {
            //check in transactions table
            $query = Transaction::where('business_id', $business_id)
                            ->where('ref_no', $ref_no)
                            ->where('contact_id', $contact_id);
            if (! empty($purchase_id)) {
                $query->where('id', '!=', $purchase_id);
            }
            $count = $query->count();
        }
        if ($count == 0) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $taxes = TaxRate::where('business_id', $business_id)
                                ->pluck('name', 'id');
            $purchase = Transaction::where('business_id', $business_id)
                                    ->where('id', $id)
                                    ->with(
                                        'contact',
                                        'purchase_lines',
                                        'purchase_lines.product',
                                        'purchase_lines.variations',
                                        'purchase_lines.variations.product_variation',
                                        'location',
                                        'payment_lines'
                                    )
                                    ->first();
            $payment_methods = $this->productUtil->payment_types(null, false, $business_id);

            //Purchase orders
            $purchase_order_nos = '';
            $purchase_order_dates = '';
            if (! empty($purchase->purchase_order_ids)) {
                $purchase_orders = Transaction::find($purchase->purchase_order_ids);

                $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
                $order_dates = [];
                foreach ($purchase_orders as $purchase_order) {
                    $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
                }
                $purchase_order_dates = implode(', ', $order_dates);
            }

            $output = ['success' => 1, 'receipt' => [], 'print_title' => $purchase->ref_no];
            $output['receipt']['html_content'] = view('purchase.partials.show_details', compact('taxes', 'purchase', 'payment_methods', 'purchase_order_nos', 'purchase_order_dates'))->render();
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        if (! auth()->user()->can('purchase.update') && ! auth()->user()->can('purchase.update_status')) {
            abort(403, 'Unauthorized action.');
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (! $this->transactionUtil->canBeEdited($request->input('purchase_id'), $edit_days)) {
            return ['success' => 0,
                'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]), ];
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase')
                                ->with(['purchase_lines'])
                                ->findOrFail($request->input('purchase_id'));

            $before_status = $transaction->status;

            $update_data['status'] = $request->input('status');

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->purchase_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('purchase.purchase_update_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    // Purchase Export
    Public function exportPurchase(){

        $currentYear = date('Y');
        $business_id = \Illuminate\Support\Facades\Auth::user()->business_id;


    $purchases =   DB::table('transactions')
        ->leftJoin('business', 'business.id', '=', 'transactions.business_id')
        ->leftJoin('business_locations', 'business_locations.id', '=', 'transactions.location_id')
        ->leftJoin('users', 'users.id', '=', 'transactions.created_by')
        ->leftJoin('tax_rates', 'tax_rates.id', '=', 'transactions.tax_id')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->leftJoin('transaction_payments', 'transaction_payments.transaction_id', '=', 'transactions.id')
        ->where('transactions.type', '=', 'purchase')
        ->where('transactions.status', '=', 'received')
        ->where('transactions.payment_status', '=', 'paid')
        ->where('transactions.business_id', $business_id)
        ->whereYear('transactions.transaction_date', '=', $currentYear)
        ->where('contacts.type', '=', 'supplier')
        ->select(
            'transactions.id as transaction_id',
            'transactions.ref_no',
            'transactions.transaction_date', 
            'transactions.type', 
            'transactions.status', 
            'transactions.is_quotation', 
            'transactions.payment_status', 
            'transactions.total_before_tax', 
            // 'transactions.tax_id', 
            'transactions.tax_amount', 
            'transactions.discount_type', 
            'transactions.discount_amount', 
            'transactions.final_total', 
            'transactions.is_suspend', 
            'transactions.exchange_rate',
            'transactions.is_recurring',
            'transactions.recur_interval',
            'transactions.recur_interval_type',
            'users.username as create_by',
            'tax_rates.name as tax_name',
            'business.name as bus_name', 
            'business_locations.name as loc_name', 
            'contacts.name', 
            'contacts.contact_id', 
            'contacts.email', 
            'contacts.mobile',
            'transaction_payments.method',
            'transaction_payments.card_type',
            'transaction_payments.paid_on',
            'transaction_payments.is_advance',
         

            // Purchase LIne 
            // product id
            DB::raw('(SELECT GROUP_CONCAT(products.name SEPARATOR "|") 
            FROM purchase_lines 
            LEFT JOIN products ON purchase_lines.product_id = products.id
            WHERE purchase_lines.transaction_id = transactions.id) as products'),

            // variation_id
            DB::raw('(SELECT GROUP_CONCAT(variations.sub_sku SEPARATOR "|") 
            FROM purchase_lines 
            LEFT JOIN variations ON purchase_lines.variation_id = variations.id
            WHERE purchase_lines.transaction_id = transactions.id) as product_sku'),

            // quantities
            DB::raw('(SELECT GROUP_CONCAT(purchase_lines.quantity SEPARATOR "|") 
            FROM purchase_lines 
            WHERE purchase_lines.transaction_id = transactions.id) as quantities'),


            // pp_without_discount
            DB::raw('(SELECT GROUP_CONCAT(purchase_lines.pp_without_discount SEPARATOR "|") 
            FROM purchase_lines 
            WHERE purchase_lines.transaction_id = transactions.id) as pp_without_discount'),

             //  purchase_price

             DB::raw('(SELECT GROUP_CONCAT(purchase_lines.purchase_price SEPARATOR "|") 
             FROM purchase_lines 
             WHERE purchase_lines.transaction_id = transactions.id) as purchase_price'),
            

            //  purchase_price_inc_tax
               DB::raw('(SELECT GROUP_CONCAT(COALESCE(purchase_lines.purchase_price_inc_tax, "") SEPARATOR "|") 
                FROM purchase_lines 
                WHERE purchase_lines.transaction_id = transactions.id) as purchase_price_inc_tax'),

            // item_tax
            DB::raw('(SELECT GROUP_CONCAT(purchase_lines.item_tax SEPARATOR "|") 
            FROM purchase_lines 
            WHERE purchase_lines.transaction_id = transactions.id) as item_tax'),

            // Tax_id

            DB::raw('(SELECT GROUP_CONCAT(COALESCE(tax_rates.name, "") SEPARATOR "|" )
            FROM purchase_lines 
            LEFT JOIN tax_rates ON purchase_lines.tax_id = tax_rates.id
            WHERE purchase_lines.transaction_id = transactions.id) as tax_rate_name'),



            // quantity_sold
            DB::raw('(SELECT GROUP_CONCAT(purchase_lines.quantity_sold SEPARATOR "|") 
            FROM purchase_lines 
            WHERE purchase_lines.transaction_id = transactions.id) as quantity_sold'),

            // tax_id
            // DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.tax_id SEPARATOR "|") 
            // FROM transaction_sell_lines 
            // WHERE transaction_sell_lines.transaction_id = transactions.id) as tax_id'),

            // clindern_type // Type of children for the parent, like modifier or combo
           
            // DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.children_type  SEPARATOR "|") 
            // FROM transaction_sell_lines 
            // WHERE transaction_sell_lines.transaction_id = transactions.id) as children_type'),

            // Presnt sell line ID

            // DB::raw('(SELECT GROUP_CONCAT(COALESCE(transaction_sell_lines.parent_sell_line_id, "") SEPARATOR "|") 
            // FROM transaction_sell_lines 
            // WHERE transaction_sell_lines.transaction_id = transactions.id) as parent_sell_line_id'),

            // Tax rate id in transaction_sell_lines

            // DB::raw('(SELECT GROUP_CONCAT(COALESCE(tax_rates.name, "") SEPARATOR "|") 
            // FROM transaction_sell_lines 
            // LEFT JOIN tax_rates ON transaction_sell_lines.tax_id  = tax_rates.id
            // WHERE transaction_sell_lines.transaction_id = transactions.id) as tax_rates_name'),

            // DB::raw('(SELECT GROUP_CONCAT(COALESCE(tsl.parent_sell_line_id, "") SEPARATOR "|") 
            // FROM transaction_sell_lines AS tsl
            // WHERE tsl.transaction_id = transactions.id
            // ) as parent_sell_line'),

)
->get();

// dd($purchases);
    
                                      $export_data = [];
    
                                      foreach ($purchases as $purchase) {
                                          $temp = [];
                                          $temp['Ref_no'] = $purchase->ref_no;
                                          $temp['Supplier Name'] = $purchase->contact_id;
                                          $temp['Sale date'] = $purchase->transaction_date;
                                          $temp['Location'] = $purchase->loc_name;
                                          $temp['Type'] = $purchase->type;
                                          $temp['Status'] = $purchase->status;
                                          $temp['Payment Status'] = $purchase->payment_status;
                                          $temp['Toatl Before Tax'] = $purchase->total_before_tax;
                                          $temp['Tax Name'] = $purchase->tax_name;
                                          $temp['tax amount'] = $purchase->tax_amount;
                                          $temp['Discount Type'] = $purchase->discount_type;
                                          $temp['Discount Amount'] = $purchase->discount_amount;
                                          $temp['Final Total'] = $purchase->final_total;
                                          
                                        //   Purchase Line Show
                                        $temp['Product Name'] = $purchase->products;
                                        $temp['Variation Sub_Sku'] = $purchase->product_sku;
                                        $temp['Quantity'] = $purchase->quantities;
                                        $temp['pp without discount'] = $purchase->pp_without_discount;
                                        $temp['purchase price'] = $purchase->purchase_price;

                                        $temp['purchase_price_inc_tax'] = $purchase->purchase_price_inc_tax;
                                        $temp['item_tax'] = $purchase->item_tax;
                                        $temp['tax_rate_name'] = $purchase->tax_rate_name;
                                        $temp['quantity_sold'] = $purchase->quantity_sold;

                                        // Payments 

                                        $temp['method'] = $purchase->method;
                                        $temp['card_type'] = $purchase->card_type;
                                        $temp['paid_on'] = $purchase->paid_on;
                                        $temp['is_advance'] = $purchase->is_advance;

                        
                                          $export_data[] = $temp;
    
                                }
    
                                // dd($export_data);
    
                                if (ob_get_contents()) ob_end_clean();
                                ob_start();
                                return collect($export_data)->downloadExcel(
                                    'purchases_export.xlsx',
                                    null,
                                    true
                                );
        }


        // Import data

        public function ImportPurchase(Request $request)
        {

            try {
               
    
                //Set maximum php execution time
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', -1);
                
                if ($request->hasFile('purcahse_csv')) 
                {
                    $file = $request->file('purcahse_csv');
    
                    $parsed_array = Excel::toArray([], $file);
    
                    //Remove header row
                    $imported_data = array_splice($parsed_array[0], 1);
    
                    $business_id = $request->session()->get('user.business_id');
                    $user_id = $request->session()->get('user.id');
                   
    
                    $formated_data = [];
    
                    $is_valid = true;
                    $error_msg = '';
    
                    $total_rows = count($imported_data);
                
                    // DB::beginTransaction();
                    $now = Carbon::now()->toDateTimeString();
                    $row_index = 2;

                    $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');
                        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

                    foreach ($imported_data as $data) {

                        if (empty(array_filter($data))) {
                            continue;
                        }

                        $purchase_lines = [];
                        // Split product names and variations
                        $product_names = explode('|', $data[13]);
                        $variation_names = explode('|', $data[14]);
                        $quantities = explode('|', $data[15]);
                        $pp_without_discounts = explode('|', $data[16]);
                        $purchase_prices = explode('|', $data[17]);
                        $purchase_price_inc_taxs = explode('|', $data[18]);
                        $item_taxes = explode('|', $data[19]);
                        $tax_rate_names = explode('|', $data[20]);
                        $quantity_solds = explode('|', $data[21]);

                        // Payments

                        $method = $data[22];
                        $card_type = $data[23];
                        $paid_on = $data[24];
                        $is_advance = $data[25];

                       

                        for ($key = 0; $key < count($product_names); $key++) {
                            // Find the product by name and business ID
                            $product = Product::where('name', $product_names[$key])
                            ->where('business_id', $business_id)
                                ->first();
                
                                // dd($product);
                            // Check if the product exists
                            if (empty($product)) {
                                 throw new \Exception(__('Product :product_name not found in row :row_index', ['product_name' => $product_names[$key], 'row_index' => $row_index]));
                                // \Log::error(__('Product :product_name not found in row :row_index', ['product_name' => $product_names[$key], 'row_index' => $row_index]));
                                // // Move to the next iteration
                                // continue;
                            }
                
                            // Find the variation by sub SKU and product ID
                            $variation = Variation::where('sub_sku', trim($variation_names[$key]))
                            // ->where('business_id', $business_id)
                                // ->where('product_id', $product->id)
                                ->first();
                
                            // Check if the variation exists
                            if (empty($variation)) {
                                 throw new \Exception(__('Variation :variation_name for product :product_name not found in row :row_index', ['variation_name' => $variation_names[$key], 'product_name' => $product_names[$key], 'row_index' => $row_index]));
                            //         \Log::error(__('Variation :variation_name for product :product_name not found in row :row_index', ['variation_name' => $variation_names[$key], 'product_name' => $product_names[$key], 'row_index' => $row_index]));
                            // // Move to the next iteration
                            //         continue;
                            }
                
                            $tax_Rates = TaxRate::where('name', trim($tax_rate_names[$key]))
                            ->where('business_id', $business_id)
                            ->first();
                
                            $taxID = isset($tax_Rates[$key]) ? $tax_Rates[$key] : NULL; 
                            // Calculate line total
                            $line_quantity = $quantities[$key];
                            $pp_without_discount = $pp_without_discounts[$key];
                            $purchase_price = $purchase_prices[$key];
                            $purchase_price_inc_tax = $purchase_price_inc_taxs[$key];
                            $item_tax = $item_taxes[$key];
                            $quantity_sold = $quantity_solds[$key];
                            // Create sell line and add it to the array
                            $purchase_lines[] = [
                                'product_id' => $product->id,
                                'product_type' => $product->type,
                                'variation_id' => $variation->id,
                                'quantity' => $line_quantity,
                                'pp_without_discount' => $pp_without_discount,
                                'purchase_price' => $purchase_price,
                                'purchase_price_inc_tax' => $purchase_price_inc_tax,
                                'item_tax' => $item_tax,
                                'tax_id' => $taxID,
                                'quantity_sold' => $quantity_sold,
                                'discount_percent' => 0,

                            ];
            
                
                        } //Enf for loop


                        $contact = Contact::where('business_id', $business_id)
                            ->where('contact_id', $data[1])
                            ->first();

                        

                        if (!empty($data[3])) {
                            $location = BusinessLocation::where('business_id', $business_id)
                                ->where('name', $data[3])->first();
                            if (!empty($location)) {
                                $location_id = $location->id;
                            }
                        }

                
                        // Process Transactions
                        $transaction_data = [
                            'ref_no' => $data[0],
                            'location_id' => $location_id,
                            'business_id' => $business_id,
                            'transaction_date' => !empty($data[2]) ? $data[2] : $now,
                            'type' => 'purchase',
                            'status' => 'received',
                            'payment_status' => 'paid',
                            'contact_id' => $contact->id,
                            'total_before_tax' => $data[7],
                            'tax_id' => $taxID,
                            'tax_amount' => $data[9],
                            'discount_type' => !empty($data[11]) ? $data[10] : "",
                            'discount_amount' => !empty($data[12]) ? $data[11] : 0,
                            'final_total' => $data[12],
                            'created_by' => $user_id,
                        ];
                
                        $transaction = Transaction::create($transaction_data);

                        
                        
                        $this->productUtil->createOrUpdatePurchaseLinesImport($transaction, $purchase_lines, $currency_details, $enable_product_editing);

                        $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                        $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);

                        $paymnet_ref_no = "SP". $input['subscription_no'];
                        
                        //Update payment status
                        $p = $this->transactionUtil->updatePaymentStatusImport($transaction->id, $transaction->final_total ,$transaction->import_batch);
                    
                        $t = TransactionPayment::where('transaction_id' ,$transaction->id)->where('business_id' , $business_id)->first();
                        
                      
                        $t->created_by = Auth::user()->id;
                        $t->paid_on = $paid_on;
                        $t->payment_for = $transaction->contact_id;
                        $t->is_advance = $is_advance;
                        $t->payment_ref_no =  $paymnet_ref_no;
                        $t->method =  $method;
                        $t->card_type =  $card_type;
                        $t->save();


                
                    } //End foreach loop
                    
                    // Increment row index
                    $row_index++;
                } // End if statements


                $output = ['success' => 1,
                'msg' => __('Purchase imported successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        //     $output = ['success' => 0,
        //         'msg' => $e->getMessage(),
        //     ];

        //     @unlink($file_path);

        //     return redirect('import-sales')->with('notification', $output);
        }

        // @unlink($file_path);

        return redirect()->back()->with('status', $output);
        }

    
}
