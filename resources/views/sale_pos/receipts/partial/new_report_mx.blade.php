
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- <link rel="stylesheet" href="style.css"> -->
        {{-- <title>Receipt-{{$receipt_details->invoice_no}}</title> --}}
        <style>
            .bordered-element {
                border: 1px solid #000000 !important;
                color: #000 !important;
              }
            
              .tableborder th , .tableborder td{
              border: 1px solid black !important;
              border-radius: 10px !important;
            }

             @media print {
    .bordered-element_one {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
    }

    .bordered-element_one .col-md-4 {
        flex: 0 0 auto;
        width: 33.33%;
    }

    .bordered-element_one .text-center {
        text-align: center;
    }

    .bordered-element_one .text-right {
        /* text-align: right; */
    }

    /* Adjust this to your actual image size */
    .bordered-element_one img {
        max-width: 100%;
        height: auto;
    }

   .bordered-element_two {
        display: flex;
        flex-wrap: wrap;
    }

    .bordered-element_two .col-md-4 {
        flex: 0 0 auto;
        width: 33.33%;
    }

    .bordered-element_two .mt-5 {
        margin-top: 1.25rem; /* Adjust as needed */
    }

    .bordered-element_two .text-center {
        text-align: center;
    }

    .bordered-element_two .mt-2 {
        margin-top: 0.625rem; /* Adjust as needed */
    }

    
   
}
            
            </style>
    </head>
    <body>
    
<div class="row bordered-element bordered-element_one">
    <div class="col-md-4">
        @if(!empty($receipt_details->display_name))
           <h3> {{$receipt_details->display_name}} </h3>
        @endif
        <p><b>Branch:</b>
            <small> {{$receipt_details->address ?? ""}}</small>
        </p>
        <p><b>Landline#</b>
            <small>  {{__("042 111 441 441")}} </small>
        </p>
        <p><b>Mobile#</b>
            <small> {{__("0305 1114414")}}</small>
        </p>
    </div>

    <div class="col-md-4 text-center">
        <h3>Sale Tax Invoice</h3> 
    </div>

    <div class="col-md-4 text-right mt-5">
        <!-- Logo  -->
        @if(!empty($receipt_details->logo))
            <img src="{{$receipt_details->logo}}" class="img img-responsive">
        @endif

        @if ($receipt_details->show_barcode || $receipt_details->show_qr_code)
        <div class="text-center">
           
                @if ($receipt_details->show_barcode)
                    {{-- Barcode --}}
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                @endif

                @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
                @endif

                {{-- Invoice Date --}}
                <p style="color: #000;" class = "text-center mt-5"><b>Invoice Date:</b> {{date('d-m-Y', strtotime($receipt_details->invoice_date))}}</p>
    </div>
       
        @endif
    </div>

