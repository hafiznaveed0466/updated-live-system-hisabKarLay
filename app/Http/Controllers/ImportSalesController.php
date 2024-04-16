<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\TypesOfService;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\TransactionPayment;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportSalesController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $imported_sales = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->whereNotNull('import_batch')
                            ->with(['sales_person'])
                            ->select('id', 'import_batch', 'import_time', 'invoice_no', 'created_by')
                            ->orderBy('import_batch', 'desc')
                            ->get();

        $imported_sales_array = [];
        foreach ($imported_sales as $sale) {
            $imported_sales_array[$sale->import_batch]['import_time'] = $sale->import_time;
            $imported_sales_array[$sale->import_batch]['created_by'] = $sale->sales_person->user_full_name;
            $imported_sales_array[$sale->import_batch]['invoices'][] = $sale->invoice_no;
        }

        $import_fields = $this->__importFields();

        return view('import_sales.index')->with(compact('imported_sales_array', 'import_fields'));
    }

    /**
     * Preview imported data and map columns with sale fields
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->hasFile('sales')) {
            $file_name = time().'_'.$request->sales->getClientOriginalName();
            $request->sales->storeAs('temp', $file_name);

            $parsed_array = $this->__parseData($file_name);

            // dd($parsed_array);

            $import_fields = $this->__importFields();
            foreach ($import_fields as $key => $value) {
                $import_fields[$key] = $value['label'];
            }

            //Evaluate highest matching field with the header to pre select from dropdown
            $headers = $parsed_array[0];
            $match_array = [];
            foreach ($headers as $key => $value) {
                $match_percentage = [];
                foreach ($import_fields as $k => $v) {
                    similar_text($value, $v, $percentage);
                    $match_percentage[$k] = $percentage;
                }
                $max_key = array_keys($match_percentage, max($match_percentage))[0];

                //If match percentage is greater than 50% then pre select the value
                $match_array[$key] = $match_percentage[$max_key] >= 50 ? $max_key : null;
            }

            $business_locations = BusinessLocation::forDropdown($business_id);

            return view('import_sales.preview')->with(compact('parsed_array', 'import_fields', 'file_name', 'business_locations', 'match_array'));
        }
    }

    public function __parseData($file_name)
    {
        $array = Excel::toArray([], public_path('uploads/temp/'.$file_name))[0];

        //remove blank columns from headers
        $headers = array_filter($array[0]);

        //Remove header row
        unset($array[0]);
        $parsed_array[] = $headers;
        foreach ($array as $row) {
            $temp = [];
            foreach ($row as $k => $v) {
                if (array_key_exists($k, $headers)) {
                    $temp[] = $v;
                }
            }
            $parsed_array[] = $temp;
        }

        return $parsed_array;
    }

    /**
     * Import sales to database
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }


          $business_id = request()->session()->get('user.business_id');

        if ($request->hasFile('sales')) {
            $file_name = time().'_'.$request->sales->getClientOriginalName();
            $request->sales->storeAs('temp', $file_name);

            $parsed_array = $this->__parseData($file_name);

            // dd($parsed_array);

            $import_fields = $this->__importFields();
            foreach ($import_fields as $key => $value) {
                $import_fields[$key] = $value['label'];
            }

            //Evaluate highest matching field with the header to pre select from dropdown
            $headers = $parsed_array[0];
            $match_array = [];
            foreach ($headers as $key => $value) {
                $match_percentage = [];
                foreach ($import_fields as $k => $v) {
                    similar_text($value, $v, $percentage);
                    $match_percentage[$k] = $percentage;
                }
                $max_key = array_keys($match_percentage, max($match_percentage))[0];

                //If match percentage is greater than 50% then pre select the value
                $match_array[$key] = $match_percentage[$max_key] >= 50 ? $max_key : null;
            }
        }


        // try {
        //     DB::beginTransaction();

            // $file_name = $request->input('file_name');

            // $import_fields = $request->input('import_fields');
            // $group_by = $request->input('group_by');
            // $location_id = $request->input('location_id');
            // $business_id = $request->session()->get('user.business_id');

            // $file_path = public_path('uploads/temp/'.$file_name);

            // $parsed_array = $this->__parseData($file_name);
            //Remove header row
// dd($parsed_array);

            // dd($parsed_array, $import_fields, $group_by);
            unset($parsed_array[0]);

            // $formatted_sales_data = $this->__formatSaleData($parsed_array, $import_fields, $group_by);
            // dd($formatted_sales_data);
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            $this->__importSales($parsed_array, $business_id, $location_id  = null);

            // DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.sales_imported_successfully'),
            ];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        //     $output = ['success' => 0,
        //         'msg' => $e->getMessage(),
        //     ];

        //     @unlink($file_path);

        //     return redirect('import-sales')->with('notification', $output);
        // }

        // @unlink($file_path);

        return redirect('import-sales')->with('status', $output);
    }



    // private function __importSales($formated_data, $business_id, $location_id)
    // {
    //     // dd($formated_data);

    //     $import_batch = Transaction::where('business_id', $business_id)->max('import_batch');
      
    //     if (empty($import_batch)) {
    //         $import_batch = 1;
    //     } else {
    //         $import_batch = $import_batch + 1;
    //     }

    //     $now = \Carbon::now()->toDateTimeString();
    //     $row_index = 2;
    //     foreach ($formated_data as $data) {
    //         // Create an empty array to store TransactionSellLine instances
    //         $sell_lines = [];
        
    //         // Split product names and variations
    //         $product_names = explode('|', $data[19]);
    //         $variation_names = explode('|', $data[20]);
    //         $quantities = explode('|', $data[21]);
    //         $unit_prices_before_discount = explode('|', $data[22]);
    //         $unit_prices = explode('|', $data[23]);
    //         $item_discounts = explode('|', $data[24]);
    //         $item_amounts = explode('|', $data[25]);
    //         $unit_price_inc_taxs = explode('|', $data[26]);
    //         $item_taxes = explode('|', $data[27]);
    //         $tax_ids = $data[28];
        
    //         // Process each line of the sale
    //         for ($key = 0; $key < count($product_names); $key++) {
    //             // Find the product by name and business ID
    //             $product = Product::where('name', $product_names[$key])
    //                 ->where('business_id', $business_id)
    //                 ->first();
        
    //             // Check if the product exists
    //             if (empty($product)) {
    //                 // Throw an exception if the product is not found
    //                 throw new \Exception(__('Product :product_name not found in row :row_index', ['product_name' => $product_names[$key], 'row_index' => $row_index]));
    //             }
        
    //             // Find the variation by sub SKU and product ID
    //             $variation = Variation::where('sub_sku', trim($variation_names[$key]))
    //                 ->where('product_id', $product->id)
    //                 ->first();
        
    //             // Check if the variation exists
    //             if (empty($variation)) {
    //                 // Throw an exception if the variation is not found
    //                 throw new \Exception(__('Variation :variation_name for product :product_name not found in row :row_index', ['variation_name' => $variation_names[$key], 'product_name' => $product_names[$key], 'row_index' => $row_index]));
    //             }
        
    //             // Calculate line total
    //             $line_quantity = $quantities[$key];
    //             $unit_price_before_discount = $unit_prices_before_discount[$key];
    //             $unit_price = $unit_prices[$key];
    //             $line_discount = $item_discounts[$key];
    //             $item_amount = $item_amounts[$key];
    //             $unit_price_inc_tax = $unit_price_inc_taxs[$key];
    //             $item_tax = $item_taxes[$key];
        
    //             // Get enable_stock value from the product
    //             $enable_stock = $product->enable_stock;
        
    //             // Create sell line and add it to the array
    //             $sell_lines[] = [
    //                 'product_id' => $product->id,
    //                 'variation_id' => $variation->id,
    //                 'quantity' => $line_quantity,
    //                 'unit_price_before_discount' => $unit_price_before_discount,
    //                 'unit_price' => $unit_price,
    //                 'line_discount_type' => $line_discount,
    //                 'line_discount_amount' => $item_amount,
    //                 'unit_price_inc_tax' => $unit_price_inc_tax,
    //                 'item_tax' => $item_tax,
    //                 'tax_id' => null,
    //                 'enable_stock' => $enable_stock
    //                 // Add other sell line details here
    //             ];
    //         }

    //     // $first_sell_line = $data;
    //         //get contact
    //         if (!empty($data[2])) {
    //             $contact = Contact::where('business_id', $business_id)
    //                             ->where('mobile', $data[2])
    //                             ->first();
    //         } elseif (! empty($data[3])) {
    //             $contact = Contact::where('business_id', $business_id)
    //                             ->where('email', $data['3'])
    //                             ->first();
    //         }

    //         $contact = Contact::where('business_id', $business_id)
    //         ->where('name', $data[1])
    //         ->first();

    //         if (empty($contact)) {
    //             $customer_name = ! empty($data[1]) ? $data[1] : $data[2];
    //             $contact = Contact::create([
    //                 'business_id' => $business_id,
    //                 'type' => 'customer',
    //                 'name' => $customer_name,
    //                 'email' => $data[3],
    //                 'mobile' => $data[2],
    //                 'created_by' => auth()->user()->id,
    //             ]);
    //         }

    //         // Res Tables
    //             if(!empty($data[6]))
    //             {
    //                                 $res_table = \App\Restaurant\ResTable::firstOrCreate(
    //                                     ['business_id' => $business_id, 'name' => $data[6]],
    //                                     ['created_by' => Auth::user()->id , 'location_id' => $location_id]
    //                                 );

    //                                 $data[6] =  $res_table->id;
    //             }else{
    //                                 $data[6] =  "";
                                    
    //                             }

    //             if(!empty($data[5]))
    //             {
    //                  $location = BusinessLocation::where('business_id' , $business_id)
    //                                  ->where('name' , $data[5])->first();

    //                 $location_id  = $location->id;
    //                 $data[5] =   $location_id;

    //             }else{
    //                 $data[5] = $location_id;
    //             }

    //             // Transcations Table
    //         $sale_data = [
    //             'invoice_no' => $data[0],
    //             'location_id' => $location_id,
    //             'status' => 'final',
    //             'contact_id' => $contact->id,
    //             'final_total' => $data[16],
    //             'transaction_date' => ! empty($data[4]) ? $data[4] : $now,
    //             'discount_amount' =>! empty($data[14]) ? $data[14] : 0,
    //             'discount_type' => ! empty($data[12]) ? $data[12] : "",
    //             'import_batch' => $import_batch,
    //             'import_time' => $now,
    //             'commission_agent' => null,
    //             'res_table_id' => $data[6],
    //             'total_before_tax' => $data[12],
    //             'payment_status' => 'paid',
    //         ];

    //         $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
    //         if ($is_types_service_enabled && ! empty($data[25])) {
    //             $types_of_service = TypesOfService::where('business_id', $business_id)
    //                                             ->where('name', $data[25])
    //                                             ->first();

    //             if (empty($types_of_service)) {
    //                 throw new \Exception(__('lang_v1.types_of_servicet_not_found', ['row' => $row_index, 'types_of_service_name' => $data[25]]));
    //             }

    //             $sale_data['types_of_service_id'] = $types_of_service->id;
    //             $sale_data['service_custom_field_1'] = ! empty($data['service_custom_field1']) ? $data['service_custom_field1'] : null;
    //             $sale_data['service_custom_field_2'] = ! empty($data['service_custom_field2']) ? $data['service_custom_field2'] : null;
    //             $sale_data['service_custom_field_3'] = ! empty($data['service_custom_field3']) ? $data['service_custom_field3'] : null;
    //             $sale_data['service_custom_field_4'] = ! empty($data['service_custom_field4']) ? $data['service_custom_field4'] : null;
    //         }

    //         $invoice_total = [
    //             'total_before_tax' =>  $data[12],
    //             'tax' => 0,
    //         ];


    //         $transaction = $this->transactionUtil->createSellTransaction($business_id, $sale_data, $invoice_total, auth()->user()->id, false);
       
    //         $this->transactionUtil->createOrUpdateSellLines($transaction, $sell_lines, $location_id, false, null, [], false);

    //         // dd($sell_lines);

    //         // foreach ($sell_lines as $line) {
    //                 // dd($sell_lines , $line);
    //             if (isset($line['enable_stock'])) {
    //                 $this->productUtil->decreaseProductQuantity(
    //                     $line['product_id'],
    //                     $line['variation_id'],
    //                     $location_id,
    //                     $line['quantity']
    //                 );
    //             }

    //             // if ($line['type'] == 'combo') {
    //             //     $line_total_quantity = $line['quantity'];
    //             //     if (! empty($line['base_unit_multiplier'])) {
    //             //         $line_total_quantity = $line_total_quantity * $line['base_unit_multiplier'];
    //             //     }

    //             //     //Decrease quantity of combo as well.
    //             //     $combo_details = [];
    //             //     foreach ($line['combo_variations'] as $combo_variation) {
    //             //         $combo_variation_obj = Variation::find($combo_variation['variation_id']);

    //             //         //Multiply both subunit multiplier of child product and parent product to the quantity
    //             //         $combo_variation_quantity = $combo_variation['quantity'];
    //             //         if (! empty($combo_variation['unit_id'])) {
    //             //             $combo_variation_unit = Unit::find($combo_variation['unit_id']);
    //             //             if (! empty($combo_variation_unit->base_unit_multiplier)) {
    //             //                 $combo_variation_quantity = $combo_variation_quantity * $combo_variation_unit->base_unit_multiplier;
    //             //             }
    //             //         }

    //             //         $combo_details[] = [
    //             //             'product_id' => $combo_variation_obj->product_id,
    //             //             'variation_id' => $combo_variation['variation_id'],
    //             //             'quantity' => $combo_variation_quantity * $line_total_quantity,
    //             //         ];
    //             //     }

    //             //     $this->productUtil
    //             //         ->decreaseProductQuantityComboImport(
    //             //             $combo_details,
    //             //             $location_id
    //             //         );
    //             // }
    //         // }

    //         // $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
    //         // $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);

    //         // $paymnet_ref_no = "SP". $input['subscription_no'];
    //         // // dd($ref_count , $input['subscription_no']);
    //         // //Update payment status
    //         //  $payment =  $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

    //         //   $t =  TransactionPayment::find($transaction->id);
    //         //   $t->created_by = Auth::user()->id;
    //         //   $t->paid_on = now();
    //         //   $t->payment_for = 1;
    //         //   $t->payment_ref_no =  $paymnet_ref_no;
    //         //   $t->save();

    //         $business_details = $this->businessUtil->getDetails($business_id);
    //         $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

    //         $business = ['id' => $business_id,
    //             'accounting_method' => request()->session()->get('business.accounting_method'),
    //             'location_id' => $location_id,
    //             'pos_settings' => $pos_settings,
    //         ];
    //         $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');
    //     }
    // }


    private function __importSales($formated_data, $business_id, $location_id)
{
    $import_batch = Transaction::where('business_id', $business_id)->max('import_batch');

    if (empty($import_batch)) {
        $import_batch = 1;
    } else {
        $import_batch = $import_batch + 1;
    }

    $now = \Carbon::now()->toDateTimeString();
    $row_index = 2;
    foreach ($formated_data as $data) 
    {

         // Skip empty rows
    if (empty(array_filter($data))) {
        continue;
    }
        // Create an empty array to store TransactionSellLine instances
        $sell_lines = [];
        $processedComboProducts = [];
        // Split product names and variations
        $product_names = explode('|', $data[19]);
        $variation_names = explode('|', $data[20]);
        $quantities = explode('|', $data[21]);
        $unit_prices_before_discount = explode('|', $data[22]);
        $unit_prices = explode('|', $data[23]);
        $item_discounts = explode('|', $data[24]);
        $item_amounts = explode('|', $data[25]);
        $unit_price_inc_taxs = explode('|', $data[26]);
        $item_taxes = explode('|', $data[27]);
        $child_types = explode('|', $data[34]);
        $parent_sells = explode('|', $data[35]);
        $tax_t_id = explode('|', $data[36]);
        $tax_ids = $data[28];
        // $pyemnt_paymnet = $data[9];
// dd($child_types);
        // Process each line of the sale
        for ($key = 0; $key < count($product_names); $key++) {
            // Find the product by name and business ID
            $product = Product::where('name', $product_names[$key])
                ->with(['modifier_products'])
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
                ->where('product_id', $product->id)
                ->first();

            // Check if the variation exists
            if (empty($variation)) {
                 throw new \Exception(__('Variation :variation_name for product :product_name not found in row :row_index', ['variation_name' => $variation_names[$key], 'product_name' => $product_names[$key], 'row_index' => $row_index]));
            //         \Log::error(__('Variation :variation_name for product :product_name not found in row :row_index', ['variation_name' => $variation_names[$key], 'product_name' => $product_names[$key], 'row_index' => $row_index]));
            // // Move to the next iteration
            //         continue;
            }

            $tax_Rates = TaxRate::where('name', trim($tax_t_id[$key]))
            ->where('business_id', $business_id)
            ->first();

            $taxID = isset($tax_Rates[$key]) ? $tax_Rates[$key] : NULL; 

            // dd($taxID);
            // if(){

            // }

            // $parent_s = Product::where('business_id', $business_id)
            //                         ->where('name', $parent_sells[$key])
            //                         ->with(['variations'])
            //                         ->first();

            // $parent_sell_line = !empty($parent_s) ? $parent_s->variations->first(): null;


            // if ($product->type == 'combo') {
            //     // Check if the combo product has already been processed
            //     $comboProductId = $product->id;
            //     if (in_array($comboProductId, $processedComboProducts)) {
            //         // Skip this combo product as it has already been processed
            //         continue;
            //     }
    
            //     // Add the combo product ID to the list of processed combo products
            //     $processedComboProducts[] = $comboProductId;
            // }

            
            // $parent_s = Product::where('business_id', $business_id)
            //                         ->where('name', $parent_sells[$key])
            //                         ->with(['variations'])
            //                         ->first();

            // $parent_sell_line = !empty($parent_s) ? $parent_s->variations->first(): null;
            // dd();
            // $parent_sells = Variation::where('sub_sku', $parent_sells[$key])->with(['product'])->first();

            // $parent_sell_line = !empty($parent_sells) ? $parent_sells->product : null;

            // dd($parent_sell_line);

            // dd($product);
            // Check if the product exists
            // if (empty($parent_sell)) {

            //     $parent_sell = "";
            // }



            // dd($product_names);
            // Calculate line total
            $line_quantity = $quantities[$key];
            $unit_price_before_discount = $unit_prices_before_discount[$key];
            $unit_price = $unit_prices[$key];
            $line_discount = $item_discounts[$key];
            $item_amount = $item_amounts[$key];
            $unit_price_inc_tax = $unit_price_inc_taxs[$key];
            $item_tax = $item_taxes[$key];
            $child_type = $child_types[$key];
            $parent_sell = $parent_sells[$key];
     
            // Get enable_stock value from the product
            $enable_stock = $product->enable_stock;
           
            // dd($child_type);
            // Create sell line and add it to the array
            $sell_lines[] = [
                'product_id' => $product->id,
                'product_type' => $product->type,
                'variation_id' => $variation->id,
                'quantity' => $line_quantity,
                'unit_price_before_discount' => $unit_price_before_discount,
                'unit_price' => $unit_price,
                'line_discount_type' => $line_discount,
                'line_discount_amount' => $item_amount,
                'unit_price_inc_tax' => $unit_price_inc_tax,
                'item_tax' => $item_tax,
                'tax_id' => $taxID,
                'enable_stock' => $enable_stock,
                'parent_sell_line_id' => $parent_sell,
                'children_type' => $child_type,
                // 'combo_variations' => $variation->combo_variations ?? []
                // Add other sell line details here
            ];


            // if ($product->type === 'modifier') {
            //     // Set the parent_sell_line_id to the id of the previous sell line
            //     $sell_line['parent_sell_line_id'] = $previous_sell_line_id;
            // }
    
            // // Add the sell line to the array
            // $sell_lines[] = $sell_line;
    
            // // Save the id of the current sell line for use in the next iteration
            // $previous_sell_line_id = $transaction->sell_lines[$key]->id;

        }

        // dd($product);
        // Process contact
        $contact = Contact::where('business_id', $business_id)
            ->where('mobile', $data[2])
            ->orWhere('email', $data[3])
            ->orWhere('name', $data[1])
            ->first();

        if (empty($contact)) {
            $customer_name = !empty($data[1]) ? $data[1] : $data[2];
            $contact = Contact::create([
                'business_id' => $business_id,
                'type' => 'customer',
                'name' => $customer_name,
                'email' => $data[3],
                'mobile' => $data[2],
                'created_by' => auth()->user()->id,
            ]);
        }

        // Process Res Tables and Locations
        $res_table = null;
        if (!empty($data[6])) {
            $res_table = \App\Restaurant\ResTable::firstOrCreate(
                ['business_id' => $business_id, 'name' => $data[6]],
                ['created_by' => auth()->user()->id, 'location_id' => $location_id]
            );
            
            $res_table = $res_table->id;
        }
      

        if (!empty($data[5])) {
            $location = BusinessLocation::where('business_id', $business_id)
                ->where('name', $data[5])->first();
            if (!empty($location)) {
                $location_id = $location->id;
            }
        }

        // Process Transactions
        $sale_data = [
            'invoice_no' => $data[0],
            'location_id' => $location_id,
            'status' => 'final',
            'contact_id' => $contact->id,
            'final_total' => $data[17],
            'transaction_date' => !empty($data[4]) ? $data[4] : $now,
            'discount_type' => !empty($data[14]) ? $data[14] : "",
            'discount_amount' => !empty($data[15]) ? $data[15] : 0,
            'import_batch' => $import_batch,
            'import_time' => $now,
            'commission_agent' => null,
            'res_table_id' => $res_table,
            'total_before_tax' => $data[12],
            'payment_status' => $data[9]
        ];

        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        if ($is_types_service_enabled && !empty($data[25])) {
            $types_of_service = TypesOfService::where('business_id', $business_id)
                ->where('name', $data[25])
                ->first();

            if (empty($types_of_service)) {
                throw new \Exception(__('lang_v1.types_of_servicet_not_found', ['row' => $row_index, 'types_of_service_name' => $data[25]]));
            }

            $sale_data['types_of_service_id'] = $types_of_service->id;
            $sale_data['service_custom_field_1'] = !empty($data['service_custom_field1']) ? $data['service_custom_field1'] : null;
            $sale_data['service_custom_field_2'] = !empty($data['service_custom_field2']) ? $data['service_custom_field2'] : null;
            $sale_data['service_custom_field_3'] = !empty($data['service_custom_field3']) ? $data['service_custom_field3'] : null;
            $sale_data['service_custom_field_4'] = !empty($data['service_custom_field4']) ? $data['service_custom_field4'] : null;
        }

        $invoice_total = [
            'total_before_tax' => $data[12],
            'tax' => 0,
        ];

        $transaction = $this->transactionUtil->createSellTransaction($business_id, $sale_data, $invoice_total, auth()->user()->id, false);

        $this->transactionUtil->createOrUpdateSellLines($transaction, $sell_lines, $location_id, false, null, [], false);

        // Decrease product quantity if necessary
        foreach ($sell_lines as $line) {
            if (isset($line['enable_stock'])) {
                $this->productUtil->decreaseProductQuantity(
                    $line['product_id'],
                    $line['variation_id'],
                    $location_id,
                    $line['quantity']
                );
            }


            // if ($line['type'] == 'combo') {
            //         $line_total_quantity = $line['quantity'];
            //         if (! empty($line['base_unit_multiplier'])) {
            //             $line_total_quantity = $line_total_quantity * $line['base_unit_multiplier'];
            //         }

            //         //Decrease quantity of combo as well.
            //         $combo_details = [];
            //         foreach ($line['combo_variations'] as $combo_variation) {
            //             $combo_variation_obj = Variation::find($combo_variation['variation_id']);

            //             //Multiply both subunit multiplier of child product and parent product to the quantity
            //             $combo_variation_quantity = $combo_variation['quantity'];
            //             if (! empty($combo_variation['unit_id'])) {
            //                 $combo_variation_unit = Unit::find($combo_variation['unit_id']);
            //                 if (! empty($combo_variation_unit->base_unit_multiplier)) {
            //                     $combo_variation_quantity = $combo_variation_quantity * $combo_variation_unit->base_unit_multiplier;
            //                 }
            //             }

            //             $combo_details[] = [
            //                 'product_id' => $combo_variation_obj->product_id,
            //                 'variation_id' => $combo_variation['variation_id'],
            //                 'quantity' => $combo_variation_quantity * $line_total_quantity,
            //             ];
            //         }

            //         $this->productUtil
            //             ->decreaseProductQuantityComboImport(
            //                 $combo_details,
            //                 $location_id
            //             );
            //     }

        }

        // dd($transaction);
            $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
            $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);

            $paymnet_ref_no = "SP". $input['subscription_no'];
            //Update payment status
            $p = $this->transactionUtil->updatePaymentStatusImport($transaction->id, $transaction->final_total ,$transaction->import_batch);
        
             $t = TransactionPayment::where('transaction_id' ,$transaction->id)->where('business_id' , $business_id)->first();
            //  dd($t);
              $t->created_by = Auth::user()->id;
              $t->paid_on = now();
              $t->payment_for = $transaction->contact_id;
              $t->payment_ref_no =  $paymnet_ref_no;
              $t->save();

        // Map purchase and sell if necessary
        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $business = ['id' => $business_id,
            'accounting_method' => request()->session()->get('business.accounting_method'),
            'location_id' => $location_id,
            'pos_settings' => $pos_settings,
        ];
        $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

        // Increment row index
        
        $row_index++;
    }
}





    private function __formatSaleData($imported_data, $import_fields, $group_by)
    {
        // dd($imported_data);
        $formatted_array = [];
        $invoice_number_key = array_search('invoice_no', $import_fields);
        $customer_name_key = array_search('customer_name', $import_fields);
        $customer_phone_key = array_search('customer_phone_number', $import_fields);
        $customer_email_key = array_search('customer_email', $import_fields);
        $date_key = array_search('date', $import_fields);
        $location_name_key = array_search('location_name', $import_fields);
        $table_name_key = array_search('table_name', $import_fields);
        $payment_status_key = array_search('payment_status', $import_fields);
        $tax_name_key = array_search('tax_name', $import_fields);
        $discount_type_key = array_search('discount_type', $import_fields);
        $recur_interval_type_key = array_search('recur_interval_type', $import_fields);
        $create_by_key = array_search('create_by', $import_fields);
        $product_key = array_search('product', $import_fields);
        $sku_key = array_search('sku', $import_fields);
        $quantity_key = array_search('quantity', $import_fields);
        $unit_price_key = array_search('unit_price', $import_fields);
        $item_tax_key = array_search('item_tax', $import_fields);
        $item_discount_key = array_search('item_discount', $import_fields);
        $item_description_key = array_search('item_description', $import_fields);
        $order_total_key = array_search('order_total', $import_fields);
        $unit_key = array_search('unit', $import_fields);
        $tos_key = array_search('types_of_service', $import_fields);
        $service_custom_field1_key = array_search('service_custom_field1', $import_fields);
        $service_custom_field2_key = array_search('service_custom_field2', $import_fields);
        $service_custom_field3_key = array_search('service_custom_field3', $import_fields);
        $service_custom_field4_key = array_search('service_custom_field4', $import_fields);

        $row_index = 2;
        foreach ($imported_data as $key => $value) {
            $formatted_array[$key]['invoice_no'] = $invoice_number_key !== false ? $value[$invoice_number_key] : null;
            $formatted_array[$key]['customer_name'] = $customer_name_key !== false ? $value[$customer_name_key] : null;
            $formatted_array[$key]['customer_phone_number'] = $customer_phone_key !== false ? $value[$customer_phone_key] : null;
            $formatted_array[$key]['customer_email'] = $customer_email_key !== false ? $value[$customer_email_key] : null;
            $formatted_array[$key]['date'] = $date_key !== false ? $value[$date_key] : null;
            $formatted_array[$key]['location_name'] = $location_name_key !== false ? $value[$location_name_key] : null;
            $formatted_array[$key]['table_name'] = $table_name_key !== false ? $value[$table_name_key] : null;
            $formatted_array[$key]['payment_status'] = $payment_status_key !== false ? $value[$payment_status_key] : null;
            $formatted_array[$key]['tax_name'] = $tax_name_key !== false ? $value[$tax_name_key] : null;
            $formatted_array[$key]['discount_type'] = $discount_type_key !== false ? $value[$discount_type_key] : null;
            $formatted_array[$key]['recur_interval_type'] =$recur_interval_type_key !== false ? $value[$recur_interval_type_key] : null;
            $formatted_array[$key]['create_by'] = $create_by_key !== false ? $value[$create_by_key] : null;
            $formatted_array[$key]['product'] = $product_key !== false ? $value[$product_key] : null;
            $formatted_array[$key]['sku'] = $sku_key !== false ? $value[$sku_key] : null;
            $formatted_array[$key]['quantity'] = $quantity_key !== false ? $value[$quantity_key] : null;
            $formatted_array[$key]['unit_price'] = $unit_price_key !== false ? $value[$unit_price_key] : null;
            $formatted_array[$key]['item_tax'] = $item_tax_key !== false ? $value[$item_tax_key] : null;
            $formatted_array[$key]['item_discount'] = $item_discount_key !== false ? $value[$item_discount_key] : null;
            $formatted_array[$key]['item_description'] = $item_description_key !== false ? $value[$item_description_key] : null;
            $formatted_array[$key]['order_total'] = $order_total_key !== false ? $value[$order_total_key] : null;
            $formatted_array[$key]['unit'] = $unit_key !== false ? $value[$unit_key] : null;
            $formatted_array[$key]['types_of_service'] = $tos_key !== false ? $value[$tos_key] : null;
            $formatted_array[$key]['service_custom_field1'] = $service_custom_field1_key !== false ? $value[$service_custom_field1_key] : null;
            $formatted_array[$key]['service_custom_field2'] = $service_custom_field2_key !== false ? $value[$service_custom_field2_key] : null;
            $formatted_array[$key]['service_custom_field3'] = $service_custom_field3_key !== false ? $value[$service_custom_field3_key] : null;
            $formatted_array[$key]['service_custom_field4'] = $service_custom_field4_key !== false ? $value[$service_custom_field4_key] : null;
            $formatted_array[$key]['customer_phone_number'] = $customer_phone_key !== false ? $value[$customer_phone_key] : null;
            $formatted_array[$key]['customer_email'] = $customer_email_key !== false ? $value[$customer_email_key] : null;
            $formatted_array[$key]['group_by'] = $value[$group_by];

            //check empty
            // if (empty($formatted_array[$key]['customer_phone_number']) && empty($formatted_array[$key]['customer_email'])) {
            //     throw new \Exception(__('lang_v1.email_or_phone_cannot_be_empty_in_row', ['row' => $row_index]));
            // }
            if (empty($formatted_array[$key]['product']) && empty($formatted_array[$key]['sku'])) {
                throw new \Exception(__('lang_v1.product_cannot_be_empty_in_row', ['row' => $row_index]));
            }
            if (empty($formatted_array[$key]['quantity'])) {
                throw new \Exception(__('lang_v1.quantity_cannot_be_empty_in_row', ['row' => $row_index]));
            }
            if (empty($formatted_array[$key]['unit_price'])) {
                throw new \Exception(__('lang_v1.unit_price_cannot_be_empty_in_row', ['row' => $row_index]));
            }

            $row_index++;
        }
        $group_by_key = $import_fields[$group_by];
        $formatted_data = [];
        foreach ($formatted_array as $array) {
            $formatted_data[$array['group_by']][] = $array;
        }

        return $formatted_data;
    }

    private function __importFields()
    {
        $fields = [
            'invoice_no' => ['label' => __('sale.invoice_no')],
            'customer_name' => ['label' => __('sale.customer_name')],
            'customer_phone_number' => ['label' => __('lang_v1.customer_phone_number'), 'instruction' => __('lang_v1.either_cust_email_or_phone_required')],
            'customer_email' => ['label' => __('lang_v1.customer_email'), 'instruction' => __('lang_v1.either_cust_email_or_phone_required')],
            'date' => ['label' => __('sale.sale_date'), 'instruction' => __('lang_v1.date_format_instruction')],
            'location_name' => ['label' => __('location_name'), 'instruction' => __('location_name')],
            'table_name' => ['label' => __('table_name'), 'instruction' => __('table_name')],
            'payment_status' => ['label' => __('payment_status'), 'instruction' => __('payment_status')],
            'tax_name' => ['label' => __('tax_name'), 'instruction' => __('tax_name')],
            'discount_type' => ['label' => __('discount_type'), 'instruction' => __('discount_type')],
            'recur_interval_type' => ['label' => __('recur_interval_type'), 'instruction' => __('recur_interval_type')],
            'create_by' => ['label' => __('create_by'), 'instruction' => __('create_by')],
            'product' => ['label' => __('product.product_name'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'sku' => ['label' => __('lang_v1.product_sku'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'quantity' => ['label' => __('lang_v1.quantity'), 'instruction' => __('lang_v1.required')],
            'unit' => ['label' => __('lang_v1.product_unit')],
            'unit_price' => ['label' => __('sale.unit_price')],
            'item_tax' => ['label' => __('lang_v1.item_tax')],
            'item_discount' => ['label' => __('lang_v1.item_discount')],
            'item_description' => ['label' => __('lang_v1.item_description')],
            'order_total' => ['label' => __('lang_v1.order_total')],
        ];

        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if ($is_types_service_enabled) {
            $fields['types_of_service'] = ['label' => __('lang_v1.types_of_service')];
            $fields['service_custom_field1'] = ['label' => __('lang_v1.service_custom_field_1')];
            $fields['service_custom_field2'] = ['label' => __('lang_v1.service_custom_field_2')];
            $fields['service_custom_field3'] = ['label' => __('lang_v1.service_custom_field_3')];
            $fields['service_custom_field4'] = ['label' => __('lang_v1.service_custom_field_4')];
        }

        return $fields;
    }

    /**
     * Deletes all sales from a batch
     *
     * @return \Illuminate\Http\Response
     */
    public function revertSaleImport($batch)
    {
        if (! auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $sales = Transaction::where('business_id', $business_id)
                                ->where('type', 'sell')
                                ->where('import_batch', $batch)
                                ->get();
            //Begin transaction
            DB::beginTransaction();
            foreach ($sales as $sale) {
                $this->transactionUtil->deleteSale($business_id, $sale->id);
            }

            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.import_reverted_successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return redirect('import-sales')->with('status', $output);
    }
    


    // Sale Export
    public function StoreExport()
{
    // $currentMonth = date('m');
    $currentYear = date('Y');
    $business_id = Auth::user()->business_id;
    
    $sales = DB::table('transactions')
        ->leftJoin('business', 'business.id', '=', 'transactions.business_id')
        ->leftJoin('business_locations', 'business_locations.id', '=', 'transactions.location_id')
        ->leftJoin('res_tables', 'res_tables.id', '=', 'transactions.res_table_id')
        ->leftJoin('users', 'users.id', '=', 'transactions.created_by')
        ->leftJoin('transaction_sell_lines', 'transaction_sell_lines.id', '=', 'transaction_sell_lines.transaction_id')
        ->leftJoin('tax_rates', 'tax_rates.id', '=', 'transaction_sell_lines.tax_id')
        ->leftJoin('tax_rates as tr', 'tr.id', '=', 'transactions.tax_id')
        ->leftJoin('products', 'transaction_sell_lines.product_id', '=', 'products.id')
        ->leftJoin('variations', 'variations.id', '=', 'transaction_sell_lines.variation_id')
        ->leftJoin('types_of_services', 'transactions.types_of_service_id', '=', 'types_of_services.id')
        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->where('transactions.type', '=', 'sell')
        ->where('transactions.status', '=', 'final')
        ->where('transactions.business_id', $business_id)
        ->whereYear('transactions.transaction_date', '=', $currentYear)
        ->where('contacts.type', '=', 'customer')
        ->select(
            'transactions.id as transaction_id',
            'transactions.invoice_no',
            'transactions.transaction_date', 
            'transactions.type', 
            'transactions.status', 
            'transactions.is_quotation', 
            'transactions.payment_status', 
            'transactions.total_before_tax', 
            'transactions.tax_id', 
            'transactions.total_before_tax', 
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
            'transactions.custom_field_1',
            'transactions.custom_field_2',
            'transactions.custom_field_3',
            'transactions.custom_field_4', 
            'business.name as bus_name', 
            'business_locations.name as loc_name', 
            'res_tables.name as res_name',
            'types_of_services.name as s_name',
            'tr.name as t_name',
            'contacts.name', 
            'contacts.email', 
            'contacts.mobile',
            // 'transaction_sell_lines.id',


            //  Trancations sell lines 

            // product id
            DB::raw('(SELECT GROUP_CONCAT(products.name SEPARATOR "|") 
            FROM transaction_sell_lines 
            LEFT JOIN products ON transaction_sell_lines.product_id = products.id
            WHERE transaction_sell_lines.transaction_id = transactions.id) as products'),

            // variation_id
            DB::raw('(SELECT GROUP_CONCAT(variations.sub_sku SEPARATOR "|") 
            FROM transaction_sell_lines 
            LEFT JOIN variations ON transaction_sell_lines.variation_id = variations.id
            WHERE transaction_sell_lines.transaction_id = transactions.id) as product_sku'),

            // quantities
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.quantity SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as quantities'),


            // unit_price_before_discount
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.unit_price_before_discount SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as unit_price_before_discount'),

             //  unit_price (Sell price excluding tax)

             DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.unit_price SEPARATOR "|") 
             FROM transaction_sell_lines 
             WHERE transaction_sell_lines.transaction_id = transactions.id) as unit_price'),
            

            //  line_discount_type
            DB::raw('(SELECT GROUP_CONCAT(COALESCE(transaction_sell_lines.line_discount_type, "") SEPARATOR "|") 
                FROM transaction_sell_lines 
                WHERE transaction_sell_lines.transaction_id = transactions.id) as line_discount_types'),

            // line_discount_amount

            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.line_discount_amount SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as line_discount_amount'),

            // unit_price_inc_tax(Sell price including tax )
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.unit_price_inc_tax SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as unit_price_inc_tax'),

            // item_tax
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.item_tax SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as item_tax'),

            // tax_id
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.tax_id SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as tax_id'),

            // clindern_type // Type of children for the parent, like modifier or combo
           
            DB::raw('(SELECT GROUP_CONCAT(transaction_sell_lines.children_type  SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as children_type'),

            // Presnt sell line ID

            DB::raw('(SELECT GROUP_CONCAT(COALESCE(transaction_sell_lines.parent_sell_line_id, "") SEPARATOR "|") 
            FROM transaction_sell_lines 
            WHERE transaction_sell_lines.transaction_id = transactions.id) as parent_sell_line_id'),

            // Tax rate id in transaction_sell_lines

            DB::raw('(SELECT GROUP_CONCAT(COALESCE(tax_rates.name, "") SEPARATOR "|") 
            FROM transaction_sell_lines 
            LEFT JOIN tax_rates ON transaction_sell_lines.tax_id  = tax_rates.id
            WHERE transaction_sell_lines.transaction_id = transactions.id) as tax_rates_name'),

            // DB::raw('(SELECT GROUP_CONCAT(COALESCE(tsl.parent_sell_line_id, "") SEPARATOR "|") 
            // FROM transaction_sell_lines AS tsl
            // WHERE tsl.transaction_id = transactions.id
            // ) as parent_sell_line'),

)
->get();
    
            
// dd($sales);
        // ->distinct() // Ensure only distinct records are fetched
        // ->get();

        foreach ($sales as $sale) {
            $key = $sale->invoice_no; // Use invoice number as a unique key to avoid duplicates
        
            // Handle null line_discount_types
            $line_discount_types = $sale->line_discount_types ?? '';
            $line_discount_types = ($line_discount_types !== '') ? explode('|', $line_discount_types) : [''];
        
            $parent_sell_line_ids = $sale->parent_sell_line_id ?? "";
            $parent_sell_line_ids = ($parent_sell_line_ids !== '') ? explode('|', $parent_sell_line_ids) : [''];
        
            $clindern_types = $sale->clindern_type ?? '';
            $clindern_types = ($clindern_types !== '') ? explode('|', $clindern_types) : [''];
        
            // Initialize the export data array for this invoice number if it doesn't exist
            if (!isset($export_data[$key])) {


                $export_data[$key] = [
                    'Invoice No.' => $sale->invoice_no,
                    'Customer Name' => $sale->name,
                    'Customer Phone Number' => $sale->mobile,
                    'Customer Email' => $sale->email,
                    'Sale Date' => $sale->transaction_date,
                    'Location Name' => $sale->loc_name,
                    'Table Name' => $sale->res_name,
                    'Type' => $sale->type,
                    'Status' => $sale->status,
                    'Payment Status' => $sale->payment_status,
                    'Tax Name' => $sale->tax_name,
                    'Tax Amount' => $sale->tax_amount,
                    'Total Before Tax' => $sale->total_before_tax,
                    'Tax Name in Transaction' => $sale->t_name,
                    'Discount Type' => $sale->discount_type,
                    'Discount Amount' => $sale->discount_amount,
                    'Recur Interval Type' => $sale->recur_interval_type,
                    'Final Total' => $sale->final_total,
                    'Created By' => $sale->create_by,
                    'Products' => $sale->products,
                    'Product SKU' => $sale->product_sku,
                    'Quantity' => $sale->quantities,
                    'Unit Price Before Discount' => $sale->unit_price_before_discount,
                    'Unit Price (Sell price excluding tax)' => $sale->unit_price,
                    'Item Discount' => $sale->line_discount_types,
                    'Item Amount' => $sale->line_discount_amount,
                    'Unit Price inc tax (Sell price including tax)' => $sale->unit_price_inc_tax,
                    'Item Tax' => $sale->item_tax,
                    'Tax ID' => $sale->tax_id,
                    'Types of Service' => $sale->s_name,
                    'Custom Field 1' => $sale->custom_field_1,
                    'Custom Field 2' => $sale->custom_field_2,
                    'Custom Field 3' => $sale->custom_field_3,
                    'Custom Field 4' => $sale->custom_field_4,
                    'children_type' => $sale->children_type,
                    'parent_sell_line_id' => $sale->parent_sell_line_id,
                    'tax_rates_name' => $sale->tax_rates_name
                ];
 
            }
        
            // Loop through each line_discount_type and add it to the export data array
            foreach ($line_discount_types as $index => $discount_type) 
            {
                // Add discount type to export data
                $export_data[$key]['Item Discount'] = ($discount_type !== null) ? $discount_type : '';
        
                // If clindern_types and parent_sell_line_ids are not empty, add them to the export data
                if (!empty($clindern_types[$index])) {
                    $export_data[$key]['children_type'] = $clindern_types[$index] ?? "";
                }
            }
        }
        
    if (count($export_data) > 0) {
        if (ob_get_contents()) {
            ob_end_clean();
        }
        ob_start();
        return collect(array_values($export_data))->downloadExcel('sales_export.xlsx', null, true);
    } else {
        return "No data to export.";
    }
}

}
