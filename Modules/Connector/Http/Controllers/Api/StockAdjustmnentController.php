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

class StockAdjustmnentController extends ApiController
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

    // public function tet(Request $request)
    // {
    //     $user = Auth::user();

    //     $business_id = $user->business_id;

    //     $taxes = TaxRate::where('business_id', $business_id)
    //                     ->get();

    //     return CommonResource::collection($taxes);
    // }


    public function index()
    {
        $user = Auth::user();
        $businessId = $user->business_id;

        $business = Business::find($businessId);

        if ($business) {
            $businessName = $business->name;
        } 

        $stockAdjustments = Transaction::where([
            ['transactions.business_id', $businessId],
            ['transactions.type', 'stock_adjustment'],
        ])
        ->join('stock_adjustment_lines as sdl', 'transactions.id', '=', 'sdl.transaction_id')
        ->join('business_locations as bl', 'transactions.location_id', '=', 'bl.id')
        ->join('users as u', 'transactions.created_by', '=', 'u.id')
        ->join('products as p', 'sdl.product_id', '=', 'p.id')
        ->join('product_variations as pv', 'sdl.variation_id', '=', 'pv.id')
        ->join('variations as v', 'p.id', '=', 'v.product_id')
        ->join('activity_log as al', 'transactions.id', '=', 'al.subject_id')
        ->select(
            'transactions.id as transaction_id',
            'transactions.business_id',
            'u.first_name as created_by',
            DB::raw("CONCAT(bl.name, ' ', bl.landmark , ' ', bl.city, ',', ' ', bl.state,',', bl.country) as location_info"),
            'transactions.adjustment_type',
            'transactions.transaction_date',
            'transactions.total_amount_recovered',
            'transactions.transaction_date',
            'transactions.ref_no',
            'transactions.additional_notes',
            'p.name as product_name',
            'pv.name as variation_name',
            'sdl.quantity',
            'v.default_purchase_price',
            'al.description',
            'al.created_at',
            
        )
        ->get()
        ->groupBy('transaction_id');


        $result = [];

        foreach ($stockAdjustments as $groupedData) {


            $final_total = collect($groupedData)->sum(function ($item) {
                return $item->quantity * $item->default_purchase_price;
            });


            $products = collect($groupedData)->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'variation_name' => $item->variation_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->default_purchase_price,
                    'subtotal' => $item->quantity * $item->default_purchase_price,
                ];
            });

            $activities = [

                    'Date' => $groupedData->first()->created_at->format('Y-m-d H:i'),
                    'Description' => $groupedData->first()->description,
                    'By' => $groupedData->first()->created_by,

            ];


            $result[] = [
                'Business' => $businessName,
                'created_by' => $groupedData->first()->created_by,
                'location_name' => $groupedData->first()->location_info,
                'final_total' => $final_total,
                'Date' => $groupedData->first()->transaction_date,
                'recovered_amount' => $groupedData->first()->total_amount_recovered,
                'type' => 'stock_adjustment',
                'adjustment_type' => $groupedData->first()->adjustment_type,
                'ref_no' => $groupedData->first()->ref_no,
                'reason' => $groupedData->first()->additional_notes,
                'products' => $products->toArray(),
                'activities' => $activities,
            ];
        }

        return [
            'data' => $result
        ];

    }


    public function createStockAdjustment(Request $request)
    {
        $validatedData = $request->validate([
            'location_id' => 'required|numeric',
            'adjustment_type' => 'required|string|in:normal,abnormal',
            'total_amount_recovered' => 'required|numeric',
           
        ]);


        // Get the authenticated user and business_id
        $user = Auth::user();

        $businessId = $user->business_id;


         //Update reference count
         $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment', $businessId);
         $Prefix = 'SA';
         $ref_no = $Prefix . $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

         $final_total = 0;
         foreach($request->input('products') as $product)
         {
            $final_total += $product['unit_price'] * $product['quantity'];

         }

        
        // Create a new stock adjustment transaction
        $transaction = [
            'business_id' => $businessId,
            'created_by' => $user->id,
            'location_id' => $validatedData["location_id"],
            'transaction_date' => \Carbon::now()->format('Y-m-d H:i:s'),
            'adjustment_type' => $validatedData["adjustment_type"],
            'final_total' => $final_total,
            'total_amount_recovered' => $validatedData["total_amount_recovered"],
            'ref_no' => $ref_no,
            'additional_notes' => $request->input('additional_notes'),
            'type' => 'stock_adjustment',
        ];

        // dd($transaction);


        // Add stock adjustment lines
        $products = $request->input('products');

            if (! empty($products)) {
                $product_data = [];

                foreach ($products as $product) {
                    $adjustment_line = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'unit_price' => $this->productUtil->num_uf($product['unit_price']),
                    ];
                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to stock adjustment line
                        $adjustment_line['lot_no_line_id'] = $product['lot_no_line_id'];
                    }
                    $product_data[] = $adjustment_line;

                    //Decrease available quantity
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $request->input('location_id'),
                        $this->productUtil->num_uf($product['quantity'])
                    );
                }

                $stock_adjustment = Transaction::create($transaction);


                $stock_adjustment->stock_adjustment_lines()->createMany($product_data);

                $this->transactionUtil->activityLog($stock_adjustment, 'added', null, [], false);


            }

        return response()->json(['message' => 'Stock adjustment created successfully']);
    }


    public function deleteStockAdjustment($id)
    {

        $stock_adjustment = Transaction::where('id', $id)
                                    ->where('type', 'stock_adjustment')
                                    ->with(['stock_adjustment_lines'])
                                    ->first();



                //Add deleted product quantity to available quantity
                $stock_adjustment_lines = $stock_adjustment->stock_adjustment_lines;

                if (! empty($stock_adjustment_lines)) {
                    $line_ids = [];

                    foreach ($stock_adjustment_lines as $stock_adjustment_line) {

                        // dd($stock_adjustment_line->quantity);

                        $naveed = $this->productUtil->updateProductQuantity(
                            $stock_adjustment->location_id,
                            $stock_adjustment_line->product_id,
                            $stock_adjustment_line->variation_id,
                            $this->productUtil->num_f($stock_adjustment_line->quantity, false,  $stock_adjustment),

                        );

                        $line_ids[] = $stock_adjustment_line->id;


                    }

                    $this->transactionUtil->mapPurchaseQuantityForDeleteStockAdjustment($line_ids);
                }
                $stock_adjustment->delete();

                // event( new StockAdjustmentCreatedOrModified($stock_adjustment, 'deleted'));

        return response()->json(['message' => 'Stock adjustment deleted successfully']);

    }
    


            
}
