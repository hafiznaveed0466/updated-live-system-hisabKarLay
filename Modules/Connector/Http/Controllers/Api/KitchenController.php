<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Transaction;
use App\TransactionSellLine;
use App\Utils\RestaurantUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;


class KitchenController extends ApiController
{

     /**
     * All Utils instance.
     */
    protected $commonUtil;

    protected $restUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @param  RestaurantUtil  $restUtil
     * @return void
     */
    public function __construct(Util $commonUtil, RestaurantUtil $restUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
       
        $business_id = Auth::user()->business_id;
        $orders = $this->restUtil->getAllOrders($business_id, ['line_order_status' => 'received']);

        return CommonResource::collection($orders);
       
    }

    /**
     * Marks an order as cooked
     *
     * @return json $output
     */
    public function markAsCooked($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $sl = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                        ->where('t.business_id', $business_id)
                        ->where('transaction_id', $id)
                        ->where(function($q) {
                            $q->whereNull('res_line_order_status')
                                ->orWhere('res_line_order_status', 'received');
                        })->update(['res_line_order_status' => 'cooked','update_cooked_status' =>'1']);

                    $this->servedtime($business_id , $id);

            $output = ['success' => 1,
                            'msg' => trans("restaurant.order_successfully_marked_cooked")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        return $output;
    }


    public function servedtime($business_id , $id){

       $t =  Transaction::where("id", $id) ->where('business_id', $business_id)->update([
            "order_status_cooked"=>"Cooked",
        ]);

    }
    
            
}
