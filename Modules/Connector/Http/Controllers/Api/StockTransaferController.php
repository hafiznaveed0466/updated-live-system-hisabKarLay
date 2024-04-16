<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\TaxRate;
use App\Business;
use App\GroupSubTax;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\StockAdjustmentLine;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use App\BusinessLocation;
use App\PurchaseLine;
use App\TransactionSellLinesPurchaseLines;
use Spatie\Activitylog\Models\Activity;
use App\Events\StockTransferCreatedOrModified;

class StockTransaferController extends ApiController
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
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $user = Auth::user();
        $business_id = $user->business_id;

        $business = Business::find($business_id);

        if ($business) {
            $businessName = $business->name;
        } 


        $stock_transfers = Transaction::join(
            'business_locations AS l1',
            'transactions.location_id',
            '=',
            'l1.id'
        )
                ->join('transactions as t2', 't2.transfer_parent_id', '=', 'transactions.id')
                ->join(
                    'business_locations AS l2',
                    't2.location_id',
                    '=',
                    'l2.id'
                )
                ->join('activity_log as al', 'transactions.id', '=', 'al.subject_id')
                ->join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->join('products as p', 'tsl.product_id', '=', 'p.id')
                ->join('users as u', 'transactions.created_by', '=', 'u.id')
                ->join('product_variations as pv', 'tsl.variation_id', '=', 'pv.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_transfer')
                ->select(
                    'transactions.id as transaction_id',
                    'transactions.transaction_date',
                    'transactions.ref_no',
                    DB::raw("CONCAT(l1.name, ' ', l1.landmark , ' ', l1.city, ',', ' ', l1.state,',', l1.country) as location_from"),
                    'l1.mobile as location_from_mobile',
                    'l1.email as location_from_email',
                    DB::raw("CONCAT(l2.name, ' ', l2.landmark , ' ', l2.city, ',', ' ', l2.state,',', l2.country) as location_to"),
                    'l2.mobile as location_to_mobile',
                    'l2.email as location_to_email',
                    'transactions.final_total',
                    'transactions.shipping_charges',
                    'transactions.additional_notes',
                    'transactions.status',
                    'al.description',
                    'al.created_at',
                    'p.name as product_name',
                    'pv.name as variation_name',
                    'tsl.quantity as quantity_transafer',
                    'tsl.unit_price_before_discount',
                    'u.first_name as created_by'
                )->get()
                ->groupBy('transaction_id');


                $result = [];

                foreach ($stock_transfers as $groupedData) {

        
        
                    $products = collect($groupedData)->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'variation_name' => $item->variation_name,
                            'quantity_transafer' => $item->quantity_transafer,
                            'unit_price' => $item->unit_price_before_discount,
                            'subtotal' => $item->quantity_transafer * $item->unit_price_before_discount,
                        ];
                    });

                    $activities = [

                        'Date' => $groupedData->first()->created_at->format('Y-m-d H:i'),
                        'Description' => $groupedData->first()->description,
                        'By' => $groupedData->first()->created_by,
    
                ];

                    $location_from = [

                        'location_from' => $groupedData->first()->location_from,
                        'location_from_mobile' => $groupedData->first()->location_from_mobile,
                        'location_from_email' => $groupedData->first()->location_from_email,

                    ];

                    $location_to = [

                        'location_to' => $groupedData->first()->location_to,
                        'location_to_mobile' => $groupedData->first()->location_to_mobile,
                        'location_to_email' => $groupedData->first()->location_to_email,

                    ];



                    $result[] = [
                        'Business' => $businessName,
                        'created_by' => $groupedData->first()->created_by,
                        'shipping Chanrges' => $groupedData->first()->shipping_charges,
                        'status' => $groupedData->first()->status,
                        'final_total' => $groupedData->first()->final_total,
                        'Date' => $groupedData->first()->transaction_date,
                        'type' => 'sell_transfer',
                        'ref_no' => $groupedData->first()->ref_no,
                        'reason' => $groupedData->first()->additional_notes,
                        'Location From' => $location_from,
                        'Location To' => $location_to,
                        'products' => $products->toArray(),
                        'activities' => $activities,
                    ];
                    

                

                
            }
            return [
                'data' => $result
            ];


    }


    public function createStockTransfer(Request $request)
    {

        // dd('kjhj', $request);
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }
        // Get the authenticated user and business_id
        $user = Auth::user();

        $businessId = $user->business_id;

         //Update reference count
         $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer', $businessId);
         $Prefix = 'ST';
         $ref_no = $Prefix . $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);

        //  dd($ref_no);
        $final_total = 0;
        foreach($request->input('products') as $product)
        {
           $final_total += $product['unit_price'] * $product['quantity'];

        }

        $final_total += $request->shipping_charges;
        $status = $request->input('status');



        $input_data['business_id'] = $businessId;
        $input_data['location_id'] = $request->location_id;
        $input_data['type'] = 'sell_transfer';
        $input_data['status'] = $request->input('status');
        $input_data['created_by'] = $user->id;
        $input_data['final_total'] = $final_total;
        $input_data['total_before_tax'] = $final_total;
        $input_data['payment_status'] = 'paid';
        $input_data['shipping_charges'] = $request->shipping_charges;
        $input_data['additional_notes'] = $request->additional_notes;
        $input_data['transaction_date'] = \Carbon::now()->format('Y-m-d H:i:s');
        $input_data['ref_no'] = $ref_no;


        $products = $request->input('products');
        $sell_lines = [];
        $purchase_lines = [];

        if (! empty($products)) {
            foreach ($products as $product) {
                $sell_line_arr = [
                    'product_id' => $product['product_id'],
                    'variation_id' => $product['variation_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'subtotal' => $product['quantity'] * $product['unit_price'],
                    'unit_id' => $product['unit_id'],
                    'item_tax' => 0,
                    'tax_id' => null,

                     ];

                     $purchase_line_arr = $sell_line_arr;

                     $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                     $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];


                     $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                     $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];


                     $sell_lines[] = $sell_line_arr;
                     $purchase_lines[] = $purchase_line_arr;


                    
            }

        }



        //Create Sell Transfer transaction
        $sell_transfer = Transaction::create($input_data);


        if($sell_transfer['status'] == 'final')
        {
            $input_data['status'] = 'recieved';

        }else
        {
            $input_data['status'] =  $input_data['status'];

        }

         //Create Purchase Transfer at transfer location
         $input_data['type'] = 'purchase_transfer';
         $input_data['location_id'] = $request->input('transfer_location_id');
         $input_data['transfer_parent_id'] = $sell_transfer->id;


         $purchase_transfer = Transaction::create($input_data);

          //Sell Product from first location
          if (! empty($sell_lines)) {
            $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, $input_data['location_id'], false, null, [], false);
        }


         //Purchase product in second location
         if (! empty($purchase_lines)) {
            $purchase_transfer->purchase_lines()->createMany($purchase_lines);
        }


        if ($request->input('status') == 'final') {
            foreach ($products as $product) {
                if ($product['enable_stock']) {
                    $decrease_qty = $this->productUtil
                                ->num_uf($product['quantity']);
                    if (! empty($product['base_unit_multiplier'])) {
                        $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                    }

                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $sell_transfer->location_id,
                        $decrease_qty
                    );

                    $this->productUtil->updateProductQuantity(
                        $purchase_transfer->location_id,
                        $product['product_id'],
                        $product['variation_id'],
                        $decrease_qty,
                        0,
                        null,
                        false
                    );
                }
            }


             //Adjust stock over selling if found
             $this->productUtil->adjustStockOverSelling($purchase_transfer);


         }

         $this->transactionUtil->activityLog($sell_transfer, 'added');



