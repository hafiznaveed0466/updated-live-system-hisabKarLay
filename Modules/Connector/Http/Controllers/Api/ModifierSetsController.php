<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Product;

use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\CommonResource;
use Illuminate\Support\Facades\DB;

class ModifierSetsController extends ApiController
{

    protected $productUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $business_id = $user->business_id;
        $modifer_set = Product::where('business_id', $business_id)
        ->where('type', 'modifier')
         ->with(['variations', 'modifier_products' ,'modifier_products.product_variations'])
        ->get();
  
        return CommonResource::collection($modifer_set);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
        public function storeModifier(Request $request)
    {
        // dd("sda");
        // try {
            // if (!auth()->user()->can('product.create')) {
            //     abort(403, 'Unauthorized action.');
            // }

            $input = $request->all();
            $user = Auth::user();
            $business_id = $user->business_id;
            $modifer_set_data = [
                'name' => $input['name'],
                'type' => 'modifier',
                'sku' => ' ',
                'tax_type' => 'inclusive',
                'business_id' => $business_id,
                'created_by' => $user->id
            ];

            // dd($modifer_set_data);
            // DB::beginTransaction();
            $modifer_set = Product::create($modifer_set_data);

            $sku = $this->productUtil->generateProductSku($modifer_set->id);
            $modifer_set->sku = $sku;
            $modifer_set->save();

            $modifers = [];
            foreach ($input['modifier_name'] as $key => $value) {
                $modifers[] = [
                    'value' => $value,
                    'default_purchase_price' => $input['modifier_price'][$key],
                    'dpp_inc_tax' => $input['modifier_price'][$key],
                    'profit_percent' => 0,
                    'default_sell_price' => $input['modifier_price'][$key],
                    'sell_price_inc_tax' => $input['modifier_price'][$key],
                ];
            }

            // dd($modifers);
            $modifiers_data = [];
            $modifiers_data[] = [
                'name' => 'DUMMY',
                'variations' => $modifers
            ];
            $this->productUtil->createVariableProductVariations($modifer_set->id, $modifiers_data);

            // DB::commit();

            $output = ['success' => 1, 'msg' => __("modifer added success")];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //     $output = ['success' => 0, 'msg' => __("messages.something_went_wrong")];
        // }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productModifier($modifier_set_id, Request $request)
    {
        // dd($modifier_set_id);
        // try {
        //     DB::beginTransaction();

            $input = $request->all();
            $user = Auth::user();
            $business_id = $user->business_id;

            $modifer_set = Product::where('business_id', $business_id)
                    ->where('id', $modifier_set_id)
                    ->where('type', 'modifier')
                    ->first();

            $products = [];
            if (!empty($input['products'])) {
                $products = $input['products'];
            }
            $modifer_set->modifier_products()->sync($products);
// dd($modifer_set);
            
            // DB::commit();

            $output = ['success' => 1, 'msg' => __("Product Modifirt Success")];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //     $output = ['success' => 0,
        //         'msg' => __("messages.something_went_wrong")];
        // }

        return $output;
    }
       public function update($id, Request $request)
    {
       

        // try {
            DB::beginTransaction();

            $input = $request->all();
              $user = Auth::user();
            $business_id = $user->business_id;

            $modifer_set = Product::where('business_id', $business_id)
                    ->where('id', $id)
                    ->where('type', 'modifier')
                    ->first();
            $modifer_set->update(['name' => $input['name']]);

            //Get the dummy product variation
            $product_variation = $modifer_set->product_variations()->first();

            $modifiers_data[$product_variation->id]['name'] = $product_variation->name;

            $variations_edit = [];
            $variations = [];

            //Set existing variations
            if (!empty($input['modifier_name_edit'])) {
                $modifier_name_edit = $input['modifier_name_edit'];
                $modifier_price_edit = $input['modifier_price_edit'];

                foreach ($modifier_name_edit as $key => $name) {
                    if (isset($modifier_price_edit[$key])) {
                        $variations_edit[$key]['value'] = $name;
                        $variations_edit[$key]['default_purchase_price'] = $modifier_price_edit[$key];
                        $variations_edit[$key]['dpp_inc_tax'] = $modifier_price_edit[$key];
                        $variations_edit[$key]['default_sell_price'] = $modifier_price_edit[$key];
                        $variations_edit[$key]['sell_price_inc_tax'] = $modifier_price_edit[$key];
                        $variations_edit[$key]['profit_percent'] = 0;
                    }
                }
            }
            //Set new variations
            if (!empty($input['modifier_name'])) {
                foreach ($input['modifier_name'] as $key => $value) {
                    $variations[] = [
                        'value' => $value,
                        'default_purchase_price' => $input['modifier_price'][$key],
                        'dpp_inc_tax' => $input['modifier_price'][$key],
                        'profit_percent' => 0,
                        'default_sell_price' => $input['modifier_price'][$key],
                        'sell_price_inc_tax' => $input['modifier_price'][$key],
                    ];
                }
            }

            //Update variations
            $modifiers_data[$product_variation->id]['variations_edit'] = $variations_edit;
            $modifiers_data[$product_variation->id]['variations'] = $variations;
            $this->productUtil->updateVariableProductVariations($modifer_set->id, $modifiers_data);

            DB::commit();

            $output = ['success' => 1, 'msg' => __("lang_v1.updated_success")];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //     $output = ['success' => 0, 'msg' => __("messages.something_went_wrong")];
        // }

        return $output;
    }

 public function show($id)
    {
       
        $user = Auth::user();
        $business_id = $user->business_id;
       
        $modifer_set = Product::where('business_id', $business_id)
         
        ->where('type', 'modifier')
        ->with(['variations', 'modifier_products'])
        ->find($id);
//   dd($modifer_set);
        return [ "data" =>$modifer_set];
    }

 public function delete($id, Request $request)
    {
        // if (!auth()->user()->can('product.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // try {
            DB::beginTransaction();
            $user = Auth::user();
        $business_id = $user->business_id;

            Product::where('business_id', $business_id)
                ->where('type', 'modifier')
                ->where('id', $id)
                ->delete();

            DB::commit();

            $output = ['success' => 1, 'msg' => __("lang_v1.deleted_success")];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //     $output = ['success' => 0, 'msg' => __("messages.something_went_wrong")];
        // }

        return $output;
    }
}
