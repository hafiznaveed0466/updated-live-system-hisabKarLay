<?php

namespace Modules\Connector\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use Modules\Connector\Transformers\BusinessResource;
use App\Account;
use App\Expense;
use App\Business;
use App\Category;
use Datatables;
use DB;
use App\CashRegister;
use App\Contact;
use App\User;
use App\CustomerGroup;
use App\Brands;
use App\BusinessLocation;
use App\PurchaseLine;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLinesPurchaseLines;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\TypesOfService;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\ProductUtil;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\DB as FacadesDB;
use Modules\Connector\Transformers\SellResource;
use Spatie\Activitylog\Models\Activity;

/**
 * @authenticated
 *
 */
class CommonResourceController extends ApiController
{

    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $businessUtil;
    protected $productUtil;
    protected $moduleUtil;
    protected $commonUtil;

    public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ModuleUtil $moduleUtil, Util $commonUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * List payment accounts
     * @response {
        "data": [
            {
                "id": 1,
                "business_id": 1,
                "name": "Test Account",
                "account_number": "8746888847455",
                "account_type_id": 0,
                "note": null,
                "created_by": 9,
                "is_closed": 0,
                "deleted_at": null,
                "created_at": "2020-06-04 21:34:21",
                "updated_at": "2020-06-04 21:34:21"
            }
        ]
    }
     *
     */
    public function getPaymentAccounts()
    {
        $user = Auth::user();

        $business_id = $user->business_id;

        //Accounts
        $accounts = Account::where('business_id', $business_id)
            ->get();

        return CommonResource::collection($accounts);
    }

    /**
     * List payment methods
     * @response {
        "cash": "Cash",
        "card": "Card",
        "cheque": "Cheque",
        "bank_transfer": "Bank Transfer",
        "other": "Other",
        "custom_pay_1": "Custom Payment 1",
        "custom_pay_2": "Custom Payment 2",
        "custom_pay_3": "Custom Payment 3"
    }
     *
     */
    public function getPaymentMethods()
    {
        $payment_methods = $this->productUtil->payment_types();

        return $payment_methods;
    }

    /**
     * Get business details
     * @response {
        "data": {
            "id": 1,
            "name": "Awesome Shop",
            "currency_id": 2,
            "start_date": "2018-01-01",
            "tax_number_1": "3412569900",
            "tax_label_1": "GSTIN",
            "tax_number_2": null,
            "tax_label_2": null,
            "default_sales_tax": null,
            "default_profit_percent": 25,
            "owner_id": 1,
            "time_zone": "America/Phoenix",
            "fy_start_month": 1,
            "accounting_method": "fifo",
            "default_sales_discount": "10.00",
            "sell_price_tax": "includes",
            "logo": null,
            "sku_prefix": "AS",
            "enable_product_expiry": 0,
            "expiry_type": "add_expiry",
            "on_product_expiry": "keep_selling",
            "stop_selling_before": 0,
            "enable_tooltip": 1,
            "purchase_in_diff_currency": 0,
            "purchase_currency_id": null,
            "p_exchange_rate": "1.000",
            "transaction_edit_days": 30,
            "stock_expiry_alert_days": 30,
            "keyboard_shortcuts": {
                "pos": {
                    "express_checkout": "shift+e",
                    "pay_n_ckeckout": "shift+p",
                    "draft": "shift+d",
                    "cancel": "shift+c",
                    "recent_product_quantity": "f2",
                    "weighing_scale": null,
                    "edit_discount": "shift+i",
                    "edit_order_tax": "shift+t",
                    "add_payment_row": "shift+r",
                    "finalize_payment": "shift+f",
                    "add_new_product": "f4"
                }
            },
            "pos_settings": {
                "amount_rounding_method": null,
                "disable_pay_checkout": 0,
                "disable_draft": 0,
                "disable_express_checkout": 0,
                "hide_product_suggestion": 0,
                "hide_recent_trans": 0,
                "disable_discount": 0,
                "disable_order_tax": 0,
                "is_pos_subtotal_editable": 0
            },
            "weighing_scale_setting": {
                "label_prefix": null,
                "product_sku_length": "4",
                "qty_length": "3",
                "qty_length_decimal": "2"
            },
            "manufacturing_settings": null,
            "essentials_settings": null,
            "ecom_settings": null,
            "woocommerce_wh_oc_secret": null,
            "woocommerce_wh_ou_secret": null,
            "woocommerce_wh_od_secret": null,
            "woocommerce_wh_or_secret": null,
            "enable_brand": 1,
            "enable_category": 1,
            "enable_sub_category": 1,
            "enable_price_tax": 1,
            "enable_purchase_status": 1,
            "enable_lot_number": 0,
            "default_unit": null,
            "enable_sub_units": 0,
            "enable_racks": 0,
            "enable_row": 0,
            "enable_position": 0,
            "enable_editing_product_from_purchase": 1,
            "sales_cmsn_agnt": null,
            "item_addition_method": 1,
            "enable_inline_tax": 1,
            "currency_symbol_placement": "before",
            "enabled_modules": [
                "purchases",
                "add_sale",
                "pos_sale",
                "stock_transfers",
                "stock_adjustment",
                "expenses",
                "account",
                "tables",
                "modifiers",
                "service_staff",
                "booking",
                "kitchen",
                "subscription",
                "types_of_service"
            ],
            "date_format": "m/d/Y",
            "time_format": "24",
            "ref_no_prefixes": {
                "purchase": "PO",
                "purchase_return": null,
                "stock_transfer": "ST",
                "stock_adjustment": "SA",
                "sell_return": "CN",
                "expense": "EP",
                "contacts": "CO",
                "purchase_payment": "PP",
                "sell_payment": "SP",
                "expense_payment": null,
                "business_location": "BL",
                "username": null,
                "subscription": null
            },
            "theme_color": null,
            "created_by": null,
            "enable_rp": 0,
            "rp_name": null,
            "amount_for_unit_rp": "1.0000",
            "min_order_total_for_rp": "1.0000",
            "max_rp_per_order": null,
            "redeem_amount_per_unit_rp": "1.0000",
            "min_order_total_for_redeem": "1.0000",
            "min_redeem_point": null,
            "max_redeem_point": null,
            "rp_expiry_period": null,
            "rp_expiry_type": "year",
            "repair_settings": null,
            "email_settings": {
                "mail_driver": "smtp",
                "mail_host": null,
                "mail_port": null,
                "mail_username": null,
                "mail_password": null,
                "mail_encryption": null,
                "mail_from_address": null,
                "mail_from_name": null
            },
            "sms_settings": {
                "url": null,
                "send_to_param_name": "to",
                "msg_param_name": "text",
                "request_method": "post",
                "param_1": null,
                "param_val_1": null,
                "param_2": null,
                "param_val_2": null,
                "param_3": null,
                "param_val_3": null,
                "param_4": null,
                "param_val_4": null,
                "param_5": null,
                "param_val_5": null,
                "param_6": null,
                "param_val_6": null,
                "param_7": null,
                "param_val_7": null,
                "param_8": null,
                "param_val_8": null,
                "param_9": null,
                "param_val_9": null,
                "param_10": null,
                "param_val_10": null
            },
            "custom_labels": {
                "payments": {
                    "custom_pay_1": null,
                    "custom_pay_2": null,
                    "custom_pay_3": null
                },
                "contact": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "product": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "location": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "user": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "purchase": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "sell": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                },
                "types_of_service": {
                    "custom_field_1": null,
                    "custom_field_2": null,
                    "custom_field_3": null,
                    "custom_field_4": null
                }
            },
            "common_settings": {
                "default_datatable_page_entries": "25"
            },
            "is_active": 1,
            "created_at": "2018-01-04 02:15:19",
            "updated_at": "2020-06-04 22:33:01",
            "locations": [
                {
                    "id": 1,
                    "business_id": 1,
                    "location_id": null,
                    "name": "Awesome Shop",
                    "landmark": "Linking Street",
                    "country": "USA",
                    "state": "Arizona",
                    "city": "Phoenix",
                    "zip_code": "85001",
                    "invoice_scheme_id": 1,
                    "invoice_layout_id": 1,
                    "selling_price_group_id": null,
                    "print_receipt_on_invoice": 1,
                    "receipt_printer_type": "browser",
                    "printer_id": null,
                    "mobile": null,
                    "alternate_number": null,
                    "email": null,
                    "website": null,
                    "featured_products": [
                        "5",
                        "71"
                    ],
                    "is_active": 1,
                    "default_payment_accounts": {
                        "cash": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "card": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "cheque": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "bank_transfer": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "other": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "custom_pay_1": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "custom_pay_2": {
                            "is_enabled": "1",
                            "account": null
                        },
                        "custom_pay_3": {
                            "is_enabled": "1",
                            "account": null
                        }
                    },
                    "custom_field1": null,
                    "custom_field2": null,
                    "custom_field3": null,
                    "custom_field4": null,
                    "deleted_at": null,
                    "created_at": "2018-01-04 02:15:20",
                    "updated_at": "2020-06-05 00:56:54"
                }
            ],
            "currency": {
                "id": 2,
                "country": "America",
                "currency": "Dollars",
                "code": "USD",
                "symbol": "$",
                "thousand_separator": ",",
                "decimal_separator": ".",
                "created_at": null,
                "updated_at": null
            },
            "printers": [],
            "currency_precision": 2,
            "quantity_precision": 2
        }
    }
     *
     */
    public function getBusinessDetails()
    {
        $user = Auth::user();

        $business = Business::with(['locations', 'currency', 'printers'])
            ->findOrFail($user->business_id);

        return new BusinessResource($business);
    }

    /**
     * Get profit and loss report
     * @queryParam location_id optional id of the location Example: 1
     * @queryParam start_date optional format:Y-m-d Example: 2018-06-25
     * @queryParam end_date optional format:Y-m-d Example: 2018-06-25
     * @queryParam user_id optional id of the user Example: 1
     *
     *@response {
        "data": {
            "total_purchase_shipping_charge": 0,
            "total_sell_shipping_charge": 0,
            "total_transfer_shipping_charges": "0.0000",
            "opening_stock": 0,
            "closing_stock": "386859.00000000",
            "total_purchase": 386936,
            "total_purchase_discount": "0.000000000000",
            "total_purchase_return": "0.0000",
            "total_sell": 9764.5,
            "total_sell_discount": "11.550000000000",
            "total_sell_return": "0.0000",
            "total_sell_round_off": "0.0000",
            "total_expense": "0.0000",
            "total_adjustment": "0.0000",
            "total_recovered": "0.0000",
            "total_reward_amount": "0.0000",
            "left_side_module_data": [
                {
                    "value": "0.0000",
                    "label": "Total Payroll",
                    "add_to_net_profit": true
                },
                {
                    "value": 0,
                    "label": "Total Production Cost",
                    "add_to_net_profit": true
                }
            ],
            "right_side_module_data": [],
            "net_profit": 9675.95,
            "gross_profit": -11.55,
            "total_sell_by_subtype": []
        }
    }
     */
    public function getProfitLoss()
    {
        $user = Auth::user();
        $business_id = $user->business_id;
        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

        $location_id = !empty(request()->input('location_id')) ? request()->input('location_id') : null;
        $start_date = !empty(request()->input('start_date')) ? request()->input('start_date') : $fy['start'];
        $end_date = !empty(request()->input('end_date')) ? request()->input('end_date') : $fy['end'];

        $user_id = request()->input('user_id') ?? null;

        $data = $this->transactionUtil->getProfitLossDetails($business_id, $location_id, $start_date, $end_date, $user_id);

        return [
            'data' => $data
        ];
    }