$data = ['message' => 'Stock adjustment created successfully', 'data' => $sell_transfer];

return $data;

       
    }


    public function deleteStockTransfer($id)
    {
        if (! auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {

                $user = Auth::user();

                $businessId = $user->business_id;

              
                //Get sell transfer transaction
                $sell_transfer = Transaction::where('id', $id)
                                    ->where('type', 'sell_transfer')
                                    ->with(['sell_lines'])
                                    ->first();

                //Get purchase transfer transaction
                $purchase_transfer = Transaction::where('transfer_parent_id', $sell_transfer->id)
                                    ->where('type', 'purchase_transfer')
                                    ->with(['purchase_lines'])
                                    ->first();

                //Check if any transfer stock is deleted and delete purchase lines
                $purchase_lines = $purchase_transfer->purchase_lines;
                foreach ($purchase_lines as $purchase_line) {
                    if ($purchase_line->quantity_sold > 0) {
                        return ['success' => 0,
                            'msg' => __('lang_v1.stock_transfer_cannot_be_deleted'),
                        ];
                    }
                }

                event( new StockTransferCreatedOrModified($sell_transfer, 'deleted'));

                DB::beginTransaction();
                //Get purchase lines from transaction_sell_lines_purchase_lines and decrease quantity_sold
                $sell_lines = $sell_transfer->sell_lines;
                $deleted_sell_purchase_ids = [];
                $products = []; //variation_id as array

                foreach ($sell_lines as $sell_line) {
                    $purchase_sell_line = TransactionSellLinesPurchaseLines::where('sell_line_id', $sell_line->id)->first();

                    if (! empty($purchase_sell_line)) {
                        //Decrease quntity sold from purchase line
                        PurchaseLine::where('id', $purchase_sell_line->purchase_line_id)
                                ->decrement('quantity_sold', $sell_line->quantity);

                        $deleted_sell_purchase_ids[] = $purchase_sell_line->id;

                        //variation details
                        if (isset($products[$sell_line->variation_id])) {
                            $products[$sell_line->variation_id]['quantity'] += $sell_line->quantity;
                            $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
                        } else {
                            $products[$sell_line->variation_id]['quantity'] = $sell_line->quantity;
                            $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
                        }
                    }
                }

                //Update quantity available in both location
                if (! empty($products)) {
                    foreach ($products as $key => $value) {
                        //Decrease from location 2
                        $this->productUtil->decreaseProductQuantity(
                            $products[$key]['product_id'],
                            $key,
                            $purchase_transfer->location_id,
                            $products[$key]['quantity']
                        );

                        //Increase in location 1
                        $this->productUtil->updateProductQuantity(
                            $sell_transfer->location_id,
                            $products[$key]['product_id'],
                            $key,
                            $products[$key]['quantity']
                        );
                    }
                }

                //Delete sale line purchase line
                if (! empty($deleted_sell_purchase_ids)) {
                    TransactionSellLinesPurchaseLines::whereIn('id', $deleted_sell_purchase_ids)
                        ->delete();
                }

                //Delete both transactions
                $sell_transfer->delete();
                $purchase_transfer->delete();
                event( new StockTransferCreatedOrModified($sell_transfer, 'deleted'));
                $output = ['success' => 1,
                    'msg' => __('lang_v1.stock_transfer_delete_success'),
                ];
                DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function updateStockTransfer(Request $request, $id)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            $user = Auth::user();

            $business_id = $user->business_id;


            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\StockTransferController::class, 'index']));
            }

            $sell_transfer = Transaction::where('business_id', $business_id)
                    ->where('type', 'sell_transfer')
                    ->findOrFail($id);

            $sell_transfer_before = $sell_transfer->replicate();

            $purchase_transfer = Transaction::where('business_id',
                    $business_id)
                    ->where('transfer_parent_id', $id)
                    ->where('type', 'purchase_transfer')
                    ->with(['purchase_lines'])
                    ->first();

            $status = $request->input('status');

            DB::beginTransaction();

            $input_data = $request->only(['transaction_date', 'additional_notes', 'shipping_charges']);
            $status = $request->input('status');

             $final_total = 0;
            foreach($request->input('products') as $product)
            {
            $final_total += $product['unit_price'] * $product['quantity'];

            }

            $final_total += $request->shipping_charges;

            $input_data['final_total'] = $final_total;

            $input_data['transaction_date'] = \Carbon::now()->format('Y-m-d H:i:s');

            $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