</div>



        
            
                         <div class="row bordered-element_two">
                        <div class="col-md-5 mt-5">
                            {{-- Invoice no --}}
                            <p><b>Invoice#:</b> {{$receipt_details->invoice_no}}</p>
                        </div>

                        <div class="col-md-7 mt-5">
                            {{-- Customer details --}}
                            <p><b>{{$receipt_details->customer_label}}:</b>{{$receipt_details->customer_name}}</p>
                        </div>

                        <div class="col-md-12 mt-2">
                            {{-- Sale Order --}}
                            <p><b>{{__('Sale Order')}}#:</b> {{$receipt_details->sale_orders_invoice_no ?? $receipt_details->invoice_no}}</p>
                        </div>
                           
                         <div class="col-md-4 mt-2">
                            
                            <p><b>{{__('D.O')}}#:</b> {{$receipt_details->do_number}}</p>
                        </div>
                        

                    </div>
           

        <div class="row bordered-element">

                    <div class="col-xs-12">
                        <br/>
                        @php
                            $p_width = 45;
                        @endphp
                        @if(!empty($receipt_details->item_discount_label))
                            @php
                                $p_width -= 10;
                            @endphp
                        @endif
                        @if(!empty($receipt_details->discounted_unit_price_label))
                            @php
                                $p_width -= 10;
                            @endphp
                        @endif
                        <table class="table table-responsive table-slim tableborder">
                            <thead>
                                <tr>
                                    <th class="text-center" width="2%">{{__('#')}}</th> {{-- # --}}
                                    <th class="text-center" width="5%">{{$receipt_details->table_product_label}}</th> {{-- product--}}
                                    <th class="text-center" width="5%">{{__('Barcode/IMEI')}}</th> {{-- barcode--}}
                                    <th class="text-center" width="2%">{{$receipt_details->table_qty_label}}</th> {{-- Quantity	--}}
                                    <th class="text-center" width="5%">{{$receipt_details->table_unit_price_label}}</th> {{--Rate--}}
                                    <th class="text-center" width="5%">{{__("Discount")}}</th>
                                    <th class="text-center" width="5%">{{__("Net Unit Rate")}}</th> {{-- NET Rate--}}
                                    <th class="text-center" width="5%">{{__("S.TAX%")}}</th>
                                    <th class="text-center" width="5%">{{__("S.TAX")}}</th>
                                    <th class="text-center" width="5%">{{$receipt_details->table_subtotal_label}}</th> {{--Amount--}}
                                </tr>
                            </thead>
                         
                            <tbody>

                              
                               
                               
                           
                               @php
                                $serialNumber = 1; 
                                       
                               $string = $receipt_details->discount_label;
                               
                               if (preg_match('/\((\d+\.\d+)%\)/', $string, $matches)) {
                                   $percentage = $matches[1];
                                   $percentage;
                               }      

                            //    Discount <small>(₨ 0.00%)</small> 

                            // dd($receipt_details->customer_name);
                               // tax formate 
                               $taxNumber = $receipt_details->tax_info1;
                              
                            //    if ($taxNumber > 0) 
                            //    {
                            //         $unitPrice= str_replace(',', '', $receipt_details->lines[0]['unit_price_before_discount']);

                            //         // Convert the string to a float
                            //         $unitPriceNum = (float)$unitPrice;
                            //         $unit_price = $unitPriceNum;
                            //         $unit = (int)$unit_price;
                            //         $tn = (int)$taxNumber;

                            //         $get_amount = ($tn/100) * $unit;
                            //         $tax_amount = $get_amount;

                            //    }
                
                           
                                   // subtotal formate
                               $subtoalNumber = $receipt_details->subtotal;
                               if (preg_match('/[\d,]+\.\d{2}/', $subtoalNumber, $matches)) {
                                   $subtotal_amount = $matches[0];
                               }
                                   // Total amount formate
                
                               if (isset($receipt_details->total_paid)) {
                                $totalPad = $receipt_details->total_paid;
                               if (preg_match('/[\d,]+\.\d{2}/', $totalPad, $matches)) {
                                   $total_amount = $matches[0];
                               }
                           }else {
                                   $total_amount = 0;
                               }

                            //code to find sales tax
//                             $salesTaxValue = preg_replace("/[^0-9.]/", "", $tax_amount);
//                             $cleaned_subtotal = preg_replace("/[^0-9.]/", "", $receipt_details->subtotal_exc_tax);
//                             $cleaned_discount = preg_replace("/[^0-9.]/", "", $receipt_details->discount);  
// // Convert to float
// $subtotal = floatval($cleaned_subtotal);
// $discount = floatval($cleaned_discount);
// $salesTaxValue=floatval($salesTaxValue);
//                                $salesTaxPercentage =  ($salesTaxValue / ($subtotal-$discount)) * 100 ;
                           
                               
                @endphp
                            @php
                            $line = $receipt_details->lines;
                            $serialNumber = 1; // Reset serial number for each line
                            $productRows = []; // Associative array to accumulate product rows
                        @endphp

                    
                            @foreach ($line as $key => $value)
                                @php
                                    $productName = $value['name'];
                                    $imeiNumber = $value['imei_number'] ?? '';
                                    $quantity = $value['quantity'];
                                    $tax_value = $value['tax'];
                                    // dd($tax_value);
                                    $unitPrice = $value['unit_price_before_discount'];
                                    if (!empty($value['line_discount_percent'])){
                                        $discount = $value['line_discount_percent'];

                                        $discount_value = (($unitPrice+$tax_amount)*$discount)/100;

                                    }
                                    else{
                                        $discount = $value['total_line_discount'];
                                        $discount_value = $value['total_line_discount'];
                                    }

                                    if($tax_value != "0.00"){
                                        $unitPrice= str_replace(',', '', $receipt_details->lines[0]['unit_price_before_discount']);

                                        // Convert the string to a float
                                        $unitPriceNum = (float)$unitPrice;
                                        $unit_price = $unitPriceNum;
                                        $unit = (int)$unit_price;
                                        $tn = $receipt_details->tax_info1;

                                        $get_amount = ($tn/100) * $unit;
                                        $tax_amount = $get_amount;
                                    }
                                    else{

                                        $tax_amount = 0;
                                    }

                                @endphp

                           
                    
                            @if (!isset($productRows[$productName]))
                                @php
                                    $productRows[$productName] = [
                                        'imei_numbers' => [$imeiNumber],
                                        'quantity' => $quantity,
                                        'unit_price' => $unitPrice,
                                        'line_discount' => $discount,
                                        'line_discount_value' => $discount_value,
                                        'tax' => $tax_value,
                                    ];
                                @endphp
                            @else
                                @php
                                    $productRows[$productName]['imei_numbers'][] = $imeiNumber;
                                    $productRows[$productName]['quantity'] += $quantity;
                                    $productRows[$productName]['line_discount'] = $discount;
                                    $productRows[$productName]['line_discount_value'] = $discount_value;
                                    $productRows[$productName]['tax'] = $tax_value;

                                @endphp
                            @endif
                        @endforeach
                        
                        {{-- Display the product rows --}}
                        @foreach ($productRows as $productName => $productData)
                        
                        {{-- @dd($productData['imei_numbers']); --}}
                            <tr>
                                
                                       
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $serialNumber++ }}</td>
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $productName }}</td>
                                <td class="text-center">{{ implode(', ', $productData['imei_numbers']) }}</td>
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000000;">{{ $productData['quantity'] }}</td>
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $productData['unit_price'] }}</td>
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $productData['line_discount']  }}</td>
                                
                                @php
                                // Remove commas from the string
                                $unitPriceString = str_replace(',', '', $productData['unit_price']);

                                // Convert the string to a float
                                $unitPriceNumeric = (float)$unitPriceString;
                                $lineDiscountNumeric = (float)($productData['line_discount_value']);

                                $net_rate = $unitPriceNumeric - $lineDiscountNumeric;
                            
                                @endphp

                                 {{-- @php
                                    dd($productData['tax'])
                                @endphp --}}
                               
                               
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $net_rate}}</td>
                                {{-- @if(!empty($receipt_details->tax_label1)  && !empty($receipt_details->tax_info1)) --}}
                                @if(!empty($productData['tax']))
                                   <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{$receipt_details->tax_info1}}</td> 
                                @else
                                   <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;"> </td> 

                                @endif
                               
                                
                                        <!-- // S.Tax -->
                                        @php
                                            $sale_tax1 = $tax_amount;
                                        @endphp
                                        
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">  {{$sale_tax1 }}</td>
                                @php
                                    // Remove commas from the string
                                    // $unitPriceString = str_replace(',', '', $productData['unit_price']);

                                    // // Convert the string to a float
                                    // $unitPriceNumeric = (float)$unitPriceString;
                                    // $subtotal = ($unitPriceNumeric + $productData['line_discount_value']) * $productData['quantity'] + $sale_tax1 ;
                                    $subtotal = $receipt_details->subtotal;
                                    $sub_total = trim(str_replace('₨', '', $subtotal));

                                @endphp
                                <td style="vertical-align: middle; text-align: center; font-size: 17px; color: #000;">{{ $sub_total }}</td>
                            </tr>
                        @endforeach

                       

                        
                    </tbody>
                    
                    <tfoot>
                        <tr>
                            <th style="font-size:17px; color: #000;" colspan="3">Total</th>
                            <th style="text-align:center; font-size:17px; color: #000;">{{ array_sum(array_column($line, 'quantity')) }}</th>
                            <th style="font-size:17px; color: #000;"></th>
                            <th style="font-size:17px; color: #000;"></th>
                            <th style="font-size:17px; color: #000;"></th>
                            <th style="font-size:17px; color: #000;"></th>
                        @php
                            $sale_tax2 = $tax_amount * $productData['quantity'];
                        @endphp
                            <th style="text-align:center;font-size:17px; color: #000;">{{ $sale_tax2 }}</th>
                            <th style="text-align:center;font-size:17px; color: #000;">{{ $sub_total }}</th>
                        </tr>
                    </tfoot>
                   
                        </table>
                    </div>
 
                    {{-- Remarks --}}
                    <div class="col-xs-6">
                        <p><b>Remarks:</b> <br/>{{$receipt_details->additional_notes}}</p>
                    </div>
                    
                    <div class="col-xs-6">
                            <table class="table table-slim">
                                <thead>

                                    @php
                                        $net_num= str_replace(',', '', $net_rate);

                                        // Convert the string to a float
                                        $netNumeric = (float)$net_num;

                                        // dd($netNumeric, $productRows[$productName]['quantity']);
                                        $total_excl_tax = $netNumeric * $productRows[$productName]['quantity'];

                                    @endphp
                                    <tr>
                                        <th><u>Amount Exclusive Of Tax</b></th> 
                                            {{-- <td class="text-right">amountExclusiveOfTax</td> --}}
                                            <td  class="text-right">{{ $total_excl_tax}}</td>
                                            {{-- <td  class="text-right">{{ $receipt_details->total_excl_tax}}</td> --}}
                                        
                                    </tr>
                                    <tr>
                                          
                                        <th><u>{{$receipt_details->line_discount_label}}</th>
                                            @php
                                            $lineDiscountNumeric1 = (float)($productData['line_discount_value']);
                                            $total_discount = $lineDiscountNumeric1 * $productData['quantity'];
                                        @endphp
                                            <td class="text-right">{{$total_discount}}</td>
                                            
                                        </tr>
                                    <tr>
                                    
                                        <th><u>Sale Tax 18%</u></th>
                                        <td class="text-right">
                                            {{($tax_amount * $productRows[$productName]['quantity']) }}
                                        </td>
                                            
                                    </tr>

                                <tr>

                                        <th><u>Amount Inclusive Of Tax</u></th>
                                        @php
                                        @endphp
                                        <td  class="text-right">{{$sub_total }}</td>
                                    </tr>
                                </thead>
                                

                                
                            </table>
                            {{-- @php
                                dd('lkjhgvc')
                            @endphp --}}
                            @php
                    @endphp
                    
                </div>

                {{-- Prepared by --}}
     
                <div class="col-xs-6">
                <p style="font-size: 15px; color: #000;">Prepared by</p>
                <hr style="text-align: start; width: 40%; margin: 0px;">
                <p style="font-size: 17px; color: #000;">{{auth()->user()->first_name ."".auth()->user()->last_name}}</p>
                </div>
                {{-- Recived by --}}
                <div class="col-xs-6">
                    <p  style="font-size: 15px; color: #000; text-align: center">Received by</p>
                    <hr style="width: 40%;" />
                </div>

                {{-- Generated on 23/02/2023 04:20 PM | User ID: 2000 --}}

                <div class="col-xs-12" style="margin-top: 10%;">
                    <p style="text-align:end; color: #000; font-size: 15px;">Generated on {{$receipt_details->invoice_date}} | User ID: {{$receipt_details->client_id}}</p> 
                </div>

                <div class="col-xs-6">
                    <p style="font-size: 12px;"><b>UAN#:</b>042 - 111 441 441 | <b>Mobile#:</b>0305 1114414</p>
                    <p style="font-size: 12px;"><b>Accounts#:</b>+923217901297</p>
                    <strong></strong><p></p>  <strong></strong><p></p>
                </div>
                <div class="col-xs-6 mb-5">
                    <p style="font-size: 12px; text-align: end;"><b>Head Office Address:</b>Muqaddas Building, 203 D-II, Gulshan e Ravi, Lahore.</p>
                    <p style="font-size: 12px; text-align: end;"><b>Email:</b>{{auth()->user()->email}}</p>

                </div>

            </div>

          
    
</body>
</html>