    /**
     * Get product current stock
     * @response {
        "data": [
            {
                "total_sold": null,
                "total_transfered": null,
                "total_adjusted": null,
                "stock_price": null,
                "stock": null,
                "sku": "AS0001",
                "product": "Men's Reverse Fleece Crew",
                "type": "single",
                "product_id": 1,
                "unit": "Pc(s)",
                "enable_stock": 1,
                "unit_price": "143.0000",
                "product_variation": "DUMMY",
                "variation_name": "DUMMY",
                "location_name": null,
                "location_id": null,
                "variation_id": 1
            },
            {
                "total_sold": "50.0000",
                "total_transfered": null,
                "total_adjusted": null,
                "stock_price": "3850.00000000",
                "stock": "50.0000",
                "sku": "AS0002-1",
                "product": "Levis Men's Slimmy Fit Jeans",
                "type": "variable",
                "product_id": 2,
                "unit": "Pc(s)",
                "enable_stock": 1,
                "unit_price": "77.0000",
                "product_variation": "Waist Size",
                "variation_name": "28",
                "location_name": "Awesome Shop",
                "location_id": 1,
                "variation_id": 2
            },
            {
                "total_sold": "60.0000",
                "total_transfered": null,
                "total_adjusted": null,
                "stock_price": "6930.00000000",
                "stock": "90.0000",
                "sku": "AS0002-2",
                "product": "Levis Men's Slimmy Fit Jeans",
                "type": "variable",
                "product_id": 2,
                "unit": "Pc(s)",
                "enable_stock": 1,
                "unit_price": "77.0000",
                "product_variation": "Waist Size",
                "variation_name": "30",
                "location_name": "Awesome Shop",
                "location_id": 1,
                "variation_id": 3
            }
        ],
        "links": {
            "first": "http://local.pos.com/connector/api/product-stock-report?page=1",
            "last": "http://local.pos.com/connector/api/product-stock-report?page=22",
            "prev": null,
            "next": "http://local.pos.com/connector/api/product-stock-report?page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 22,
            "path": "http://local.pos.com/connector/api/product-stock-report",
            "per_page": 3,
            "to": 3,
            "total": 66
        }
    }
     */
    public function getProductStock()
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $filters = request()->only([
            'location_id', 'category_id', 'sub_category_id',
            'brand_id', 'unit_id', 'tax_id', 'type',
            'only_mfg_products', 'active_state',
            'not_for_selling', 'repair_model_id',
            'product_id', 'active_state'
        ]);