// dd($input_data);

            $input_data['status'] = $status == 'completed' ? 'final' : $status;

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];
            $edited_purchase_lines = [];
            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null, ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];
                    if (isset($product['transaction_sell_lines_id'])) {
                        $sell_line_arr['transaction_sell_lines_id'] = $product['transaction_sell_lines_id'];
                    }

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;

                    $purchase_line = [];
                    //check if purchase_line for the variation exists else create new
                    foreach ($purchase_transfer->purchase_lines as $pl) {
                        if ($pl->variation_id == $purchase_line_arr['variation_id']) {
                            $pl->update($purchase_line_arr);
                            $edited_purchase_lines[] = $pl->id;
                            $purchase_line = $pl;
                            break;
                        }
                    }
                    if (empty($purchase_line)) {
                        $purchase_line = new PurchaseLine($purchase_line_arr);
                    }

                    $purchase_lines[] = $purchase_line;
                }
            }

            //Create Sell Transfer transaction
            $sell_transfer->update($input_data);
            $sell_transfer->save();

            event( new StockTransferCreatedOrModified($sell_transfer, 'updated'));

            //Create Purchase Transfer at transfer location
            $input_data['status'] = $status == 'completed' ? 'received' : $status;

            $purchase_transfer->update($input_data);
            $purchase_transfer->save();

            //Sell Product from first location
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, $sell_transfer->location_id, false, 'draft', [], false);
            }

            //Purchase product in second location
            if (! empty($purchase_lines)) {

                if (! empty($edited_purchase_lines)) {

                    PurchaseLine::where('transaction_id', $purchase_transfer->id)
                    ->whereNotIn('id', $edited_purchase_lines)
                    ->delete();
                }
                $purchase_transfer->purchase_lines()->saveMany($purchase_lines);
            }

            //And increase product stock at purchase location
            if ($status == 'completed') {
                foreach ($products as $product) {
                    
                    if ($product['enable_stock']) {
                        $decrease_qty = $this->productUtil
                                    ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_transfer->location_id,
                            $decrease_qty
                        );

                        $this->productUtil->updateProductQuantity(
                            $purchase_transfer->location_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $decrease_qty,
                            0,
                            null,
                            false
                        );
                    }
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_transfer);

                //Map sell lines with purchase lines
                $business = Business::where('id', $business_id)->get();

                $businessId = $business->first()->id;
                $accountingMethod = $business->first()->accounting_method;

                $business1 = ['id' => $businessId,
                    'accounting_method' => $accountingMethod,
                    'location_id' => $sell_transfer->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business1, $sell_transfer->sell_lines, 'purchase');
            }


            $this->transactionUtil->activityLog($sell_transfer, 'edited', $sell_transfer_before);

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_succesfully'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    
            
}
