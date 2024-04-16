<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use Illuminate\Support\Facades\Auth;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class IdController extends Controller
{
    

    public function kot($id)
    {
       
        //    $sale=Transaction::find($id);
        // dd($id);     
        $data = DB::table('transaction_sell_lines')
            ->join('transactions', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_sell_lines.product_id')
            ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
            ->join('variations', 'variations.id', '=', 'transaction_sell_lines.variation_id')
            ->where('transaction_sell_lines.inovice_status','=','0')
            ->select('transaction_sell_lines.*','transaction_sell_lines.product_id',
            // \DB::raw("MAX(transaction_sell_lines.id) AS id"), 
            // 'transaction_sell_lines.created_at', 
            'transactions.additional_notes', 
            'variations.name as var_name', 'transactions.invoice_no', 'transactions.id',
             DB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
            'products.name', 'contacts.name as cname')->where('transactions.id', '=', $id)
            ->get();
// dd($data);
            //echo '<pre>';print_r(($data)); echo '</pre>'; die;
    
           
        return Response($data);
    }

    public function index()
    {

      
        $ok = Transaction::latest('id')->first();
        $id = $ok->id;
        $data = DB::table('transaction_sell_lines')
            ->join('products', 'products.id', '=', 'transaction_sell_lines.product_id')
           
            ->join('transactions', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('variations', 'variations.id', '=', 'transaction_sell_lines.variation_id')
            ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
            ->leftjoin('res_tables', 'res_tables.id', '=', 'transactions.res_table_id')
            ->leftjoin('types_of_services', 'types_of_services.id', '=', 'transactions.types_of_service_id')

            ->join('business_locations', 'business_locations.id', '=', 'transactions.business_id')
            // dd($data);
            ->select(
                'transaction_sell_lines.quantity',
                'products.name as pro_name',
                'variations.name as var_name',
                'res_tables.name as tabel_name',
                'types_of_services.name as sname',
                'transactions.invoice_no',
                 'transactions.invoice_no',
                 'transactions.transaction_date',
                //   DB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
                'transactions.additional_notes', 
                'contacts.name',
                'transaction_sell_lines.created_at'
            )
            ->where('transaction_sell_lines.transaction_id', '=', $id)
           
            ->get();
        return response($data);
        // $ok=TransactionSellLinesPurchaseLines::latest('id')->first();
        // $id=$ok->sell_line_id;

        // $tran=TransactionSellLine::find($id);

        // $aa=$tran->transaction_id;




        // $data=DB::table('transaction_sell_lines') 
        // ->join('transactions','transactions.id','=','transaction_sell_lines.transaction_id')
        // ->join('products','products.id','=','transaction_sell_lines.product_id')
        // ->select('transaction_sell_lines.quantity','transactions.invoice_no','transactions.id','products.name as pro_name')->where('transaction_sell_lines.transaction_id','=',$aa)->get();
        //         return response($data);

    }

 



    public function bill($id)
    {
        $ab = null;
        $data = DB::table('transaction_sell_lines')

            ->join('transactions', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_sell_lines.product_id')
            ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
            ->join('business_locations', 'business_locations.id', '=', 'transactions.location_id')
            ->select('transaction_sell_lines.quantity', 'transactions.invoice_no', 'transactions.id', 
            DB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
            'products.name', 'contacts.name as cname', 'business_locations.mobile', 'business_locations.country', 
            'business_locations.city', 'business_locations.state', 'business_locations.zip_code', 
            'business_locations.name as bname', 'business_locations.landmark', 
            'transaction_sell_lines.created_at', 'transactions.final_total', 'transaction_sell_lines.unit_price','transactions.packing_charge','transactions.packing_charge_type')
            ->where('transactions.id', '=', $id)->where('transaction_sell_lines.parent_sell_line_id', '=', $ab)


            ->get();

        // return view('kot')->with('data',$data);
        return Response($data);
    }
    public function kot_bill_ids()
    {
        $user = Auth::user();
        $business_id = $user->business_id;
        $ok = Transaction::latest('id')->first();
        $id = $ok['id'];
        $data = DB::table('transaction_sell_lines')
            ->join('transactions', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_sell_lines.product_id')
            ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
            ->join('business_locations', 'business_locations.id', '=', 'transactions.business_id')
          
            ->select('transaction_sell_lines.quantity', 'transactions.invoice_no', 'transactions.id',
             DB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
            'products.name', 'contacts.name as cname', 'business_locations.mobile', 'business_locations.country', 'business_locations.city', 'business_locations.state', 'business_locations.zip_code', 'business_locations.name as bname', 'business_locations.landmark', 'transaction_sell_lines.created_at', 'transactions.final_total', 'transaction_sell_lines.unit_price')
            ->where('transaction_sell_lines.inovice_status','=','0')
            ->where('transactions.id', '=', $id)
            ->where('transactions.business_id', '=', $business_id)

            ->get();

        // return view('kot')->with('data',$data);
        return Response($data);
    }
    public function kot_bill_pos()
    {
        $user = Auth::user();
        $business_id = $user->business_id;
        $ok = Transaction::latest('id')->first();
        $id = $ok['id'];
        $data = DB::table('transaction_sell_lines')
            ->join('transactions', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_sell_lines.product_id')
            ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
            ->join('business_locations', 'business_locations.id', '=', 'transactions.business_id')
            ->leftjoin('res_tables', 'res_tables.id', '=', 'transactions.res_table_id')
            ->leftjoin('types_of_services', 'types_of_services.id', '=', 'transactions.types_of_service_id')

            ->select(
                'transaction_sell_lines.quantity',
                'transactions.invoice_no',
                'transactions.id',
              DB::raw("DATE_FORMAT(transactions.transaction_date, '%d-%m-%y %h:%i:%s %p') as transaction_date"),
                'products.name',
                'contacts.name as cname',
                'business_locations.mobile',
                'business_locations.country',
                'res_tables.name as tabel_name',
                'types_of_services.name as sname',
                'business_locations.city',
                'business_locations.state',
                'business_locations.zip_code',
                'business_locations.name as bname',
                'business_locations.landmark',
                'transaction_sell_lines.created_at',
                'transactions.final_total',
                'transaction_sell_lines.unit_price'
            )
            ->where('transaction_sell_lines.transaction_id', '=', $id)
            ->where('transactions.business_id', '=', $business_id)


            ->get();

        // return view('kot')->with('data',$data);
        return Response($data);
    }
}