        $products = $this->productUtil->getProductStockDetails($business_id, $filters, 'api');
        return CommonResource::collection($products);
    }

    /**
     * Get notifications
     * @response {
            "data": [
                {
                    "msg": "Payroll for August/2020 added by Mr. Super Admin. Reference No. 2020/0002",
                    "icon_class": "fas fa-money-bill-alt bg-green",
                    "link": "http://local.pos.com/hrm/payroll",
                    "read_at": null,
                    "created_at": "3 hours ago"
                }
            ]
        }
     */
    public function getNotifications()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'DESC')->get();

        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return new CommonResource($notifications_data);
    }

    /**
     * Get location details from coordinates
     * @bodyParam lat decimal required Lattitude of the location Example: 41.40338
     * @bodyParam lon decimal required Longitude of the location Example: 2.17403
     * @response {
        "address": "Radhanath Mullick Ln, Tiretta Bazaar, Bow Bazaar, Kolkata, West Bengal, 700 073, India"
    }
     *
     */
    public function getLocation()
    {
        $lat = request()->input('lat');
        $lon = request()->input('lon');

        $address = '';
        if (!empty($lat) && !empty($lon)) {
            $address = $this->moduleUtil->getLocationFromCoordinates($lat, $lon);
        }

        return [
            'address' => $address
        ];
    }

    public function getexpense(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->business_id;
        //
        $filters = $request->only(['category', 'location_id']);

        $date_range = $request->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        } else {
            $filters['start_date'] = \Carbon::now()->startOfMonth()->format('Y-m-d');
            $filters['end_date'] = \Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $data = $this->transactionUtil->getExpenseReport($business_id, $filters);

        return [
            'data' => $data
        ];
    }
    public function getStockReport()
    {

        $user = Auth::user();
        $business_id = $user->business_id;
        $filters = request()->only([
            'location_id', 'category_id', 'sub_category_id', 'brand_id', 'unit_id', 'tax_id', 'type',
            'only_mfg_products', 'active_state',  'not_for_selling', 'repair_model_id', 'product_id', 'active_state'
        ]);

        $filters['not_for_selling'] = isset($filters['not_for_selling']) && $filters['not_for_selling'] == 'true' ? 1 : 0;
        // Return the details in ajax call
        $for = request()->input('for') == 'view_product' ? 'view_product' : 'datatables';

        $data = $this->productUtil->getProductStockDetails($business_id, $filters, $for)->get();




        return [
            'data' => $data,

        ];
    }


    public function getRegisterReport(Request $request)
    {
         $user = Auth::user();
        $business_id = $user->business_id;

        //Return the details in ajax call
        // if ($request->ajax()) {
            $registers = CashRegister::leftjoin(
                'cash_register_transactions as ct',
                'ct.cash_register_id',
                '=',
                'cash_registers.id'
            )->join(
                'users as u',
                'u.id',
                '=',
                'cash_registers.user_id'
            )
                ->leftJoin(
                    'business_locations as bl',
                    'bl.id',
                    '=',
                    'cash_registers.location_id'
                )
                ->where('cash_registers.business_id', $business_id)
                ->select(
                    'cash_registers.*',
                    DB::raw(
                        "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '', COALESCE(u.email, '')) as user_name"
                    ),
                    'bl.name as location_name',
                    DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='sell', amount, 0), 0)) as total_cash_payment"),
                    DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='sell', amount, 0), 0)) as total_cheque_payment"),
                    DB::raw("SUM(IF(pay_method='card', IF(transaction_type='sell', amount, 0), 0)) as total_card_payment"),
                    DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='sell', amount, 0), 0)) as total_bank_transfer_payment"),
                    DB::raw("SUM(IF(pay_method='other', IF(transaction_type='sell', amount, 0), 0)) as total_other_payment"),
                    DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='sell', amount, 0), 0)) as total_advance_payment"),
                    DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_1"),
                    DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_2"),
                    DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_3"),
                    DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_4"),
                    DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_5"),
                    DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_6"),
                    DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_7")
                )->groupby('cash_registers.id')->get();

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $registers->whereIn('cash_registers.location_id', $permitted_locations);
            }

            if (!empty($request->input('user_id'))) {
                $registers->where('cash_registers.user_id', $request->input('user_id'));
            }
            if (!empty($request->input('status'))) {
                $registers->where('cash_registers.status', $request->input('status'));
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $registers->whereDate('cash_registers.created_at', '>=', $start_date)
                    ->whereDate('cash_registers.created_at', '<=', $end_date);
            }
           
        // }

        // $users = User::forDropdown($business_id, false);
        // $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        return [
          
                'registers'=>  $registers 
        ];
    }

    public function getCustomerSuppliers(Request $request)
    {



        $user = Auth::user();
        $business_id = $user->business_id;
        //Return the details in ajax call

        // if ($request->ajax()) {

            // Return the details in ajax call//
            // if ($request->ajax()) {
                $contacts = Contact::where('contacts.business_id', $business_id)
                    ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    ->active()
                    ->groupBy('contacts.id')
                    ->select(
                        DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                        DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                        DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                        DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                        DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                        'contacts.supplier_business_name',
                        'contacts.name',
                        'contacts.id',
                        'contacts.type as contact_type'
                    )->get();
               
   
                return Datatables::of($contacts)
    
    
                    ->addColumn('due', function ($row) {
                        $due = ($row->total_invoice - $row->invoice_received - $row->total_sell_return + $row->sell_return_paid) - ($row->total_purchase - $row->total_purchase_return + $row->purchase_return_received - $row->purchase_paid);
    
                        if ($row->contact_type == 'supplier') {
                            $due -= $row->opening_balance - $row->opening_balance_paid;
                        } else {
                            $due += $row->opening_balance - $row->opening_balance_paid;
                        }
                     
    
                        // $due = $this->transactionUtil->num_f($due, true);
                        // dd($due_formatted);
                        return    $due  ;
                    })
                   
    
                    
               
                    // ->rawColumns(['total_purchase', 'total_invoice', 'due', 'name', 'total_purchase_return', 'total_sell_return', 'opening_balance_due'])
                    ->make(true);
            // }    
    
            $customer_group = CustomerGroup::forDropdown($business_id, false, true);
            $types = [
                '' => __('lang_v1.all'),
                'customer' => __('report.customer'),
                'supplier' => __('report.supplier')
            ];
    



        return [
            'contacts' => $contacts,

        ];
    }


    public function getproductSellReport(Request $request)
    {


        $user = Auth::user();
        $business_id = $user->business_id;
        $variation_id = $request->get('variation_id', null);
        // if ($request->ajax()) {
        //    
        $query = TransactionSellLine::join(
            'transactions as t',
            'transaction_sell_lines.transaction_id',
            '=',
            't.id'
        )
            ->join(
                'variations as v',
                'transaction_sell_lines.variation_id',
                '=',
                'v.id'
            )
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select(
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as customer',
                'c.supplier_business_name',
                'c.contact_id',
                't.id as transaction_id',
                't.invoice_no',
                't.transaction_date as transaction_date',
                'transaction_sell_lines.unit_price_before_discount as unit_price',
                'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                'transaction_sell_lines.line_discount_type as discount_type',
                'transaction_sell_lines.line_discount_amount as discount_amount',
                'transaction_sell_lines.item_tax',
                'tax_rates.name as tax',
                'u.short_name as unit',
                'transaction_sell_lines.parent_sell_line_id',
                DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
            )
            ->get('transaction_sell_lines.id');

        if (!empty($variation_id)) {
            $query->where('transaction_sell_lines.variation_id', $variation_id);
        }
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->where('t.transaction_date', '>=', $start_date)
                ->where('t.transaction_date', '<=', $end_date);
        }

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $customer_id = $request->get('customer_id', null);
        if (!empty($customer_id)) {
            $query->where('t.contact_id', $customer_id);
        }

        $customer_group_id = $request->get('customer_group_id', null);
        if (!empty($customer_group_id)) {
            $query->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                ->where('CG.id', $customer_group_id);
        }

        $category_id = $request->get('category_id', null);
        if (!empty($category_id)) {
            $query->where('p.category_id', $category_id);
        }

        $brand_id = $request->get('brand_id', null);
        if (!empty($brand_id)) {
            $query->where('p.brand_id', $brand_id);
        }

        return Datatables::of($query)
            ->editColumn('product_name', function ($row) {
                $product_name = $row->product_name;
                if ($row->product_type == 'variable') {
                    $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                }

                return $product_name;
            })
            ->editColumn('invoice_no', function ($row) {
                return
                    $row->invoice_no;
            })
            ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
            ->editColumn('unit_sale_price', function ($row) {
                return  $row->unit_sale_price;
            })
            ->editColumn('sell_qty', function ($row) {
                //ignore child sell line of combo product
                $class = is_null($row->parent_sell_line_id) ? '' : '';

                return  $class  . (float)$row->sell_qty  . $row->unit;
            })
            ->editColumn('subtotal', function ($row) {
                //ignore child sell line of combo product
                $class = is_null($row->parent_sell_line_id) ? 'row_subtotal' : '';
                return  $row->subtotal;
            })
            ->editColumn('unit_price', function ($row) {
                return  $row->unit_price;
            })
            // ->editColumn('discount_amount', '
            //     @if($discount_type == "percentage")
            //         {{@number_format($discount_amount)}} %
            //     @elseif($discount_type == "fixed")
            //         {{@number_format($discount_amount)}}
            //     @endif  
            //     ')
            ->editColumn('tax', function ($row) {
                return
                    $row->item_tax .
                    (float)$row->item_tax . $row->tax;
            })
            ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}},<br>@endif {{$customer}}')
            ->rawColumns(['invoice_no', 'unit_sale_price', 'subtotal', 'sell_qty', 'discount_amount', 'unit_price', 'tax', 'customer'])
            ->make(true);
        // }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $customer_group = CustomerGroup::forDropdown($business_id, false, true);

        return [
            'business_locations' => $business_locations,
            'customers' => $customers,
            'categories' => $categories,
            'brands' => $brands,
            'customer_group' => $customer_group,
            'query' => $query,
            'variation_id' => $variation_id,

        ];
    }
    public function getTrendingProducts()
    {

        $user = Auth::user();
        $business_id = $user->business_id;
        $filters = request()->only(['category', 'sub_category', 'brand', 'unit', 'limit', 'location_id', 'product_type']);

        $date_range = request()->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        }

        $products = $this->productUtil->getTrendingProducts($business_id, $filters);
        return [
            'products' => $products,
        ];
    }
    
    
    
    
    public function activityLog()
    {

        $user = Auth::user();
        $business_id = $user->business_id;
        $transaction_types = [
            'contact' => __('report.contact'),
            'user' => __('report.user'),
            'sell' => __('sale.sale'),
            'purchase' => __('lang_v1.purchase'),
            'sales_order' => __('lang_v1.sales_order'),
            'purchase_order' => __('lang_v1.purchase_order'),
            'sell_return' => __('lang_v1.sell_return'),
            'purchase_return' => __('lang_v1.purchase_return'),
            'sell_transfer' => __('lang_v1.stock_transfer'),
            'stock_adjustment' => __('stock_adjustment.stock_adjustment'),
            'expense' => __('lang_v1.expense')
        ];

        $activities = Activity::with(['subject'])

            ->leftjoin('users as u', 'u.id', '=', 'activity_log.causer_id')
            ->where('activity_log.business_id', $business_id)
            ->select(
                'activity_log.*',
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by")
            )->get();


        $users = User::allUsersDropdown($business_id, false);



        return [
            'users' => $users,
            'activities' => $activities,
        ];
    }

    public function getkot(Request $request)
    {


        $user = Auth::user();
        $business_id = $user->business_id;



        $kot = TransactionSellLine::join(
            'transactions',
            'transaction_sell_lines.transaction_id',
            '=',
            'transactions.id'


        )
            ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')

            ->leftjoin(
                'types_of_services as ts',
                'transactions.types_of_service_id',
                '=',
                'ts.id'
            )->leftjoin('res_tables', 'transactions.res_table_id', '=', 'ts.id')
            ->leftjoin(
                'users as u',
                'transactions.res_waiter_id',
                '=',
                'u.id'
            )
            ->leftJoin(
                'business_locations as bl',
                'transactions.location_id',
                '=',
                'bl.id'
            )


            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', '=', 'sell')

            ->select(
                'res_tables.name as table',
                'transactions.invoice_no',
                FacadesDB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
                'transactions.res_waiter_id as transaction_waiter',
                'transactions.types_of_service_id as types_of_service_id',
                'transactions.additional_notes as additional_notes',
                'transactions.contact_id as contact_id',
                'transactions.packing_charge',
                'transactions.total_before_tax',
                'c.name as cname',
                'transactions.location_id as tlocation_id',
                'ts.name as name',
                DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                'u.first_name as fname',
                'bl.location_id as location_id',


            )
            ->groupby('transactions.invoice_no')->get();




        return
            [
                'data' => $kot
            ];
    }


    public function TypeOfService(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->business_id;

        $sell_details = TransactionSellLine::join(
            'transactions',
            'transaction_sell_lines.transaction_id',
            '=',
            'transactions.id'


        )
            ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->leftjoin(
                'types_of_services as ts',
                'transactions.types_of_service_id',
                '=',
                'ts.id'
            )
            ->leftjoin(
                'users as u',
                'transactions.res_waiter_id',
                '=',
                'u.id'
            )
            ->leftJoin(
                'business_locations as bl',
                'transactions.location_id',
                '=',
                'bl.id'
            )->where('transactions.business_id', $business_id)
            ->where('transactions.types_of_service_id', '!=', 'null')

            ->select(

                'transactions.invoice_no',
                'transactions.transaction_date as transaction_date',
                'transactions.res_waiter_id as transaction_waiter',
                'transactions.types_of_service_id as types_of_service_id',
                'transactions.contact_id as contact_id',
                'transactions.packing_charge',
                'transactions.total_before_tax',
                'c.name as customer_name',

                'ts.name as type_name',
                'u.first_name as Service_Staf',
                'bl.location_id as location_id',


            )->groupby('transactions.invoice_no')->get();



        return [
            'data' => $sell_details,

        ];
    }
    public function getproductPurchaseReport(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->business_id;


        $variation_id = $request->get('variation_id', null);
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
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->select(
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as supplier',
                'c.supplier_business_name',
                't.id as transaction_id',
                't.ref_no',
                't.transaction_date as transaction_date',
                'purchase_lines.purchase_price_inc_tax as unit_purchase_price',
                DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                'purchase_lines.quantity_adjusted',
                'u.short_name as unit',
                DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal')
            )
            ->groupBy('purchase_lines.id')->get();




        return [
            'data' => $query
        ];
    }


    public function sellPaymentReport(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->business_id;

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        $customer_id = $request->get('supplier_id', null);
        $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
        $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

        $location_id = $request->get('location_id', null);
        $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

        $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
            $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->whereIn('t.type', ['sell', 'opening_balance']);
        })
            ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('sell', 'opening_balance') $parent_payment_query_part $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('sell', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })
            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL, 
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id 
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT CONCAT(COALESCE(CONCAT(c.supplier_business_name, '<br>'), ''), c.name) FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id 
                                )
                            ) as customer"),
                'transaction_payments.amount',
                'transaction_payments.is_return',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                'transaction_payments.document',
                'transaction_payments.transaction_no',
                't.invoice_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_payments.id as DT_RowId',
                'CG.name as customer_group'
            )
            ->groupBy('transaction_payments.id')->get();



        return [
            'data' => $query
        ];
    }

    public function purchasePaymentReport(Request $request)
    {


        $user = Auth::user();
        $business_id = $user->business_id;

        $supplier_id = $request->get('supplier_id', null);
        $contact_filter1 = !empty($supplier_id) ? "AND t.contact_id=$supplier_id" : '';
        $contact_filter2 = !empty($supplier_id) ? "AND transactions.contact_id=$supplier_id" : '';

        $location_id = $request->get('location_id', null);

        $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

        $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
            $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->whereIn('t.type', ['purchase', 'opening_balance']);
        })
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('purchase', 'opening_balance')  $parent_payment_query_part $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('purchase', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })

            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL, 
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id 
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT CONCAT(COALESCE(c.supplier_business_name, ''), '<br>', c.name) FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id 
                                )
                            ) as supplier"),
                'transaction_payments.amount',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                'transaction_payments.document',
                't.ref_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_no',
                'transaction_payments.id as DT_RowId'
            )
            ->groupBy('transaction_payments.id')->get();



        return [
            'data' => $query
        ];
    }
    
    
    

    
    
    public function getPurchaseSell(Request $request)
    {
     
        $user = Auth::user();
        $business_id = $user->business_id;

        //Return the details in ajax call
        // if ($request->ajax()) {
            // $start_date = $request->get('start_date');
            // $end_date = $request->get('end_date');

            // $location_id = $request->get('location_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id);

            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                // $start_date,
                // $end_date,
                // $location_id
            );

            $transaction_types = [
                'purchase_return', 'sell_return'
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                // $start_date,
                // $end_date,
                // $location_id
            );

            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];
            $total_sell_return_inc_tax = $transaction_totals['total_sell_return_inc_tax'];

            $difference = [
                'total' =>  number_format($sell_details['total_sell_inc_tax'] - $total_sell_return_inc_tax - ($purchase_details['total_purchase_inc_tax'] - $total_purchase_return_inc_tax) , 2),
                'due' => number_format($sell_details['invoice_due'] - $purchase_details['purchase_due'] ,2)
            ];

            return [
                'purchase' => $purchase_details,
                'sell' => $sell_details,
                'total_purchase_return' => $total_purchase_return_inc_tax,
                'total_sell_return' => $total_sell_return_inc_tax,
                'difference' => $difference
            ];
        // }

        // $business_locations = BusinessLocation::forDropdown($business_id, true);

        // return [
        //     'business_locations' => $business_locations
        // ];
    }
    public function getSalesRepresentativeTotalSell(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        //Return the details in ajax call
        // if ($request->ajax()) {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $location_id = $request->get('location_id');
        $created_by = $request->get('created_by');

        $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id, $created_by);

        //Get Sell Return details
        $transaction_types = [
            'sell_return'
        ];
        $sell_return_details = $this->transactionUtil->getTransactionTotals(
            $business_id,
            $transaction_types,
            $start_date,
            $end_date,
            $location_id,
            $created_by
        );

       $total_expense = $this->transactionUtil->getExpenseReport($business_id,'total');

           


        $total_sell_return = !empty($sell_return_details['total_sell_return_exc_tax']) ? $sell_return_details['total_sell_return_exc_tax'] : 0;
        $total_sell = $sell_details['total_sell_exc_tax'] - $total_sell_return;
  $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id') 
  ->join(
                            'business_locations AS bl',
                            'transactions.location_id',
                            '=',
                            'bl.id'
                        )
                        ->leftJoin('tax_rates as tr', 'transactions.tax_id', '=', 'tr.id')
                        ->leftJoin('users AS U', 'transactions.expense_for', '=', 'U.id')
                        ->leftJoin('users AS usr', 'transactions.created_by', '=', 'usr.id')
                        ->leftJoin('contacts AS c', 'transactions.contact_id', '=', 'c.id')
                        ->leftJoin(
                            'transaction_payments AS TP',
                            'transactions.id',
                            '=',
                            'TP.transaction_id'
                        )
                        ->where('transactions.business_id', $business_id)
                        ->whereIn('transactions.type', ['expense', 'expense_refund'])
                        ->select(
                            'transactions.id',
                            'transactions.document',
                            'transaction_date',
                            'ref_no',
                            'ec.name as category',
                            'payment_status',
                            'additional_notes',
                            'final_total',
                            'transactions.is_recurring',
                            'transactions.recur_interval',
                            'transactions.recur_interval_type',
                            'transactions.recur_repetitions',
                            'transactions.subscription_repeat_on',
                            'bl.name as location_name',
                            DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as expense_for"),
                            DB::raw("CONCAT(tr.name ,' (', tr.amount ,' )') as tax"),
                            DB::raw('SUM(TP.amount) as amount_paid'),
                            DB::raw("CONCAT(COALESCE(usr.surname, ''),' ',COALESCE(usr.first_name, ''),' ',COALESCE(usr.last_name,'')) as added_by"),
                            'transactions.recur_parent_id',
                            'c.name as contact_name',
                            'transactions.type'
                        )
                        ->with(['recurring_parent'])
                        ->groupBy('transactions.id')->get();
         
        return [
            'total_sell_exc_tax' => $sell_details['total_sell_exc_tax'],
            'total_sell_return_exc_tax' => $total_sell_return,
            'total_sell' => $total_sell,
              'total_expense'=>$total_expense,
            'sell_details'=>$sell_details,
            'sell_return_details'=>$sell_return_details,
            'expenses'=>$expenses
          
          
            
        ];
    }







    public function getStockAdjustmentReport(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->business_id;


        $query =  Transaction::where('business_id', $business_id)
            ->where('type', 'stock_adjustment');



        $stock_adjustment_details = $query->select(
            DB::raw("SUM(final_total) as total_amount"),
            DB::raw("SUM(total_amount_recovered) as total_recovered"),
            DB::raw("SUM(IF(adjustment_type = 'normal', final_total, 0)) as total_normal"),
            DB::raw("SUM(IF(adjustment_type = 'abnormal', final_total, 0)) as total_abnormal")
        )->first();
        // return $stock_adjustment_details;
        $stock_adjustments = Transaction::join(
            'business_locations AS BL',
            'transactions.location_id',
            '=',
            'BL.id'
        )
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'stock_adjustment')
            ->select(
                'transactions.id',
                'transaction_date',
                'ref_no',
                'BL.name as location_name',
                'adjustment_type',
                'final_total',
                'total_amount_recovered',
                'additional_notes',
                'transactions.id as DT_RowId',
                DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by")
            )->get();

        return [
            'stock_adjustment_details' => $stock_adjustment_details,
            'stock_adjustments' => $stock_adjustments
        ];
    }




    public function gettimeReport(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $sell_details = TransactionSellLine::join(

                'products AS p',
                'transaction_sell_lines.product_id',
                '=',
                'p.id'
            )
            ->join(
                'transactions AS t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'


            )
            ->leftjoin(
                'types_of_services as ts',
                't.types_of_service_id',
                '=',
                'ts.id'
            )
            ->leftjoin(
                'users as u',
                't.res_waiter_id',
                '=',
                'u.id'
            )
            ->leftJoin(
                'business_locations as bl',
                't.location_id',
                '=',
                'bl.id'
            )
->where('t.business_id', $business_id)
            ->select(
                'p.name as product_name',
                'p.id as product_id',
                'p.time as product_time',
                't.invoice_no as transaction_invoiceno',
                't.transaction_date as transaction_date',
                't.res_waiter_id as transaction_waiter',
                't.location_id as tlocation_id',
                'transaction_sell_lines.update_time as update_time',
                'transaction_sell_lines.total_time as cooked_time',
                'ts.name as name',
                'u.first_name as fname',
                'bl.location_id as location_id'


            )
            ->get();

        if ($request->ajax()) {
            $sell_details = TransactionSellLine::join(

                    'products AS p',
                    'transaction_sell_lines.product_id',
                    '=',
                    'p.id'
                )
                ->join(
                    'transactions AS t',
                    'transaction_sell_lines.transaction_id',
                    '=',
                    't.id'


                )
                ->leftjoin(
                    'types_of_services as ts',
                    't.types_of_service_id',
                    '=',
                    'ts.id'
                )
                ->leftjoin(
                    'users as u',
                    't.res_waiter_id',
                    '=',
                    'u.id'
                )
                ->leftJoin(
                    'business_locations as bl',
                    't.location_id',
                    '=',
                    'bl.id'
                )
->where('t.business_id', $business_id)
                ->select(
                    'p.name as product_name',
                    'p.id as product_id',
                    'p.time as product_time',
                    't.invoice_no as transaction_invoiceno',
                    't.transaction_date as transaction_date',
                    't.res_waiter_id as transaction_waiter',
                    't.location_id as location_id',
                    'transaction_sell_lines.update_time as update_time',
                    'transaction_sell_lines.total_time as cooked_time',
                    'ts.name as name',
                    'u.first_name as fname',
                    'bl.location_id'


                )->where(
                    't.res_waiter_id',
                    '=',
                    $request->location
                )
                ->get();

            return response()->json(['selldetails' => $sell_details]);
        }

        return [
            'data' => $sell_details
        ];
    }


    public function getCustomerGroup(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $query = Transaction::leftjoin('customer_groups AS CG', 'transactions.customer_group_id', '=', 'CG.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->groupBy('transactions.customer_group_id')
            ->select(DB::raw("SUM(final_total) as total_sell"), 'CG.name')->get();

        // return Datatables::of($query)
        //     ->editColumn('total_sell', function ($row) {
        //         return  $row->total_sell ;
        //     })
        //     ->rawColumns(['total_sell'])
        //     ->make(true);




        return [
            'data' => $query
        ];
    }


    public function getTaxReport(Request $request)
    {
      

        $user = Auth::user();
        $business_id = $user->business_id;

        // //Return the details in ajax call
        // if ($request->ajax()) {
        //     $start_date = $request->get('start_date');
        //     $end_date = $request->get('end_date');
        //     $location_id = $request->get('location_id');

            $input_tax_details = $this->transactionUtil->getInputTax($business_id);

            $output_tax_details = $this->transactionUtil->getOutputTax($business_id);

            $expense_tax_details = $this->transactionUtil->getExpenseTax($business_id);

            $module_output_taxes = $this->moduleUtil->getModuleData('getModuleOutputTax');

            $total_module_output_tax = 0;
            foreach ($module_output_taxes as $key => $module_output_tax) {
                $total_module_output_tax += $module_output_tax;
            }

            $total_output_tax = $output_tax_details['total_tax'] + $total_module_output_tax;
            
            $tax_diff = $total_output_tax - $input_tax_details['total_tax'] - $expense_tax_details['total_tax'];

            return [
                    'tax_diff' => $tax_diff,
                    'input_tax_details'=>$input_tax_details,
                    'output_tax_details'=>$output_tax_details,
                    'expense_tax_details'=>$expense_tax_details
                ];

    }
    
      public function itemsReport()
    {
    $user = Auth::user();
        $business_id = $user->business_id;

       
            $query = TransactionSellLinesPurchaseLines::leftJoin('transaction_sell_lines 
                    as SL', 'SL.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
                ->leftJoin('stock_adjustment_lines 
                    as SAL', 'SAL.id', '=', 'transaction_sell_lines_purchase_lines.stock_adjustment_line_id')
                ->leftJoin('transactions as sale', 'SL.transaction_id', '=', 'sale.id')
                ->leftJoin('transactions as stock_adjustment', 'SAL.transaction_id', '=', 'stock_adjustment.id')
                ->join('purchase_lines as PL', 'PL.id', '=', 'transaction_sell_lines_purchase_lines.purchase_line_id')
                ->join('transactions as purchase', 'PL.transaction_id', '=', 'purchase.id')
                ->join('business_locations as bl', 'purchase.location_id', '=', 'bl.id')
                ->join(
                    'variations as v',
                    'PL.variation_id',
                    '=',
                    'v.id'
                    )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'PL.product_id', '=', 'p.id')
                ->join('units as u', 'p.unit_id', '=', 'u.id')
                ->leftJoin('contacts as suppliers', 'purchase.contact_id', '=', 'suppliers.id')
                ->leftJoin('contacts as customers', 'sale.contact_id', '=', 'customers.id')
                ->where('purchase.business_id', $business_id)
                ->select(
                    'v.sub_sku as sku',
                    'p.type as product_type',
                    'p.name as product_name',
                    'v.name as variation_name',
                    'pv.name as product_variation',
                    'u.short_name as unit',
                    'purchase.transaction_date as purchase_date',
                    'purchase.ref_no as purchase_ref_no',
                    'purchase.type as purchase_type',
                    'purchase.id as purchase_id',
                    'suppliers.name as supplier',
                    'suppliers.supplier_business_name',
                    'PL.purchase_price_inc_tax as purchase_price',
                    'sale.transaction_date as sell_date',
                    'stock_adjustment.transaction_date as stock_adjustment_date',
                    'sale.invoice_no as sale_invoice_no',
                    'stock_adjustment.ref_no as stock_adjustment_ref_no',
                    'customers.name as customer',
                    'customers.supplier_business_name as customer_business_name',
                    'transaction_sell_lines_purchase_lines.quantity as quantity',
                    'SL.unit_price_inc_tax as selling_price',
                    'SAL.unit_price as stock_adjustment_price',
                    'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
                    'transaction_sell_lines_purchase_lines.sell_line_id',
                    'transaction_sell_lines_purchase_lines.purchase_line_id',
                    'transaction_sell_lines_purchase_lines.qty_returned',
                    'bl.name as location',
                    'SL.sell_line_note'
                )->get();

           
        

        return 
        [ 
        'query'=>$query, 
       
        ]
      ;
    }
}

// 


// <?php

// namespace Modules\Connector\Http\Controllers\Api;

// use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use Illuminate\Routing\Controller;
// use Illuminate\Support\Facades\Auth;
// use Modules\Connector\Transformers\CommonResource;
// use Modules\Connector\Transformers\BusinessResource;
// use App\Account;
// use App\Expense;
// use App\Business;
// use App\Category;
// use Datatables;
// use DB;
// use App\CashRegister;
// use App\Contact;
// use App\User;
// use App\CustomerGroup;
// use App\Brands;
// use App\BusinessLocation;
// use App\PurchaseLine;
// use App\TaxRate;
// use App\Transaction;
// use App\TransactionPayment;
// use App\TransactionSellLine;
// use App\TypesOfService;
// use App\Utils\TransactionUtil;
// use App\Utils\BusinessUtil;
// use App\Utils\ProductUtil;
// use App\Utils\ModuleUtil;
// use App\Utils\Util;
// use Illuminate\Support\Facades\DB as FacadesDB;
// use Modules\Connector\Transformers\SellResource;
// use Spatie\Activitylog\Models\Activity;

// /**
//  * @authenticated
//  *
//  */
// class CommonResourceController extends ApiController
// {

//     /**
//      * All Utils instance.
//      *
//      */
//     protected $transactionUtil;
//     protected $businessUtil;
//     protected $productUtil;
//     protected $moduleUtil;
//     protected $commonUtil;

//     public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ModuleUtil $moduleUtil, Util $commonUtil)
//     {
//         $this->businessUtil = $businessUtil;
//         $this->transactionUtil = $transactionUtil;
//         $this->productUtil = $productUtil;
//         $this->moduleUtil = $moduleUtil;
//         $this->commonUtil = $commonUtil;
//     }

//     /**
//      * List payment accounts
//      * @response {
//         "data": [
//             {
//                 "id": 1,
//                 "business_id": 1,
//                 "name": "Test Account",
//                 "account_number": "8746888847455",
//                 "account_type_id": 0,
//                 "note": null,
//                 "created_by": 9,
//                 "is_closed": 0,
//                 "deleted_at": null,
//                 "created_at": "2020-06-04 21:34:21",
//                 "updated_at": "2020-06-04 21:34:21"
//             }
//         ]
//     }
//      *
//      */
//     public function getPaymentAccounts()
//     {
//         $user = Auth::user();

//         $business_id = $user->business_id;

//         //Accounts
//         $accounts = Account::where('business_id', $business_id)
//             ->get();

//         return CommonResource::collection($accounts);
//     }

//     /**
//      * List payment methods
//      * @response {
//         "cash": "Cash",
//         "card": "Card",
//         "cheque": "Cheque",
//         "bank_transfer": "Bank Transfer",
//         "other": "Other",
//         "custom_pay_1": "Custom Payment 1",
//         "custom_pay_2": "Custom Payment 2",
//         "custom_pay_3": "Custom Payment 3"
//     }
//      *
//      */
//     public function getPaymentMethods()
//     {
//         $payment_methods = $this->productUtil->payment_types();

//         return $payment_methods;
//     }

//     /**
//      * Get business details
//      * @response {
//         "data": {
//             "id": 1,
//             "name": "Awesome Shop",
//             "currency_id": 2,
//             "start_date": "2018-01-01",
//             "tax_number_1": "3412569900",
//             "tax_label_1": "GSTIN",
//             "tax_number_2": null,
//             "tax_label_2": null,
//             "default_sales_tax": null,
//             "default_profit_percent": 25,
//             "owner_id": 1,
//             "time_zone": "America/Phoenix",
//             "fy_start_month": 1,
//             "accounting_method": "fifo",
//             "default_sales_discount": "10.00",
//             "sell_price_tax": "includes",
//             "logo": null,
//             "sku_prefix": "AS",
//             "enable_product_expiry": 0,
//             "expiry_type": "add_expiry",
//             "on_product_expiry": "keep_selling",
//             "stop_selling_before": 0,
//             "enable_tooltip": 1,
//             "purchase_in_diff_currency": 0,
//             "purchase_currency_id": null,
//             "p_exchange_rate": "1.000",
//             "transaction_edit_days": 30,
//             "stock_expiry_alert_days": 30,
//             "keyboard_shortcuts": {
//                 "pos": {
//                     "express_checkout": "shift+e",
//                     "pay_n_ckeckout": "shift+p",
//                     "draft": "shift+d",
//                     "cancel": "shift+c",
//                     "recent_product_quantity": "f2",
//                     "weighing_scale": null,
//                     "edit_discount": "shift+i",
//                     "edit_order_tax": "shift+t",
//                     "add_payment_row": "shift+r",
//                     "finalize_payment": "shift+f",
//                     "add_new_product": "f4"
//                 }
//             },
//             "pos_settings": {
//                 "amount_rounding_method": null,
//                 "disable_pay_checkout": 0,
//                 "disable_draft": 0,
//                 "disable_express_checkout": 0,
//                 "hide_product_suggestion": 0,
//                 "hide_recent_trans": 0,
//                 "disable_discount": 0,
//                 "disable_order_tax": 0,
//                 "is_pos_subtotal_editable": 0
//             },
//             "weighing_scale_setting": {
//                 "label_prefix": null,
//                 "product_sku_length": "4",
//                 "qty_length": "3",
//                 "qty_length_decimal": "2"
//             },
//             "manufacturing_settings": null,
//             "essentials_settings": null,
//             "ecom_settings": null,
//             "woocommerce_wh_oc_secret": null,
//             "woocommerce_wh_ou_secret": null,
//             "woocommerce_wh_od_secret": null,
//             "woocommerce_wh_or_secret": null,
//             "enable_brand": 1,
//             "enable_category": 1,
//             "enable_sub_category": 1,
//             "enable_price_tax": 1,
//             "enable_purchase_status": 1,
//             "enable_lot_number": 0,
//             "default_unit": null,
//             "enable_sub_units": 0,
//             "enable_racks": 0,
//             "enable_row": 0,
//             "enable_position": 0,
//             "enable_editing_product_from_purchase": 1,
//             "sales_cmsn_agnt": null,
//             "item_addition_method": 1,
//             "enable_inline_tax": 1,
//             "currency_symbol_placement": "before",
//             "enabled_modules": [
//                 "purchases",
//                 "add_sale",
//                 "pos_sale",
//                 "stock_transfers",
//                 "stock_adjustment",
//                 "expenses",
//                 "account",
//                 "tables",
//                 "modifiers",
//                 "service_staff",
//                 "booking",
//                 "kitchen",
//                 "subscription",
//                 "types_of_service"
//             ],
//             "date_format": "m/d/Y",
//             "time_format": "24",
//             "ref_no_prefixes": {
//                 "purchase": "PO",
//                 "purchase_return": null,
//                 "stock_transfer": "ST",
//                 "stock_adjustment": "SA",
//                 "sell_return": "CN",
//                 "expense": "EP",
//                 "contacts": "CO",
//                 "purchase_payment": "PP",
//                 "sell_payment": "SP",
//                 "expense_payment": null,
//                 "business_location": "BL",
//                 "username": null,
//                 "subscription": null
//             },
//             "theme_color": null,
//             "created_by": null,
//             "enable_rp": 0,
//             "rp_name": null,
//             "amount_for_unit_rp": "1.0000",
//             "min_order_total_for_rp": "1.0000",
//             "max_rp_per_order": null,
//             "redeem_amount_per_unit_rp": "1.0000",
//             "min_order_total_for_redeem": "1.0000",
//             "min_redeem_point": null,
//             "max_redeem_point": null,
//             "rp_expiry_period": null,
//             "rp_expiry_type": "year",
//             "repair_settings": null,
//             "email_settings": {
//                 "mail_driver": "smtp",
//                 "mail_host": null,
//                 "mail_port": null,
//                 "mail_username": null,
//                 "mail_password": null,
//                 "mail_encryption": null,
//                 "mail_from_address": null,
//                 "mail_from_name": null
//             },
//             "sms_settings": {
//                 "url": null,
//                 "send_to_param_name": "to",
//                 "msg_param_name": "text",
//                 "request_method": "post",
//                 "param_1": null,
//                 "param_val_1": null,
//                 "param_2": null,
//                 "param_val_2": null,
//                 "param_3": null,
//                 "param_val_3": null,
//                 "param_4": null,
//                 "param_val_4": null,
//                 "param_5": null,
//                 "param_val_5": null,
//                 "param_6": null,
//                 "param_val_6": null,
//                 "param_7": null,
//                 "param_val_7": null,
//                 "param_8": null,
//                 "param_val_8": null,
//                 "param_9": null,
//                 "param_val_9": null,
//                 "param_10": null,
//                 "param_val_10": null
//             },
//             "custom_labels": {
//                 "payments": {
//                     "custom_pay_1": null,
//                     "custom_pay_2": null,
//                     "custom_pay_3": null
//                 },
//                 "contact": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "product": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "location": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "user": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "purchase": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "sell": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 },
//                 "types_of_service": {
//                     "custom_field_1": null,
//                     "custom_field_2": null,
//                     "custom_field_3": null,
//                     "custom_field_4": null
//                 }
//             },
//             "common_settings": {
//                 "default_datatable_page_entries": "25"
//             },
//             "is_active": 1,
//             "created_at": "2018-01-04 02:15:19",
//             "updated_at": "2020-06-04 22:33:01",
//             "locations": [
//                 {
//                     "id": 1,
//                     "business_id": 1,
//                     "location_id": null,
//                     "name": "Awesome Shop",
//                     "landmark": "Linking Street",
//                     "country": "USA",
//                     "state": "Arizona",
//                     "city": "Phoenix",
//                     "zip_code": "85001",
//                     "invoice_scheme_id": 1,
//                     "invoice_layout_id": 1,
//                     "selling_price_group_id": null,
//                     "print_receipt_on_invoice": 1,
//                     "receipt_printer_type": "browser",
//                     "printer_id": null,
//                     "mobile": null,
//                     "alternate_number": null,
//                     "email": null,
//                     "website": null,
//                     "featured_products": [
//                         "5",
//                         "71"
//                     ],
//                     "is_active": 1,
//                     "default_payment_accounts": {
//                         "cash": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "card": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "cheque": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "bank_transfer": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "other": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "custom_pay_1": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "custom_pay_2": {
//                             "is_enabled": "1",
//                             "account": null
//                         },
//                         "custom_pay_3": {
//                             "is_enabled": "1",
//                             "account": null
//                         }
//                     },
//                     "custom_field1": null,
//                     "custom_field2": null,
//                     "custom_field3": null,
//                     "custom_field4": null,
//                     "deleted_at": null,
//                     "created_at": "2018-01-04 02:15:20",
//                     "updated_at": "2020-06-05 00:56:54"
//                 }
//             ],
//             "currency": {
//                 "id": 2,
//                 "country": "America",
//                 "currency": "Dollars",
//                 "code": "USD",
//                 "symbol": "$",
//                 "thousand_separator": ",",
//                 "decimal_separator": ".",
//                 "created_at": null,
//                 "updated_at": null
//             },
//             "printers": [],
//             "currency_precision": 2,
//             "quantity_precision": 2
//         }
//     }
//      *
//      */
//     public function getBusinessDetails()
//     {
//         $user = Auth::user();

//         $business = Business::with(['locations', 'currency', 'printers'])
//             ->findOrFail($user->business_id);

//         return new BusinessResource($business);
//     }

//     /**
//      * Get profit and loss report
//      * @queryParam location_id optional id of the location Example: 1
//      * @queryParam start_date optional format:Y-m-d Example: 2018-06-25
//      * @queryParam end_date optional format:Y-m-d Example: 2018-06-25
//      * @queryParam user_id optional id of the user Example: 1
//      *
//      *@response {
//         "data": {
//             "total_purchase_shipping_charge": 0,
//             "total_sell_shipping_charge": 0,
//             "total_transfer_shipping_charges": "0.0000",
//             "opening_stock": 0,
//             "closing_stock": "386859.00000000",
//             "total_purchase": 386936,
//             "total_purchase_discount": "0.000000000000",
//             "total_purchase_return": "0.0000",
//             "total_sell": 9764.5,
//             "total_sell_discount": "11.550000000000",
//             "total_sell_return": "0.0000",
//             "total_sell_round_off": "0.0000",
//             "total_expense": "0.0000",
//             "total_adjustment": "0.0000",
//             "total_recovered": "0.0000",
//             "total_reward_amount": "0.0000",
//             "left_side_module_data": [
//                 {
//                     "value": "0.0000",
//                     "label": "Total Payroll",
//                     "add_to_net_profit": true
//                 },
//                 {
//                     "value": 0,
//                     "label": "Total Production Cost",
//                     "add_to_net_profit": true
//                 }
//             ],
//             "right_side_module_data": [],
//             "net_profit": 9675.95,
//             "gross_profit": -11.55,
//             "total_sell_by_subtype": []
//         }
//     }
//      */
//     public function getProfitLoss()
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;
//         $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

//         $location_id = !empty(request()->input('location_id')) ? request()->input('location_id') : null;
//         $start_date = !empty(request()->input('start_date')) ? request()->input('start_date') : $fy['start'];
//         $end_date = !empty(request()->input('end_date')) ? request()->input('end_date') : $fy['end'];

//         $user_id = request()->input('user_id') ?? null;

//         $data = $this->transactionUtil->getProfitLossDetails($business_id, $location_id, $start_date, $end_date, $user_id);

//         return [
//             'data' => $data
//         ];
//     }

//     /**
//      * Get product current stock
//      * @response {
//         "data": [
//             {
//                 "total_sold": null,
//                 "total_transfered": null,
//                 "total_adjusted": null,
//                 "stock_price": null,
//                 "stock": null,
//                 "sku": "AS0001",
//                 "product": "Men's Reverse Fleece Crew",
//                 "type": "single",
//                 "product_id": 1,
//                 "unit": "Pc(s)",
//                 "enable_stock": 1,
//                 "unit_price": "143.0000",
//                 "product_variation": "DUMMY",
//                 "variation_name": "DUMMY",
//                 "location_name": null,
//                 "location_id": null,
//                 "variation_id": 1
//             },
//             {
//                 "total_sold": "50.0000",
//                 "total_transfered": null,
//                 "total_adjusted": null,
//                 "stock_price": "3850.00000000",
//                 "stock": "50.0000",
//                 "sku": "AS0002-1",
//                 "product": "Levis Men's Slimmy Fit Jeans",
//                 "type": "variable",
//                 "product_id": 2,
//                 "unit": "Pc(s)",
//                 "enable_stock": 1,
//                 "unit_price": "77.0000",
//                 "product_variation": "Waist Size",
//                 "variation_name": "28",
//                 "location_name": "Awesome Shop",
//                 "location_id": 1,
//                 "variation_id": 2
//             },
//             {
//                 "total_sold": "60.0000",
//                 "total_transfered": null,
//                 "total_adjusted": null,
//                 "stock_price": "6930.00000000",
//                 "stock": "90.0000",
//                 "sku": "AS0002-2",
//                 "product": "Levis Men's Slimmy Fit Jeans",
//                 "type": "variable",
//                 "product_id": 2,
//                 "unit": "Pc(s)",
//                 "enable_stock": 1,
//                 "unit_price": "77.0000",
//                 "product_variation": "Waist Size",
//                 "variation_name": "30",
//                 "location_name": "Awesome Shop",
//                 "location_id": 1,
//                 "variation_id": 3
//             }
//         ],
//         "links": {
//             "first": "http://local.pos.com/connector/api/product-stock-report?page=1",
//             "last": "http://local.pos.com/connector/api/product-stock-report?page=22",
//             "prev": null,
//             "next": "http://local.pos.com/connector/api/product-stock-report?page=2"
//         },
//         "meta": {
//             "current_page": 1,
//             "from": 1,
//             "last_page": 22,
//             "path": "http://local.pos.com/connector/api/product-stock-report",
//             "per_page": 3,
//             "to": 3,
//             "total": 66
//         }
//     }
//      */
//     public function getProductStock()
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $filters = request()->only([
//             'location_id', 'category_id', 'sub_category_id',
//             'brand_id', 'unit_id', 'tax_id', 'type',
//             'only_mfg_products', 'active_state',
//             'not_for_selling', 'repair_model_id',
//             'product_id', 'active_state'
//         ]);

//         $products = $this->productUtil->getProductStockDetails($business_id, $filters, 'api');
//         return CommonResource::collection($products);
//     }

//     /**
//      * Get notifications
//      * @response {
//             "data": [
//                 {
//                     "msg": "Payroll for August/2020 added by Mr. Super Admin. Reference No. 2020/0002",
//                     "icon_class": "fas fa-money-bill-alt bg-green",
//                     "link": "http://local.pos.com/hrm/payroll",
//                     "read_at": null,
//                     "created_at": "3 hours ago"
//                 }
//             ]
//         }
//      */
//     public function getNotifications()
//     {
//         $user = Auth::user();
//         $notifications = $user->notifications()->orderBy('created_at', 'DESC')->get();

//         $notifications_data = $this->commonUtil->parseNotifications($notifications);

//         return new CommonResource($notifications_data);
//     }

//     /**
//      * Get location details from coordinates
//      * @bodyParam lat decimal required Lattitude of the location Example: 41.40338
//      * @bodyParam lon decimal required Longitude of the location Example: 2.17403
//      * @response {
//         "address": "Radhanath Mullick Ln, Tiretta Bazaar, Bow Bazaar, Kolkata, West Bengal, 700 073, India"
//     }
//      *
//      */
//     public function getLocation()
//     {
//         $lat = request()->input('lat');
//         $lon = request()->input('lon');

//         $address = '';
//         if (!empty($lat) && !empty($lon)) {
//             $address = $this->moduleUtil->getLocationFromCoordinates($lat, $lon);
//         }

//         return [
//             'address' => $address
//         ];
//     }

//     public function getexpense(Request $request)
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;
//         //
//         $filters = $request->only(['category', 'location_id']);

//         $date_range = $request->input('date_range');

//         if (!empty($date_range)) {
//             $date_range_array = explode('~', $date_range);
//             $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
//             $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
//         } else {
//             $filters['start_date'] = \Carbon::now()->startOfMonth()->format('Y-m-d');
//             $filters['end_date'] = \Carbon::now()->endOfMonth()->format('Y-m-d');
//         }

//         $data = $this->transactionUtil->getExpenseReport($business_id, $filters);

//         return [
//             'data' => $data
//         ];
//     }
//     public function getStockReport()
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;
//         $filters = request()->only([
//             'location_id', 'category_id', 'sub_category_id', 'brand_id', 'unit_id', 'tax_id', 'type',
//             'only_mfg_products', 'active_state',  'not_for_selling', 'repair_model_id', 'product_id', 'active_state'
//         ]);

//         $filters['not_for_selling'] = isset($filters['not_for_selling']) && $filters['not_for_selling'] == 'true' ? 1 : 0;
//         // Return the details in ajax call
//         $for = request()->input('for') == 'view_product' ? 'view_product' : 'datatables';

//         $data = $this->productUtil->getProductStockDetails($business_id, $filters, $for)->get();




//         return [
//             'data' => $data,

//         ];
//     }


//     public function getRegisterReport(Request $request)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         //Return the details in ajax call
//         // if ($request->ajax()) {
//         $registers = CashRegister::leftjoin(
//             'cash_register_transactions as ct',
//             'ct.cash_register_id',
//             '=',
//             'cash_registers.id'
//         )->join(
//             'users as u',
//             'u.id',
//             '=',
//             'cash_registers.user_id'
//         )
//             ->leftJoin(
//                 'business_locations as bl',
//                 'bl.id',
//                 '=',
//                 'cash_registers.location_id'
//             )
//             ->where('cash_registers.business_id', $business_id)
//             ->select(
//                 'cash_registers.*',
//                 DB::raw(
//                     "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '', COALESCE(u.email, '')) as user_name"
//                 ),
//                 'bl.name as location_name',
//                 DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='sell', amount, 0), 0)) as total_cash_payment"),
//                 DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='sell', amount, 0), 0)) as total_cheque_payment"),
//                 DB::raw("SUM(IF(pay_method='card', IF(transaction_type='sell', amount, 0), 0)) as total_card_payment"),
//                 DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='sell', amount, 0), 0)) as total_bank_transfer_payment"),
//                 DB::raw("SUM(IF(pay_method='other', IF(transaction_type='sell', amount, 0), 0)) as total_other_payment"),
//                 DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='sell', amount, 0), 0)) as total_advance_payment"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_1"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_2"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_3"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_4"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_5"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_6"),
//                 DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_7")
//             )->groupby('cash_registers.id')->get();

//         $permitted_locations = auth()->user()->permitted_locations();
//         if ($permitted_locations != 'all') {
//             $registers->whereIn('cash_registers.location_id', $permitted_locations);
//         }

//         if (!empty($request->input('user_id'))) {
//             $registers->where('cash_registers.user_id', $request->input('user_id'));
//         }
//         if (!empty($request->input('status'))) {
//             $registers->where('cash_registers.status', $request->input('status'));
//         }
//         $start_date = $request->get('start_date');
//         $end_date = $request->get('end_date');

//         if (!empty($start_date) && !empty($end_date)) {
//             $registers->whereDate('cash_registers.created_at', '>=', $start_date)
//                 ->whereDate('cash_registers.created_at', '<=', $end_date);
//         }

//         // }

//         // $users = User::forDropdown($business_id, false);
//         // $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

//         return [

//             'registers' =>  $registers
//         ];
//     }

//     public function getCustomerSuppliers(Request $request)
//     {



//         $user = Auth::user();
//         $business_id = $user->business_id;
//         //Return the details in ajax call

//         // if ($request->ajax()) {

//         // Return the details in ajax call//
//         // if ($request->ajax()) {
//         $contacts = Contact::where('contacts.business_id', $business_id)
//             ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
//             ->active()
//             ->groupBy('contacts.id')
//             ->select(
//                 DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
//                 DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
//                 DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
//                 DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
//                 DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
//                 DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
//                 DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
//                 DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
//                 DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
//                 DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
//                 'contacts.supplier_business_name',
//                 'contacts.name',
//                 'contacts.id',
//                 'contacts.type as contact_type'
//             )->get();


//         return Datatables::of($contacts)


//             ->addColumn('due', function ($row) {
//                 $due = ($row->total_invoice - $row->invoice_received - $row->total_sell_return + $row->sell_return_paid) - ($row->total_purchase - $row->total_purchase_return + $row->purchase_return_received - $row->purchase_paid);

//                 if ($row->contact_type == 'supplier') {
//                     $due -= $row->opening_balance - $row->opening_balance_paid;
//                 } else {
//                     $due += $row->opening_balance - $row->opening_balance_paid;
//                 }


//                 // $due = $this->transactionUtil->num_f($due, true);
//                 // dd($due_formatted);
//                 return    $due;
//             })




//             // ->rawColumns(['total_purchase', 'total_invoice', 'due', 'name', 'total_purchase_return', 'total_sell_return', 'opening_balance_due'])
//             ->make(true);
//         // }    

//         $customer_group = CustomerGroup::forDropdown($business_id, false, true);
//         $types = [
//             '' => __('lang_v1.all'),
//             'customer' => __('report.customer'),
//             'supplier' => __('report.supplier')
//         ];




//         return [
//             'contacts' => $contacts,

//         ];
//     }


//     public function getproductSellReport(Request $request)
//     {


//         $user = Auth::user();
//         $business_id = $user->business_id;
//         $variation_id = $request->get('variation_id', null);
//         // if ($request->ajax()) {
//         //    
//         $query = TransactionSellLine::join(
//             'transactions as t',
//             'transaction_sell_lines.transaction_id',
//             '=',
//             't.id'
//         )
//             ->join(
//                 'variations as v',
//                 'transaction_sell_lines.variation_id',
//                 '=',
//                 'v.id'
//             )
//             ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
//             ->join('contacts as c', 't.contact_id', '=', 'c.id')
//             ->join('products as p', 'pv.product_id', '=', 'p.id')
//             ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
//             ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
//             ->where('t.business_id', $business_id)
//             ->where('t.type', 'sell')
//             ->where('t.status', 'final')
//             ->select(
//                 'p.name as product_name',
//                 'p.type as product_type',
//                 'pv.name as product_variation',
//                 'v.name as variation_name',
//                 'v.sub_sku',
//                 'c.name as customer',
//                 'c.supplier_business_name',
//                 'c.contact_id',
//                 't.id as transaction_id',
//                 't.invoice_no',
//                 't.transaction_date as transaction_date',
//                 'transaction_sell_lines.unit_price_before_discount as unit_price',
//                 'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
//                 DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
//                 'transaction_sell_lines.line_discount_type as discount_type',
//                 'transaction_sell_lines.line_discount_amount as discount_amount',
//                 'transaction_sell_lines.item_tax',
//                 'tax_rates.name as tax',
//                 'u.short_name as unit',
//                 'transaction_sell_lines.parent_sell_line_id',
//                 DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
//             )
//             ->get('transaction_sell_lines.id');

//         if (!empty($variation_id)) {
//             $query->where('transaction_sell_lines.variation_id', $variation_id);
//         }
//         $start_date = $request->get('start_date');
//         $end_date = $request->get('end_date');
//         if (!empty($start_date) && !empty($end_date)) {
//             $query->where('t.transaction_date', '>=', $start_date)
//                 ->where('t.transaction_date', '<=', $end_date);
//         }

//         $permitted_locations = auth()->user()->permitted_locations();
//         if ($permitted_locations != 'all') {
//             $query->whereIn('t.location_id', $permitted_locations);
//         }

//         $location_id = $request->get('location_id', null);
//         if (!empty($location_id)) {
//             $query->where('t.location_id', $location_id);
//         }

//         $customer_id = $request->get('customer_id', null);
//         if (!empty($customer_id)) {
//             $query->where('t.contact_id', $customer_id);
//         }

//         $customer_group_id = $request->get('customer_group_id', null);
//         if (!empty($customer_group_id)) {
//             $query->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
//                 ->where('CG.id', $customer_group_id);
//         }

//         $category_id = $request->get('category_id', null);
//         if (!empty($category_id)) {
//             $query->where('p.category_id', $category_id);
//         }

//         $brand_id = $request->get('brand_id', null);
//         if (!empty($brand_id)) {
//             $query->where('p.brand_id', $brand_id);
//         }

//         return Datatables::of($query)
//             ->editColumn('product_name', function ($row) {
//                 $product_name = $row->product_name;
//                 if ($row->product_type == 'variable') {
//                     $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
//                 }

//                 return $product_name;
//             })
//             ->editColumn('invoice_no', function ($row) {
//                 return
//                     $row->invoice_no;
//             })
//             ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
//             ->editColumn('unit_sale_price', function ($row) {
//                 return  $row->unit_sale_price;
//             })
//             ->editColumn('sell_qty', function ($row) {
//                 //ignore child sell line of combo product
//                 $class = is_null($row->parent_sell_line_id) ? '' : '';

//                 return  $class  . (float)$row->sell_qty  . $row->unit;
//             })
//             ->editColumn('subtotal', function ($row) {
//                 //ignore child sell line of combo product
//                 $class = is_null($row->parent_sell_line_id) ? 'row_subtotal' : '';
//                 return  $row->subtotal;
//             })
//             ->editColumn('unit_price', function ($row) {
//                 return  $row->unit_price;
//             })
//             // ->editColumn('discount_amount', '
//             //     @if($discount_type == "percentage")
//             //         {{@number_format($discount_amount)}} %
//             //     @elseif($discount_type == "fixed")
//             //         {{@number_format($discount_amount)}}
//             //     @endif  
//             //     ')
//             ->editColumn('tax', function ($row) {
//                 return
//                     $row->item_tax .
//                     (float)$row->item_tax . $row->tax;
//             })
//             ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}},<br>@endif {{$customer}}')
//             ->rawColumns(['invoice_no', 'unit_sale_price', 'subtotal', 'sell_qty', 'discount_amount', 'unit_price', 'tax', 'customer'])
//             ->make(true);
//         // }
//         $business_locations = BusinessLocation::forDropdown($business_id);
//         $customers = Contact::customersDropdown($business_id);
//         $categories = Category::forDropdown($business_id, 'product');
//         $brands = Brands::forDropdown($business_id);
//         $customer_group = CustomerGroup::forDropdown($business_id, false, true);

//         return [
//             'business_locations' => $business_locations,
//             'customers' => $customers,
//             'categories' => $categories,
//             'brands' => $brands,
//             'customer_group' => $customer_group,
//             'query' => $query,
//             'variation_id' => $variation_id,

//         ];
//     }
//     public function getTrendingProducts()
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;
//         $filters = request()->only(['category', 'sub_category', 'brand', 'unit', 'limit', 'location_id', 'product_type']);

//         $date_range = request()->input('date_range');

//         if (!empty($date_range)) {
//             $date_range_array = explode('~', $date_range);
//             $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
//             $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
//         }

//         $products = $this->productUtil->getTrendingProducts($business_id, $filters);
//         return [
//             'products' => $products,
//         ];
//     }


//     public function activityLog()
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;
//         $transaction_types = [
//             'contact' => __('report.contact'),
//             'user' => __('report.user'),
//             'sell' => __('sale.sale'),
//             'purchase' => __('lang_v1.purchase'),
//             'sales_order' => __('lang_v1.sales_order'),
//             'purchase_order' => __('lang_v1.purchase_order'),
//             'sell_return' => __('lang_v1.sell_return'),
//             'purchase_return' => __('lang_v1.purchase_return'),
//             'sell_transfer' => __('lang_v1.stock_transfer'),
//             'stock_adjustment' => __('stock_adjustment.stock_adjustment'),
//             'expense' => __('lang_v1.expense')
//         ];

//         $activities = Activity::with(['subject'])

//             ->leftjoin('users as u', 'u.id', '=', 'activity_log.causer_id')
//             ->where('activity_log.business_id', $business_id)
//             ->select(
//                 'activity_log.*',
//                 DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by")
//             )->get();


//         $users = User::allUsersDropdown($business_id, false);



//         return [
//             'users' => $users,
//             'activities' => $activities,
//         ];
//     }

//     public function getkot(Request $request)
//     {


//         $user = Auth::user();
//         $business_id = $user->business_id;



//         $kot = TransactionSellLine::join(
//             'transactions',
//             'transaction_sell_lines.transaction_id',
//             '=',
//             'transactions.id'


//         )
//             ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')

//             ->leftjoin(
//                 'types_of_services as ts',
//                 'transactions.types_of_service_id',
//                 '=',
//                 'ts.id'
//             )->leftjoin('res_tables', 'transactions.res_table_id', '=', 'ts.id')
//             ->leftjoin(
//                 'users as u',
//                 'transactions.res_waiter_id',
//                 '=',
//                 'u.id'
//             )
//             ->leftJoin(
//                 'business_locations as bl',
//                 'transactions.location_id',
//                 '=',
//                 'bl.id'
//             )


//             ->where('transactions.business_id', $business_id)
//             ->where('transactions.type', '=', 'sell')

//             ->select(
//                 'res_tables.name as table',
//                 'transactions.invoice_no',
//                 FacadesDB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
//                 'transactions.res_waiter_id as transaction_waiter',
//                 'transactions.types_of_service_id as types_of_service_id',
//                 'transactions.additional_notes as additional_notes',
//                 'transactions.contact_id as contact_id',
//                 'transactions.packing_charge',
//                 'transactions.total_before_tax',
//                 'c.name as cname',
//                 'transactions.location_id as tlocation_id',
//                 'ts.name as name',
//                 DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
//                 'u.first_name as fname',
//                 'bl.location_id as location_id',


//             )
//             ->groupby('transactions.invoice_no')->get();




//         return
//             [
//                 'data' => $kot
//             ];
//     }


//     public function TypeOfService(Request $request)
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $sell_details = TransactionSellLine::join(
//             'transactions',
//             'transaction_sell_lines.transaction_id',
//             '=',
//             'transactions.id'


//         )
//             ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
//             ->leftjoin(
//                 'types_of_services as ts',
//                 'transactions.types_of_service_id',
//                 '=',
//                 'ts.id'
//             )
//             ->leftjoin(
//                 'users as u',
//                 'transactions.res_waiter_id',
//                 '=',
//                 'u.id'
//             )
//             ->leftJoin(
//                 'business_locations as bl',
//                 'transactions.location_id',
//                 '=',
//                 'bl.id'
//             )->where('transactions.business_id', $business_id)
//             ->where('transactions.types_of_service_id', '!=', 'null')

//             ->select(

//                 'transactions.invoice_no',
//                 'transactions.transaction_date as transaction_date',
//                 'transactions.res_waiter_id as transaction_waiter',
//                 'transactions.types_of_service_id as types_of_service_id',
//                 'transactions.contact_id as contact_id',
//                 'transactions.packing_charge',
//                 'transactions.total_before_tax',
//                 'c.name as customer_name',

//                 'ts.name as type_name',
//                 'u.first_name as Service_Staf',
//                 'bl.location_id as location_id',


//             )->groupby('transactions.invoice_no')->get();



//         return [
//             'data' => $sell_details,

//         ];
//     }
//     public function getproductPurchaseReport(Request $request)
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;


//         $variation_id = $request->get('variation_id', null);
//         $query = PurchaseLine::join(
//             'transactions as t',
//             'purchase_lines.transaction_id',
//             '=',
//             't.id'
//         )
//             ->join(
//                 'variations as v',
//                 'purchase_lines.variation_id',
//                 '=',
//                 'v.id'
//             )
//             ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
//             ->join('contacts as c', 't.contact_id', '=', 'c.id')
//             ->join('products as p', 'pv.product_id', '=', 'p.id')
//             ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
//             ->where('t.business_id', $business_id)
//             ->where('t.type', 'purchase')
//             ->select(
//                 'p.name as product_name',
//                 'p.type as product_type',
//                 'pv.name as product_variation',
//                 'v.name as variation_name',
//                 'v.sub_sku',
//                 'c.name as supplier',
//                 'c.supplier_business_name',
//                 't.id as transaction_id',
//                 't.ref_no',
//                 't.transaction_date as transaction_date',
//                 'purchase_lines.purchase_price_inc_tax as unit_purchase_price',
//                 DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
//                 'purchase_lines.quantity_adjusted',
//                 'u.short_name as unit',
//                 DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal')
//             )
//             ->groupBy('purchase_lines.id')->get();




//         return [
//             'data' => $query
//         ];
//     }


//     public function sellPaymentReport(Request $request)
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

//         $customer_id = $request->get('supplier_id', null);
//         $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
//         $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

//         $location_id = $request->get('location_id', null);
//         $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

//         $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
//             $join->on('transaction_payments.transaction_id', '=', 't.id')
//                 ->where('t.business_id', $business_id)
//                 ->whereIn('t.type', ['sell', 'opening_balance']);
//         })
//             ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
//             ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
//             ->where('transaction_payments.business_id', $business_id)
//             ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
//                 $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('sell', 'opening_balance') $parent_payment_query_part $contact_filter1)")
//                     ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('sell', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
//             })
//             ->select(
//                 DB::raw("IF(transaction_payments.transaction_id IS NULL, 
//                                 (SELECT c.name FROM transactions as ts
//                                 JOIN contacts as c ON ts.contact_id=c.id 
//                                 WHERE ts.id=(
//                                         SELECT tps.transaction_id FROM transaction_payments as tps
//                                         WHERE tps.parent_id=transaction_payments.id LIMIT 1
//                                     )
//                                 ),
//                                 (SELECT CONCAT(COALESCE(CONCAT(c.supplier_business_name, '<br>'), ''), c.name) FROM transactions as ts JOIN
//                                     contacts as c ON ts.contact_id=c.id
//                                     WHERE ts.id=t.id 
//                                 )
//                             ) as customer"),
//                 'transaction_payments.amount',
//                 'transaction_payments.is_return',
//                 'method',
//                 'paid_on',
//                 'transaction_payments.payment_ref_no',
//                 'transaction_payments.document',
//                 'transaction_payments.transaction_no',
//                 't.invoice_no',
//                 't.id as transaction_id',
//                 'cheque_number',
//                 'card_transaction_number',
//                 'bank_account_number',
//                 'transaction_payments.id as DT_RowId',
//                 'CG.name as customer_group'
//             )
//             ->groupBy('transaction_payments.id')->get();



//         return [
//             'data' => $query
//         ];
//     }

//     public function purchasePaymentReport(Request $request)
//     {


//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $supplier_id = $request->get('supplier_id', null);
//         $contact_filter1 = !empty($supplier_id) ? "AND t.contact_id=$supplier_id" : '';
//         $contact_filter2 = !empty($supplier_id) ? "AND transactions.contact_id=$supplier_id" : '';

//         $location_id = $request->get('location_id', null);

//         $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

//         $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
//             $join->on('transaction_payments.transaction_id', '=', 't.id')
//                 ->where('t.business_id', $business_id)
//                 ->whereIn('t.type', ['purchase', 'opening_balance']);
//         })
//             ->where('transaction_payments.business_id', $business_id)
//             ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
//                 $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('purchase', 'opening_balance')  $parent_payment_query_part $contact_filter1)")
//                     ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('purchase', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
//             })

//             ->select(
//                 DB::raw("IF(transaction_payments.transaction_id IS NULL, 
//                                 (SELECT c.name FROM transactions as ts
//                                 JOIN contacts as c ON ts.contact_id=c.id 
//                                 WHERE ts.id=(
//                                         SELECT tps.transaction_id FROM transaction_payments as tps
//                                         WHERE tps.parent_id=transaction_payments.id LIMIT 1
//                                     )
//                                 ),
//                                 (SELECT CONCAT(COALESCE(c.supplier_business_name, ''), '<br>', c.name) FROM transactions as ts JOIN
//                                     contacts as c ON ts.contact_id=c.id
//                                     WHERE ts.id=t.id 
//                                 )
//                             ) as supplier"),
//                 'transaction_payments.amount',
//                 'method',
//                 'paid_on',
//                 'transaction_payments.payment_ref_no',
//                 'transaction_payments.document',
//                 't.ref_no',
//                 't.id as transaction_id',
//                 'cheque_number',
//                 'card_transaction_number',
//                 'bank_account_number',
//                 'transaction_no',
//                 'transaction_payments.id as DT_RowId'
//             )
//             ->groupBy('transaction_payments.id')->get();



//         return [
//             'data' => $query
//         ];
//     }
//     public function getPurchaseSell(Request $request)
//     {

//         $user = Auth::user();
//         $business_id = $user->business_id;

//         //Return the details in ajax call
//         $location_id = $request->get('location_id');
//         $commission_agent = $request->get('commission_agent');

//         $business_details = $this->businessUtil->getDetails($business_id);
//         $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

//         $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

//         $commission_percentage = User::find($commission_agent)->cmmsn_percent;

//         if ($commsn_calculation_type == 'payment_received') {
//             $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, $location_id, $commission_agent);

//             //Get Commision
//             $total_commission = $commission_percentage * $payment_details['total_payment_with_commission'] / 100;

//             return [
//                 'total_payment_with_commission' =>
//                 $payment_details['total_payment_with_commission'] ?? 0,
//                 'total_commission' => $total_commission,
//                 'commission_percentage' => $commission_percentage
//             ];
//             // }

//             // $business_locations = BusinessLocation::forDropdown($business_id, true);

//             // return [
//             //     'business_locations' => $business_locations
//             // ];
//         }
//     }
//     public function getSalesRepresentativeTotalSell(Request $request)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         //Return the details in ajax call
//         // if ($request->ajax()) {
//         $start_date = $request->get('start_date');
//         $end_date = $request->get('end_date');

//         $location_id = $request->get('location_id');
//         $created_by = $request->get('created_by');

//         $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id, $created_by);

//         //Get Sell Return details
//         $transaction_types = [
//             'sell_return'
//         ];
//         $sell_return_details = $this->transactionUtil->getTransactionTotals(
//             $business_id,
//             $transaction_types,
//             $start_date,
//             $end_date,
//             $location_id,
//             $created_by
//         );

//         $total_sell_return = !empty($sell_return_details['total_sell_return_exc_tax']) ? $sell_return_details['total_sell_return_exc_tax'] : 0;
//         $total_sell = $sell_details['total_sell_exc_tax'] - $total_sell_return;

//         return [
//             'total_sell_exc_tax' => $sell_details['total_sell_exc_tax'],
//             'total_sell_return_exc_tax' => $total_sell_return,
//             'total_sell' => $total_sell
//         ];
//     }


//     public function getStockAdjustmentReport(Request $request)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;


//         $query =  Transaction::where('business_id', $business_id)
//             ->where('type', 'stock_adjustment');



//         $stock_adjustment_details = $query->select(
//             DB::raw("SUM(final_total) as total_amount"),
//             DB::raw("SUM(total_amount_recovered) as total_recovered"),
//             DB::raw("SUM(IF(adjustment_type = 'normal', final_total, 0)) as total_normal"),
//             DB::raw("SUM(IF(adjustment_type = 'abnormal', final_total, 0)) as total_abnormal")
//         )->first();
//         // return $stock_adjustment_details;
//         $stock_adjustments = Transaction::join(
//             'business_locations AS BL',
//             'transactions.location_id',
//             '=',
//             'BL.id'
//         )
//             ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
//             ->where('transactions.business_id', $business_id)
//             ->where('transactions.type', 'stock_adjustment')
//             ->select(
//                 'transactions.id',
//                 'transaction_date',
//                 'ref_no',
//                 'BL.name as location_name',
//                 'adjustment_type',
//                 'final_total',
//                 'total_amount_recovered',
//                 'additional_notes',
//                 'transactions.id as DT_RowId',
//                 DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by")
//             )->get();

//         return [
//             'stock_adjustment_details' => $stock_adjustment_details,
//             'stock_adjustments' => $stock_adjustments
//         ];
//     }




//     public function gettimeReport(Request $request)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $sell_details = TransactionSellLine::join(

//                 'products AS p',
//                 'transaction_sell_lines.product_id',
//                 '=',
//                 'p.id'
//             )
//             ->join(
//                 'transactions AS t',
//                 'transaction_sell_lines.transaction_id',
//                 '=',
//                 't.id'


//             )
//             ->leftjoin(
//                 'types_of_services as ts',
//                 't.types_of_service_id',
//                 '=',
//                 'ts.id'
//             )
//             ->leftjoin(
//                 'users as u',
//                 't.res_waiter_id',
//                 '=',
//                 'u.id'
//             )
//             ->leftJoin(
//                 'business_locations as bl',
//                 't.location_id',
//                 '=',
//                 'bl.id'
//             )

//             ->select(
//                 'p.name as product_name',
//                 'p.id as product_id',
//                 'p.time as product_time',
//                 't.invoice_no as transaction_invoiceno',
//                 't.transaction_date as transaction_date',
//                 't.res_waiter_id as transaction_waiter',
//                 't.location_id as tlocation_id',
//                 'transaction_sell_lines.update_time as update_time',
//                 'transaction_sell_lines.total_time as cooked_time',
//                 'ts.name as name',
//                 'u.first_name as fname',
//                 'bl.location_id as location_id'


//             )
//             ->get();

//         if ($request->ajax()) {
//             $sell_details = TransactionSellLine::join(

//                     'products AS p',
//                     'transaction_sell_lines.product_id',
//                     '=',
//                     'p.id'
//                 )
//                 ->join(
//                     'transactions AS t',
//                     'transaction_sell_lines.transaction_id',
//                     '=',
//                     't.id'


//                 )
//                 ->leftjoin(
//                     'types_of_services as ts',
//                     't.types_of_service_id',
//                     '=',
//                     'ts.id'
//                 )
//                 ->leftjoin(
//                     'users as u',
//                     't.res_waiter_id',
//                     '=',
//                     'u.id'
//                 )
//                 ->leftJoin(
//                     'business_locations as bl',
//                     't.location_id',
//                     '=',
//                     'bl.id'
//                 )

//                 ->select(
//                     'p.name as product_name',
//                     'p.id as product_id',
//                     'p.time as product_time',
//                     't.invoice_no as transaction_invoiceno',
//                     't.transaction_date as transaction_date',
//                     't.res_waiter_id as transaction_waiter',
//                     't.location_id as location_id',
//                     'transaction_sell_lines.update_time as update_time',
//                     'transaction_sell_lines.total_time as cooked_time',
//                     'ts.name as name',
//                     'u.first_name as fname',
//                     'bl.location_id'


//                 )->where(
//                     't.res_waiter_id',
//                     '=',
//                     $request->location
//                 )
//                 ->get();

//             return response()->json(['selldetails' => $sell_details]);
//         }

//         return [
//             'data' => $sell_details
//         ];
//     }


//     public function getCustomerGroup(Request $request)
//     {
//         $user = Auth::user();
//         $business_id = $user->business_id;

//         $query = Transaction::leftjoin('customer_groups AS CG', 'transactions.customer_group_id', '=', 'CG.id')
//             ->where('transactions.business_id', $business_id)
//             ->where('transactions.type', 'sell')
//             ->where('transactions.status', 'final')
//             ->groupBy('transactions.customer_group_id')
//             ->select(DB::raw("SUM(final_total) as total_sell"), 'CG.name');

//         return Datatables::of($query)
//             ->editColumn('total_sell', function ($row) {
//                 return '<span class="display_currency" data-currency_symbol = true>' . $row->total_sell . '</span>';
//             })
//             ->rawColumns(['total_sell'])
//             ->make(true);




//         return [
//             'query' => $query
//         ];
//     }


//     public function getTaxReport(Request $request)
//     {
      

//         $user = Auth::user();
//         $business_id = $user->business_id;

//         // //Return the details in ajax call
//         // if ($request->ajax()) {
//         //     $start_date = $request->get('start_date');
//         //     $end_date = $request->get('end_date');
//         //     $location_id = $request->get('location_id');

//             $input_tax_details = $this->transactionUtil->getInputTax($business_id);

//             $output_tax_details = $this->transactionUtil->getOutputTax($business_id);

//             $expense_tax_details = $this->transactionUtil->getExpenseTax($business_id);

//             $module_output_taxes = $this->moduleUtil->getModuleData('getModuleOutputTax');

//             $total_module_output_tax = 0;
//             foreach ($module_output_taxes as $key => $module_output_tax) {
//                 $total_module_output_tax += $module_output_tax;
//             }

//             $total_output_tax = $output_tax_details['total_tax'] + $total_module_output_tax;
            
//             $tax_diff = $total_output_tax - $input_tax_details['total_tax'] - $expense_tax_details['total_tax'];

//             return [
//                     'tax_diff' => $tax_diff,
//                     'input_tax_details'=>$input_tax_details,
//                     'output_tax_details'=>$output_tax_details,
//                     'expense_tax_details'=>$expense_tax_details
//                 ];

//     }

// }
// }
