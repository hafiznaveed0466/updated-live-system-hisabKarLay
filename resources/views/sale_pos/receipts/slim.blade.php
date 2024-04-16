<!-- business information here -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <title>Receipt-{{ $receipt_details->invoice_no }}</title>
</head>

<body>

   {{-- @dd($receipt_details); --}}
    @if ($receipt_details->is_flex_enable)


        <div class="ticket">


            @if (!empty($receipt_details->logo))
                <div class="text-box centered">
                    <img style="max-height: 100px; width: auto;" src="{{ $receipt_details->logo }}" alt="Logo">
                </div>
            @endif
            <div class="text-box">
                <!-- Logo -->
                <p class="centered">
                    <!-- Header text -->
                    @if (!empty($receipt_details->header_text))
                        <span class="headings">{!! $receipt_details->header_text !!}</span>
                        <br />
                    @endif

                    <!-- business information here -->
                    @if (!empty($receipt_details->display_name))
                        <span class="headings">
                            {{ $receipt_details->display_name }}
                        </span>
                        <br />
                    @endif

                    @if (!empty($receipt_details->address))
                        {!! $receipt_details->address !!}
                        <br />
                    @endif

                    @if (!empty($receipt_details->contact))
                        {!! $receipt_details->contact !!}
                    @endif
                    @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                        ,
                    @endif
                    @if (!empty($receipt_details->website))
                        {{ $receipt_details->website }}
                    @endif
                    @if (!empty($receipt_details->location_custom_fields))
                        <br>{{ $receipt_details->location_custom_fields }}
                    @endif

                    @if (!empty($receipt_details->sub_heading_line1))
                        {{ $receipt_details->sub_heading_line1 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line2))
                        {{ $receipt_details->sub_heading_line2 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line3))
                        {{ $receipt_details->sub_heading_line3 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line4))
                        {{ $receipt_details->sub_heading_line4 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line5))
                        {{ $receipt_details->sub_heading_line5 }}<br />
                    @endif

                    @if (!empty($receipt_details->tax_info1))
                        <br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
                    @endif

                    @if (!empty($receipt_details->tax_info2))
                        <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
                    @endif

                    <!-- Title of receipt -->
                    @if (!empty($receipt_details->invoice_heading))
                        <br /><span class="sub-headings">{!! $receipt_details->invoice_heading !!}</span>
                    @endif
                </p>
            </div>
            <div class="border-top textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_no }}
                </p>
            </div>
            <div class="textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_date }}
                </p>
            </div>
            <?php
            $due_date = $receipt_details->due_dates;
            $due_date_get = explode(' ', $due_date);
            
            $invoicedate = $receipt_details->invoice_date;
            $get_invoice_date = explode(' ', $invoicedate);
            ?>

            {{-- @if ($due_date_get[0] !== $get_invoice_date[0])
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->due_date_label }}
                        </strong></p>
                    <p class="f-right">{{ $receipt_details->due_dates }}</p>

                </div>
            @endif --}}

            @if (!empty($receipt_details->sales_person_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->sales_person_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->sales_person }}</p>
                </div>
            @endif
            @if (!empty($receipt_details->commission_agent_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->commission_agent_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->commission_agent }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->brand_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_brand }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->device_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_device }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->model_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_model_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->serial_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_serial_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_status_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_status }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_warranty_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_warranty }}
                    </p>
                </div>
            @endif

            <!-- Waiter info -->
            @if (!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->service_staff_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->service_staff }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @if (!empty($receipt_details->table_label))
                                <b>{!! $receipt_details->table_label !!}</b>
                            @endif
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->table }}
                    </p>
                </div>
            @endif

            <!-- customer info -->
            <div class="textbox-info">
                <p style="vertical-align: top;"><strong>
                        {{ $receipt_details->customer_label ?? '' }}
                    </strong></p>

                <p>
                    @if (!empty($receipt_details->customer_info))
                        <div class="bw" style="font-size:15px;">
                            {!! $receipt_details->customer_info !!}
                        </div>
                    @endif
                </p>
            </div>

            @if (!empty($receipt_details->client_id_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->client_id_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->client_id }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_tax_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_tax_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_tax_number }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_custom_fields))
                <div class="textbox-info">
                    <p class="centered">
                        {!! $receipt_details->customer_custom_fields !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->types_of_service))
                <span class="pull-left text-left">
                    <strong style="text-transform: capitalize">
                        {!! $receipt_details->types_of_service_label !!}:</strong>
                </span>
                <p class="f-right">
                    {{ $receipt_details->types_of_service }}
                </p>
            @endif

            @if (!empty($receipt_details->customer_rp_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_rp_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_total_rp }}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_1_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_1_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_1_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_2_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_2_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_2_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_3_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_3_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_3_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_4_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_4_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_4_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_5_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_5_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_5_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->sale_orders_invoice_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('restaurant.order_no')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_no ?? '' !!}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->sale_orders_invoice_date))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('lang_v1.order_dates')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_date ?? '' !!}
                    </p>
                </div>
            @endif
            <table style="margin-top: 25px !important" class="border-bottom width-100 table-f-12 mb-10">
                <thead class="border-bottom-dotted">
                    <tr>
                        <th class="serial_number"></th>

                        <th class="description text-center" style="border:1px solid;font-size :10px">
                            {{ $receipt_details->table_product_label }}
                        </th>
                        @if ($receipt_details->is_flex_enable)
                           
                            <th class="text-center" width="13%" style="border: 1px solid; font-size :10px">Width
                            </th>
                            <th class="text-center" width="13%" style="border: 1px solid; font-size :10px">Hight
                            </th>
                            <th class="text-center" width="15%" style="border: 1px solid;font-size :10px">
                                {{-- {{ $receipt_details->table_qty_label }} --}}
                                Sqft
                            </th>
                             <th class="text-center" width="10%" style="border: 1px solid;font-size :10px">Qty</th>
                        @else
                            <th class="text-center" style="border: 1px solid;font-size :10px">
                                {{-- {{ $receipt_details->table_qty_label }} --}}
                                Qty
                            </th>
                        @endif

                        @if (empty($receipt_details->hide_price))
                            <th class="unit_price text-center" style="border:1px solid;font-size :10px">
                                {{ $receipt_details->table_unit_price_label }}
                            </th>
                            @if (!empty($receipt_details->discounted_unit_price_label))
                                <th class="text-center" style="border:1px solid;font-size :10px">
                                    {{ $receipt_details->discounted_unit_price_label }}
                                </th>
                            @endif
                            @if (!empty($receipt_details->item_discount_label))
                                <th class="text-center" style="border: 1px solid;font-size :10px">
                                    {{ $receipt_details->item_discount_label }}</th>
                            @endif
                            <th class="price text-center" style="border:1px solid;font-size :10px">
                                {{-- {{ $receipt_details->table_subtotal_label }} --}}
                                Total
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt_details->lines as $line)
                        <tr>
                            <td class="serial_number" style="vertical-align: top;">
                                {{-- {{ $loop->iteration }} --}}
                            </td>
                            <td class="description">
                                {{ $line['name'] }} {{ $line['product_variation'] }} {{ $line['variation'] }}
                                @if (!empty($line['sub_sku']))
                                    , {{ $line['sub_sku'] }}
                                    @endif @if (!empty($line['brand']))
                                        , {{ $line['brand'] }}
                                        @endif @if (!empty($line['cat_code']))
                                            , {{ $line['cat_code'] }}
                                        @endif
                                        @if (!empty($line['product_custom_fields']))
                                            , {{ $line['product_custom_fields'] }}
                                        @endif
                                        @if (!empty($line['sell_line_note']))
                                            <br>
                                            <span class="f-8">
                                                {!! $line['sell_line_note'] !!}
                                            </span>
                                        @endif
                                        @if (!empty($line['lot_number']))
                                            <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                        @endif
                                        @if (!empty($line['product_expiry']))
                                            , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                        @endif
                                        @if (!empty($line['warranty_name']))
                                            <br>
                                            <small>
                                                {{ $line['warranty_name'] }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_exp_date']))
                                            <small>
                                                - {{ @format_date($line['warranty_exp_date']) }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_description']))
                                            <small> {{ $line['warranty_description'] ?? '' }}</small>
                                        @endif

                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                1 {{ $line['units'] }} = {{ $line['base_unit_multiplier'] }}
                                                {{ $line['base_unit_name'] }} <br>
                                                {{ $line['unit_price_inc_tax'] }} x {{ $line['quantity'] }} =
                                                {{ $line['line_total'] }}
                                            </small>
                                        @endif
                            </td>
                            @if ($line['is_flex'] || $receipt_details->is_flex_enable)
                                @if (!empty($line['is_flex'] && $receipt_details->is_flex_enable))
                                    

                                   
                                    <td class="text-center">
                                        {{ $line['width'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ $line['height'] }}
                                    </td>
                                    <td class="quantity text-center">
                                        {{ $line['quantity'] }}
                                        {{-- {{ $line['units'] }} --}}
                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                                {{-- {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }}x` --}}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $line['qty_flex'] }}
                                    </td>
                                @else
                                <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        {{ $line['quantity'] }}
                                        {{-- {{ $line['units'] }} --}}

                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                                {{-- {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }} --}}
                                            </small>
                                        @endif
                                    </td>
                                    
                                    
                                @endif
                            @endif

                            @if (empty($receipt_details->hide_price))
                                <td class="unit_price text-center">{{ $line['unit_price_before_discount'] }}</td>

                                @if (!empty($receipt_details->discounted_unit_price_label))
                                    <td class="text-center">
                                        {{ $line['unit_price_inc_tax'] }}
                                    </td>
                                @endif

                                @if (!empty($receipt_details->item_discount_label))
                                    <td class="text-right">
                                        {{ $line['total_line_discount'] ?? '0.00' }}
                                        @if (!empty($line['line_discount_percent']))
                                            ({{ $line['line_discount_percent'] }}%)
                                        @endif
                                    </td>
                                @endif
                                <td class="price text-center">{{ $line['line_total'] }}</td>
                            @endif
                        </tr>
                        @if (!empty($line['modifiers']))
                            @foreach ($line['modifiers'] as $modifier)
                                <tr>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                        @if (!empty($modifier['sub_sku']))
                                            , {{ $modifier['sub_sku'] }}
                                            @endif @if (!empty($modifier['cat_code']))
                                                , {{ $modifier['cat_code'] }}
                                            @endif
                                            @if (!empty($modifier['sell_line_note']))
                                                ({!! $modifier['sell_line_note'] !!})
                                            @endif
                                    </td>
                                    <td class="text-center">{{ $modifier['quantity'] }} {{ $modifier['units'] }}
                                    </td>
                                    @if (empty($receipt_details->hide_price))
                                        <td class="text-center">{{ $modifier['unit_price_inc_tax'] }}</td>
                                        @if (!empty($receipt_details->discounted_unit_price_label))
                                            <td class="text-center">{{ $modifier['unit_price_exc_tax'] }}</td>
                                        @endif
                                        @if (!empty($receipt_details->item_discount_label))
                                            <td class="text-center">0.00</td>
                                        @endif
                                        <td class="text-center">{{ $modifier['line_total'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    <tr>
                        <td @if (!empty($receipt_details->item_discount_label)) colspan="6" @else colspan="5" @endif>&nbsp;</td>
                        @if (!empty($receipt_details->discounted_unit_price_label))
                            <td></td>
                        @endif
                    </tr>
                </tbody>
            </table>
            @if (!empty($receipt_details->total_quantity_label))
                <div class="flex-box">
                    <p class="left text-right">
                        {!! $receipt_details->total_quantity_label !!}
                    </p>
                    <p class="width-50 text-right">
                        {{ $receipt_details->total_quantity }}
                    </p>
                </div>
            @endif
            @if (empty($receipt_details->hide_price))
                <div class="flex-box">
                    <p class="left text-right sub-headings">
                        {!! $receipt_details->subtotal_label !!}
                    </p>
                    <p class="width-50 text-right sub-headings">
                        {{ $receipt_details->subtotal }}
                    </p>
                </div>

                <!-- Shipping Charges -->
                @if (!empty($receipt_details->shipping_charges))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->shipping_charges_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->shipping_charges }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->packing_charge))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->packing_charge_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->packing_charge }}
                        </p>
                    </div>
                @endif

                <!-- Discount -->
                @if (!empty($receipt_details->discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->total_line_discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->line_discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->total_line_discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->additional_expenses))
                    @foreach ($receipt_details->additional_expenses as $key => $val)
                        <div class="flex-box">
                            <p class="width-50 text-right">
                                {{ $key }}:
                            </p>

                            <p class="width-50 text-right">
                                (+)
                                {{ $val }}
                            </p>
                        </div>
                    @endforeach
                @endif

                @if (!empty($receipt_details->reward_point_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->reward_point_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->reward_point_amount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->tax))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->tax_label !!}
                        </p>
                        <p class="width-50 text-right">
                            (+) {{ $receipt_details->tax }}
                        </p>
                    </div>
                @endif

                @if ($receipt_details->round_off_amount > 0)
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->round_off_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->round_off }}
                        </p>
                    </div>
                @endif

                <div class="flex-box">
                    <p class="width-50 text-right sub-headings">
                        {!! $receipt_details->total_label !!}
                    </p>
                    <p class="width-50 text-right sub-headings">
                        {{ $receipt_details->total }}
                    </p>
                </div>
                @if (!empty($receipt_details->total_in_words))
                    <p colspan="2" class="text-right mb-0">
                        <small>
                            ({{ $receipt_details->total_in_words }})
                        </small>
                    </p>
                @endif
                @if (!empty($receipt_details->payments))
                    @foreach ($receipt_details->payments as $payment)
                        <div class="flex-box">
                            <p class="width-50 text-right">{{ $payment['method'] }} ({{ $payment['date'] }}) </p>
                            <p class="width-50 text-right">{{ $payment['amount'] }}</p>
                        </div>
                    @endforeach
                @endif

                <!-- Total Paid-->
                @if (!empty($receipt_details->total_paid))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->total_paid_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_paid }}
                        </p>
                    </div>
                @endif

                <!-- Total Due-->
                @if (!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->total_due_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_due }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->all_due))
                <div class="flex-box">
                        <p class="width-50 text-right">
                            <!--{!! $receipt_details->all_bal_label !!}-->
                            Previous Balance
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->dues }}
                        </p>
                    </div>
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->all_bal_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->all_due }}
                        </p>
                    </div>
                @endif
            @endif
            <div class="border-bottom width-100">&nbsp;</div>
            @if (empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label))
                <!-- tax -->
                @if (!empty($receipt_details->taxes))
                    <table class="border-bottom width-100 table-f-12">
                        <tr>
                            <th colspan="2" class="text-center">{{ $receipt_details->tax_summary_label }}</th>
                        </tr>
                        @foreach ($receipt_details->taxes as $key => $val)
                            <tr>
                                <td class="left">{{ $key }}</td>
                                <td class="right">{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            @endif

            @if (!empty($receipt_details->additional_notes))
                <p class="centered">
                <h6> Note </h6>
                {!! nl2br($receipt_details->additional_notes) !!}

                </p>
            @endif

            {{-- Barcode --}}
            @if ($receipt_details->show_barcode)
                <br />
                <img class="center-block"
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
            @endif

            @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <img class="center-block mt-5"
                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE') }}">
            @endif

            @if (!empty($receipt_details->footer_text))
                <p class="centered">
                    {!! $receipt_details->footer_text !!}<br>
                </p>
            @endif
            {!! $receipt_details->footercompany_text !!}

        </div>
    @else
        @if ($receipt_details->business_id == "89")
        <div class="ticket">
            @if (!empty($receipt_details->logo))
                <div class="text-box centered">
                    <img style="max-height: 100px; width: auto;" src="{{ $receipt_details->logo }}" alt="Logo">
                </div>
            @endif
            <div class="text-box">
                <!-- Logo -->
                <p class="centered">
                    <!-- Header text -->
                    @if (!empty($receipt_details->header_text))
                        <span class="headings">{!! $receipt_details->header_text !!}</span>
                        <br />
                    @endif

                    <!-- business information here -->
                    @if (!empty($receipt_details->display_name))
                        <span class="headings">
                            {{ $receipt_details->display_name }}
                        </span>
                        <br />
                    @endif

                    @if (!empty($receipt_details->address))
                        {!! $receipt_details->address !!}
                        <br />
                    @endif

                    @if (!empty($receipt_details->contact))
                        {!! $receipt_details->contact !!}
                    @endif
                    @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                        ,
                    @endif
                    @if (!empty($receipt_details->website))
                        {{ $receipt_details->website }}
                    @endif
                    @if (!empty($receipt_details->location_custom_fields))
                        <br>{{ $receipt_details->location_custom_fields }}
                    @endif

                    @if (!empty($receipt_details->sub_heading_line1))
                        {{ $receipt_details->sub_heading_line1 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line2))
                        {{ $receipt_details->sub_heading_line2 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line3))
                        {{ $receipt_details->sub_heading_line3 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line4))
                        {{ $receipt_details->sub_heading_line4 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line5))
                        {{ $receipt_details->sub_heading_line5 }}<br />
                    @endif

                    @if (!empty($receipt_details->tax_info1))
                        <br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
                    @endif

                    @if (!empty($receipt_details->tax_info2))
                        <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
                    @endif

                    <!-- Title of receipt -->
                    @if (!empty($receipt_details->invoice_heading))
                        <br /><span class="sub-headings">{!! $receipt_details->invoice_heading !!}</span>
                    @endif
                </p>
            </div>
            <div class="border-top textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_no }}
                </p>
            </div>
            <div class="textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_date }}
                </p>
            </div>

            {{-- @if (!empty($receipt_details->due_date_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->due_date_label }}</strong></p>
                    <p class="f-right">{{ $receipt_details->due_date ?? '' }}</p>
                </div>
            @endif --}}

            @if (!empty($receipt_details->sales_person_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->sales_person_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->sales_person }}</p>
                </div>
            @endif
            @if (!empty($receipt_details->commission_agent_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->commission_agent_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->commission_agent }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->brand_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_brand }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->device_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_device }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->model_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_model_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->serial_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_serial_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_status_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_status }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_warranty_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_warranty }}
                    </p>
                </div>
            @endif

            <!-- Waiter info -->
            @if (!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->service_staff_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->service_staff }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @if (!empty($receipt_details->table_label))
                                <b>{!! $receipt_details->table_label !!}</b>
                            @endif
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->table }}
                    </p>
                </div>
            @endif

            <!-- customer info -->
            <div class="textbox-info">
                <p style="vertical-align: top;"><strong>
                        {{ $receipt_details->customer_label ?? '' }}
                    </strong></p>

                <p>
                    @if (!empty($receipt_details->customer_info))
                        <div class="bw">
                            {!! $receipt_details->customer_info !!}
                        </div>
                    @endif
                </p>
            </div>

            @if (!empty($receipt_details->client_id_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->client_id_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->client_id }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_tax_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_tax_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_tax_number }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_custom_fields))
                <div class="textbox-info">
                    <p class="centered">
                        {!! $receipt_details->customer_custom_fields !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->types_of_service))
                <span class="pull-left text-left">
                    <strong style="text-transform: capitalize">
                        {!! $receipt_details->types_of_service_label !!}:</strong>
                </span>
                <p class="f-right">
                    {{ $receipt_details->types_of_service }}
                </p>
            @endif

            @if (!empty($receipt_details->customer_rp_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_rp_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_total_rp }}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_1_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_1_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_1_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_2_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_2_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_2_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_3_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_3_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_3_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_4_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_4_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_4_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_5_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_5_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_5_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->sale_orders_invoice_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('restaurant.order_no')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_no ?? '' !!}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->sale_orders_invoice_date))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('lang_v1.order_dates')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_date ?? '' !!}
                    </p>
                </div>
            @endif
            <table style="margin-top: 25px !important" class="border-bottom width-100 table-f-12 mb-10">
                <thead class="border-bottom-dotted">
                    <tr>
                        {{-- <th class="serial_number">#</th> --}}
                        <th class="description">
                            {{ $receipt_details->table_product_label }}
                        </th>
                        @if ($receipt_details->is_flex_enable)
                            <th class="text-right" width="10%">Qty Pcs</th>
                            <th class="text-right" width="10%">W</th>
                            <th class="text-right" width="10%">H</th>
                        @endif
                        <th class="quantity text-right">
                            {{-- {{ $receipt_details->table_qty_label }} --}}
                            Qty
                        </th>
                        @if (empty($receipt_details->hide_price))
                            <th class="unit_price text-right">
                                Rate

                                {{-- @dd($receipt_details); --}}
                                {{-- {{ $receipt_details->table_unit_price_label }} --}}
                            </th>
                              @if ($receipt_details->business_id == "89")
                            <th class="unit_price text-right">
                            Vat(5%)
                            </th>
                            @endif
                            @if (!empty($receipt_details->discounted_unit_price_label))
                                <th class="text-right">
                                    {{ $receipt_details->discounted_unit_price_label }}
                                </th>
                            @endif
                            @if (!empty($receipt_details->item_discount_label))
                                <th class="text-right">{{ $receipt_details->item_discount_label }}</th>
                            @endif
                            <th class="price text-right">
                                <!--{{ $receipt_details->table_subtotal_label }}-->
                                Total
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt_details->lines as $line)
                   
                        <tr>
                            {{-- <td class="serial_number" style="vertical-align: top;">
                                {{ $loop->iteration }}
                            </td> --}}
                            <td class="description">
                                {{ $line['name'] }} {{ $line['product_variation'] }} {{ $line['variation'] }}
                                @if (!empty($line['sub_sku']))
                                    , {{ $line['sub_sku'] }}
                                    @endif @if (!empty($line['brand']))
                                        , {{ $line['brand'] }}
                                        @endif @if (!empty($line['cat_code']))
                                            , {{ $line['cat_code'] }}
                                        @endif
                                        @if (!empty($line['product_custom_fields']))
                                            , {{ $line['product_custom_fields'] }}
                                        @endif
                                        @if (!empty($line['sell_line_note']))
                                            <br>
                                            <span class="f-8">
                                                {!! $line['sell_line_note'] !!}
                                            </span>
                                        @endif
                                        @if (!empty($line['lot_number']))
                                            <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                        @endif
                                        @if (!empty($line['product_expiry']))
                                            , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                        @endif
                                        @if (!empty($line['warranty_name']))
                                            <br>
                                            <small>
                                                {{ $line['warranty_name'] }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_exp_date']))
                                            <small>
                                                - {{ @format_date($line['warranty_exp_date']) }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_description']))
                                            <small> {{ $line['warranty_description'] ?? '' }}</small>
                                        @endif

                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                1 {{ $line['units'] }} = {{ $line['base_unit_multiplier'] }}
                                                {{ $line['base_unit_name'] }} <br>
                                                {{ $line['unit_price_inc_tax'] }} x {{ $line['quantity'] }} =
                                                {{ $line['line_total'] }}
                                            </small>
                                        @endif
                            </td>
                            @if ($line['is_flex'] || $receipt_details->is_flex_enable)
                                @if (!empty($line['is_flex'] && $receipt_details->is_flex_enable))
                                    {

                                    <td class="text-right">
                                        {{ $line['qty_flex'] }}
                                    </td>
                                    <td class="text-right">
                                        {{ $line['width'] }}
                                    </td>
                                    <td class="text-right">
                                        {{ $line['height'] }}
                                    </td>

                                }@else{
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    }
                                @endif
                            @endif
                            <td class="quantity text-right">{{ $line['quantity'] }} {{ $line['units'] }}
                                @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                    <br><small>
                                        {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                        {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }}
                                    </small>
                                @endif
                            </td>
                            
                            @if (empty($receipt_details->hide_price))
                                @if ($receipt_details->business_id == "89")
                                  <?php 
                                 $getqunatitybreak = $line['quantity'];
                                  $Withoutpoint = (int) $getqunatitybreak;
                                ?>
                                <td class="unit_price text-right">{{ $line['unit_price_exc_tax'] }}</td>
                                 
<td class="unit_price text-right">
    <?php 
       $currencyValue1 = str_replace(',', '', $line['unit_price_exc_tax']);
                             
                                $currencyValue2 = str_replace(',', '', $line['unit_price_inc_tax']);
                                
                                $numericValue1 = substr($currencyValue1, strpos($currencyValue1, ' ') + 1);
                             $numericValue2 = substr($currencyValue2, strpos($currencyValue2, ' ') + 1);
                                // $numericValue2 = preg_replace('/[^0-9.]/', '', $currencyValue2);
                                // @dd($numericValue1,$numericValue2);
                                $WithoutVat = (float) $currencyValue2 - (float)$currencyValue1;
                                // @dd($WithoutVat);
                                $roundAfter = number_format($WithoutVat, 2, '.', '');

?>

        {{ $roundAfter }}
 
</td>                                    @else
                                   
                                <td class="unit_price text-right">{{ $line['line_total'] }}</td>

                                @endif
                                

                                @if (!empty($receipt_details->discounted_unit_price_label))
                                    <td class="text-right">
                                        {{ $line['line_total'] }}
                                    </td>
                                @endif

                                @if (!empty($receipt_details->item_discount_label))
                                    <td class="text-right">
                                        {{ $line['total_line_discount'] ?? '0.00' }}
                                        @if (!empty($line['line_discount_percent']))
                                            ({{ $line['line_discount_percent'] }}%)
                                        @endif
                                    </td>
                                @endif
                                <td class="price text-right">{{ $line['line_total'] }}</td>
                            @endif
                        </tr>
                        @if (!empty($line['modifiers']))
                            @foreach ($line['modifiers'] as $modifier)
                                <tr>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                        @if (!empty($modifier['sub_sku']))
                                            , {{ $modifier['sub_sku'] }}
                                            @endif @if (!empty($modifier['cat_code']))
                                                , {{ $modifier['cat_code'] }}
                                            @endif
                                            @if (!empty($modifier['sell_line_note']))
                                                ({!! $modifier['sell_line_note'] !!})
                                            @endif
                                    </td>
                                    <td class="text-right">{{ $modifier['quantity'] }} {{ $modifier['units'] }}
                                    </td>
                                    @if (empty($receipt_details->hide_price))
                                        <td class="text-right">{{ $modifier['unit_price_inc_tax'] }}</td>
                                        @if (!empty($receipt_details->discounted_unit_price_label))
                                            <td class="text-right">{{ $modifier['unit_price_exc_tax'] }}</td>
                                        @endif
                                        @if (!empty($receipt_details->item_discount_label))
                                            <td class="text-right">0.00</td>
                                        @endif
                                        <td class="text-right">{{ $modifier['line_total'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    <tr>
                        <td @if (!empty($receipt_details->item_discount_label)) colspan="6" @else colspan="5" @endif>&nbsp;</td>
                        @if (!empty($receipt_details->discounted_unit_price_label))
                            <td></td>
                        @endif
                    </tr>
                </tbody>
            </table>
            @if (!empty($receipt_details->total_quantity_label))
                <div class="flex-box">
                    <p class="left text-right">
                        {!! $receipt_details->total_quantity_label !!}
                    </p>
                    <p class="width-50 text-right">
                        {{ $receipt_details->total_quantity }}
                    </p>
                </div>
            @endif
            
            @if (empty($receipt_details->hide_price))
                <div class="flex-box">
                    <p class="left text-right sub-headings">
                        {{-- {!! $receipt_details->subtotal_label !!}/ --}}
                        Total Before Vat

                       
                    </p>
                    <p class="width-50 text-right sub-headings">

                       @php
                                
                                foreach ($receipt_details->taxes as $key => $val) {
                                    $VATvalue = $val;
                                   
                                }
                                
                                $currencyValue1 = str_replace(',', '', $receipt_details->total);
                             
                                $currencyValue2 = $VATvalue;
                                
                                $numericValue1 = substr($currencyValue1, strpos($currencyValue1, ' ') + 1);
                             
                                // $numericValue2 = preg_replace('/[^0-9.]/', '', $currencyValue2);
                                // @dd($numericValue1,$numericValue2);
                                $WithoutVat = (float) $numericValue1 - (float)$VATvalue;
                                // @dd($WithoutVat);
                                $roundAfter = number_format($WithoutVat, 2, '.', '');
                                
                            @endphp

                        
                        {{ $roundAfter . " د.إ" }}
                    </p>
                </div>

                @if (empty($receipt_details->hide_price) || !empty($receipt_details->tax_summary_label))
                    <!-- tax -->
                    @if (!empty($receipt_details->taxes))

                        @foreach ($receipt_details->taxes as $key => $val)
                            <div class="flex-box">
                                <p class="left text-right sub-headings">

                                   
                                    
                                    {{ $key }}

                                </p>
                                <p class="width-50 text-right sub-headings">

                                    @php
                                        $VATvalue = $val . " د.إ";
                                        $VATvalueSum = $val;
                                    @endphp

                                    {{ $VATvalue }}
                            </div>
                        @endforeach
                        {{-- 
                            @foreach ($receipt_details->taxes as $key => $val)
                                <tr>
                                    <th style=": 300%">{{ $key}}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach
                        </table> --}}
                    @endif
                @endif
                <!-- Shipping Charges -->
                @if (!empty($receipt_details->shipping_charges))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->shipping_charges_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->shipping_charges }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->packing_charge))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->packing_charge_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->packing_charge }}
                        </p>
                    </div>
                @endif

                <!-- Discount -->
                @if (!empty($receipt_details->discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->total_line_discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->line_discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->total_line_discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->additional_expenses))
                    @foreach ($receipt_details->additional_expenses as $key => $val)
                        <div class="flex-box">
                            <p class="width-50 text-right">
                                {{ $key }}:
                            </p>

                            <p class="width-50 text-right">
                                (+)
                                {{ $val }}
                            </p>
                        </div>
                    @endforeach
                @endif

                @if (!empty($receipt_details->reward_point_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->reward_point_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->reward_point_amount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->tax))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->tax_label !!}
                        </p>
                        <p class="width-50 text-right">
                            (+) {{ $receipt_details->tax }}
                        </p>
                    </div>
                @endif

                @if ($receipt_details->round_off_amount > 0)
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->round_off_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->round_off }}
                        </p>
                    </div>
                @endif

                <div class="flex-box">
                    <p class="width-50 text-right sub-headings">
                        {!! $receipt_details->total_label !!}
                    </p>
                    <p class="width-50 text-right sub-headings">
                        @if ($receipt_details->business_id == "89")
                         
                        {{ $receipt_details->total }}

                        @else
                        
                        {{ $receipt_details->total }}
                        @endif
                        
                        
                    </p>
                </div>
                @if (!empty($receipt_details->total_in_words))
                    <p colspan="2" class="text-right mb-0">
                        <small>
                            ({{ $receipt_details->total_in_words }})
                        </small>
                    </p>
                @endif
                @if (!empty($receipt_details->payments))
                    @foreach ($receipt_details->payments as $payment)
                        <div class="flex-box">
                            <p class="width-50 text-right">{{ $payment['method'] }} ({{ $payment['date'] }}) </p>
                            <p class="width-50 text-right">{{ $payment['amount'] }}</p>
                        </div>
                    @endforeach
                @endif

                <!-- Total Paid-->
                @if (!empty($receipt_details->total_paid))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->total_paid_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_paid }}
                        </p>
                    </div>
                @endif

                <!-- Total Due-->
                @if (!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            <!--{!! $receipt_details->total_due_label !!}-->
                            Current Due
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_due }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->all_due))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->all_bal_label !!}
                        </p>
                        <p class="width-50 text-right">
                            @if ($receipt_details->business_id != "89")
                            {{ $receipt_details->all_due }}
                        @endif
                        </p>
                    </div>
                @endif
            @endif
            <div class="border-bottom width-100">&nbsp;</div>
            {{-- @if (!empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label)) --}}
            <!-- tax -->

            {{-- @endif --}}

            @if (!empty($receipt_details->additional_notes))
                <p class="centered">
                    {!! nl2br($receipt_details->additional_notes) !!}
                </p>
            @endif

            {{-- Barcode --}}
            @if ($receipt_details->show_barcode)
                <br />
                <img class="center-block"
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
            @endif

            @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <img class="center-block mt-5"
                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE') }}">
            @endif

            @if (!empty($receipt_details->footer_text))
                <p class="centered">
                    {!! $receipt_details->footer_text !!}<br>
                </p>
            @endif
            {!! $receipt_details->footercompany_text !!}

        </div>
        @else
        <div class="ticket">


            @if (!empty($receipt_details->logo))
                <div class="text-box centered">
                    <img style="max-height: 100px; width: auto;" src="{{ $receipt_details->logo }}" alt="Logo">
                </div>
            @endif
            <div class="text-box">
                <!-- Logo -->
                <p class="centered">
                    <!-- Header text -->
                    @if (!empty($receipt_details->header_text))
                        <span class="headings">{!! $receipt_details->header_text !!}</span>
                        <br />
                    @endif

                    <!-- business information here -->
                    @if (!empty($receipt_details->display_name))
                        <span class="headings">
                            {{ $receipt_details->display_name }}
                        </span>
                        <br />
                    @endif

                    @if (!empty($receipt_details->address))
                        {!! $receipt_details->address !!}
                        <br />
                    @endif

                    @if (!empty($receipt_details->contact))
                        {!! $receipt_details->contact !!}
                    @endif
                    @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                        ,
                    @endif
                    @if (!empty($receipt_details->website))
                        {{ $receipt_details->website }}
                    @endif
                    @if (!empty($receipt_details->location_custom_fields))
                        <br>{{ $receipt_details->location_custom_fields }}
                    @endif

                    @if (!empty($receipt_details->sub_heading_line1))
                        {{ $receipt_details->sub_heading_line1 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line2))
                        {{ $receipt_details->sub_heading_line2 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line3))
                        {{ $receipt_details->sub_heading_line3 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line4))
                        {{ $receipt_details->sub_heading_line4 }}<br />
                    @endif
                    @if (!empty($receipt_details->sub_heading_line5))
                        {{ $receipt_details->sub_heading_line5 }}<br />
                    @endif

                    @if (!empty($receipt_details->tax_info1))
                        <br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
                    @endif

                    @if (!empty($receipt_details->tax_info2))
                        <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
                    @endif

                    <!-- Title of receipt -->
                    @if (!empty($receipt_details->invoice_heading))
                        <br /><span class="sub-headings">{!! $receipt_details->invoice_heading !!}</span>
                    @endif
                </p>
            </div>
            <div class="border-top textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_no }}
                </p>
            </div>
            <div class="textbox-info">
                <p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
                <p class="f-right">
                    {{ $receipt_details->invoice_date }}
                </p>
            </div>

            {{-- @if (!empty($receipt_details->due_date_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->due_date_label }}</strong></p>
                    <p class="f-right">{{ $receipt_details->due_date ?? '' }}</p>
                </div>
            @endif --}}

            @if (!empty($receipt_details->sales_person_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->sales_person_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->sales_person }}</p>
                </div>
            @endif
            @if (!empty($receipt_details->commission_agent_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->commission_agent_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->commission_agent }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->brand_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_brand }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->device_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_device }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->model_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_model_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>{{ $receipt_details->serial_no_label }}</strong></p>

                    <p class="f-right">{{ $receipt_details->repair_serial_no }}</p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_status_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_status }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->repair_warranty_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->repair_warranty }}
                    </p>
                </div>
            @endif

            <!-- Waiter info -->
            @if (!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->service_staff_label !!}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->service_staff }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @if (!empty($receipt_details->table_label))
                                <b>{!! $receipt_details->table_label !!}</b>
                            @endif
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->table }}
                    </p>
                </div>
            @endif

            <!-- customer info -->
            <div class="textbox-info">
                <p style="vertical-align: top;"><strong>
                        {{ $receipt_details->customer_label ?? '' }}
                    </strong></p>

                <p>
                    @if (!empty($receipt_details->customer_info))
                        <div class="bw">
                            {!! $receipt_details->customer_info !!}
                        </div>
                    @endif
                </p>
            </div>

            @if (!empty($receipt_details->client_id_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->client_id_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->client_id }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_tax_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_tax_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_tax_number }}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->customer_custom_fields))
                <div class="textbox-info">
                    <p class="centered">
                        {!! $receipt_details->customer_custom_fields !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->types_of_service))
                <span class="pull-left text-left">
                    <strong style="text-transform: capitalize">
                        {!! $receipt_details->types_of_service_label !!}:</strong>
                </span>
                <p class="f-right">
                    {{ $receipt_details->types_of_service }}
                </p>
            @endif

            @if (!empty($receipt_details->customer_rp_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {{ $receipt_details->customer_rp_label }}
                        </strong></p>
                    <p class="f-right">
                        {{ $receipt_details->customer_total_rp }}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_1_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_1_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_1_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_2_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_2_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_2_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_3_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_3_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_3_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_4_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_4_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_4_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->shipping_custom_field_5_label))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            {!! $receipt_details->shipping_custom_field_5_label !!}
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->shipping_custom_field_5_value ?? '' !!}
                    </p>
                </div>
            @endif
            @if (!empty($receipt_details->sale_orders_invoice_no))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('restaurant.order_no')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_no ?? '' !!}
                    </p>
                </div>
            @endif

            @if (!empty($receipt_details->sale_orders_invoice_date))
                <div class="textbox-info">
                    <p class="f-left"><strong>
                            @lang('lang_v1.order_dates')
                        </strong></p>
                    <p class="f-right">
                        {!! $receipt_details->sale_orders_invoice_date ?? '' !!}
                    </p>
                </div>
            @endif
            <table style="margin-top: 25px !important" class="border-bottom width-100 table-f-12 mb-10">
                <thead class="border-bottom-dotted">
                    <tr>
                        <th class="serial_number">#</th>
                        <th class="description">
                            {{ $receipt_details->table_product_label }}
                        </th>
                        @if ($receipt_details->is_flex_enable)
                            <th class="text-right" width="10%">Qty Pcs</th>
                            <th class="text-right" width="10%">W</th>
                            <th class="text-right" width="10%">H</th>
                        @endif
                        <th class="quantity text-right">
                            <!--{{ $receipt_details->table_qty_label }}-->
                            Qty
                        </th>
                        @if (empty($receipt_details->hide_price))
                            <th class="unit_price text-right">
                                {{ $receipt_details->table_unit_price_label }}
                            </th>
                            @if (!empty($receipt_details->discounted_unit_price_label))
                                <th class="text-right">
                                    {{ $receipt_details->discounted_unit_price_label }}
                                </th>
                            @endif
                            @if (!empty($receipt_details->item_discount_label))
                                <th class="text-right">{{ $receipt_details->item_discount_label }}</th>
                            @endif
                            <th class="price text-right">
                                <!--{{ $receipt_details->table_subtotal_label }}-->
                                Total
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt_details->lines as $line)
                        <tr>
                            <td class="serial_number" style="vertical-align: top;">
                                {{ $loop->iteration }}
                            </td>
                            <td class="description">
                                {{ $line['name'] }} {{ $line['product_variation'] }} {{ $line['variation'] }}
                                @if (!empty($line['sub_sku']))
                                    , {{ $line['sub_sku'] }}
                                    @endif @if (!empty($line['brand']))
                                        , {{ $line['brand'] }}
                                        @endif @if (!empty($line['cat_code']))
                                            , {{ $line['cat_code'] }}
                                        @endif
                                        @if (!empty($line['product_custom_fields']))
                                            , {{ $line['product_custom_fields'] }}
                                        @endif
                                        @if (!empty($line['sell_line_note']))
                                            <br>
                                            <span class="f-8">
                                                {!! $line['sell_line_note'] !!}
                                            </span>
                                        @endif
                                        @if (!empty($line['lot_number']))
                                            <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                        @endif
                                        @if (!empty($line['product_expiry']))
                                            , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                        @endif
                                        @if (!empty($line['warranty_name']))
                                            <br>
                                            <small>
                                                {{ $line['warranty_name'] }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_exp_date']))
                                            <small>
                                                - {{ @format_date($line['warranty_exp_date']) }}
                                            </small>
                                        @endif
                                        @if (!empty($line['warranty_description']))
                                            <small> {{ $line['warranty_description'] ?? '' }}</small>
                                        @endif

                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                1 {{ $line['units'] }} = {{ $line['base_unit_multiplier'] }}
                                                {{ $line['base_unit_name'] }} <br>
                                                {{ $line['unit_price_inc_tax'] }} x {{ $line['quantity'] }} =
                                                {{ $line['line_total'] }}
                                            </small>
                                        @endif
                            </td>
                            @if ($line['is_flex'] || $receipt_details->is_flex_enable)
                                @if (!empty($line['is_flex'] && $receipt_details->is_flex_enable))
                                    {

                                    <td class="text-right">
                                        {{ $line['qty_flex'] }}
                                    </td>
                                    <td class="text-right">
                                        {{ $line['width'] }}
                                    </td>
                                    <td class="text-right">
                                        {{ $line['height'] }}
                                    </td>

                                }@else{
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    }
                                @endif
                            @endif
                            <td class="quantity text-right">{{ $line['quantity'] }} {{ $line['units'] }}
                                @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                    <br><small>
                                        {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                        {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }}
                                    </small>
                                @endif
                            </td>
                            @if (empty($receipt_details->hide_price))
                                <td class="unit_price text-right">{{ $line['unit_price_before_discount'] }}</td>

                                @if (!empty($receipt_details->discounted_unit_price_label))
                                    <td class="text-right">
                                        {{ $line['unit_price_inc_tax'] }}
                                    </td>
                                @endif

                                @if (!empty($receipt_details->item_discount_label))
                                    <td class="text-right">
                                        {{ $line['total_line_discount'] ?? '0.00' }}
                                        @if (!empty($line['line_discount_percent']))
                                            ({{ $line['line_discount_percent'] }}%)
                                        @endif
                                    </td>
                                @endif
                                <td class="price text-right">{{ $line['line_total'] }}</td>
                            @endif
                        </tr>
                        @if (!empty($line['modifiers']))
                            @foreach ($line['modifiers'] as $modifier)
                                <tr>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                        @if (!empty($modifier['sub_sku']))
                                            , {{ $modifier['sub_sku'] }}
                                            @endif @if (!empty($modifier['cat_code']))
                                                , {{ $modifier['cat_code'] }}
                                            @endif
                                            @if (!empty($modifier['sell_line_note']))
                                                ({!! $modifier['sell_line_note'] !!})
                                            @endif
                                    </td>
                                    <td class="text-right">{{ $modifier['quantity'] }} {{ $modifier['units'] }}
                                    </td>
                                    @if (empty($receipt_details->hide_price))
                                        <td class="text-right">{{ $modifier['unit_price_inc_tax'] }}</td>
                                        @if (!empty($receipt_details->discounted_unit_price_label))
                                            <td class="text-right">{{ $modifier['unit_price_exc_tax'] }}</td>
                                        @endif
                                        @if (!empty($receipt_details->item_discount_label))
                                            <td class="text-right">0.00</td>
                                        @endif
                                        <td class="text-right">{{ $modifier['line_total'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    <tr>
                        <td @if (!empty($receipt_details->item_discount_label)) colspan="6" @else colspan="5" @endif>&nbsp;</td>
                        @if (!empty($receipt_details->discounted_unit_price_label))
                            <td></td>
                        @endif
                    </tr>
                    
                        @php
                                $sum = 0;
                            @endphp

                    @foreach ($receipt_details->lines as $line)
                         @php
                             
                            $sum += $line['quantity'];   

                         @endphp
                      
                    @endforeach
               
                    <td></td>
                    <td></td>  
                   <td></td>  
                   <td>
                        <p class="width-20 text-left testCenter">
                            <b>
                                {{ $sum .".00 Item(s)"}}
                            </b>  
                        </p>
                    
                </td>  
                
                </tbody>
            </table>
            @if (!empty($receipt_details->total_quantity_label))
                <div class="flex-box">
                    <p class="left text-right">
                        {!! $receipt_details->total_quantity_label !!}
                    </p>
                    <p class="width-50 text-right">
                        {{ $receipt_details->total_quantity }}
                    </p>
                </div>
            @endif
            @if (empty($receipt_details->hide_price))
                <div class="flex-box">
                    <p class="left text-right sub-headings">
                        {!! $receipt_details->subtotal_label !!}
                    </p>
                    <p class="width-50 text-right sub-headings">
                        {{ $receipt_details->subtotal }}
                    </p>
                </div>

                <!-- Shipping Charges -->
                @if (!empty($receipt_details->shipping_charges))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->shipping_charges_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->shipping_charges }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->packing_charge))
                    <div class="flex-box">
                        <p class="left text-right">
                            {!! $receipt_details->packing_charge_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->packing_charge }}
                        </p>
                    </div>
                @endif

                <!-- Discount -->
                @if (!empty($receipt_details->discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->total_line_discount))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->line_discount_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->total_line_discount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->additional_expenses))
                    @foreach ($receipt_details->additional_expenses as $key => $val)
                        <div class="flex-box">
                            <p class="width-50 text-right">
                                {{ $key }}:
                            </p>

                            <p class="width-50 text-right">
                                (+)
                                {{ $val }}
                            </p>
                        </div>
                    @endforeach
                @endif

                @if (!empty($receipt_details->reward_point_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->reward_point_label !!}
                        </p>

                        <p class="width-50 text-right">
                            (-) {{ $receipt_details->reward_point_amount }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->tax))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->tax_label !!}
                        </p>
                        <p class="width-50 text-right">
                            (+) {{ $receipt_details->tax }}
                        </p>
                    </div>
                @endif

                @if ($receipt_details->round_off_amount > 0)
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->round_off_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->round_off }}
                        </p>
                    </div>
                @endif

                <div class="flex-box">
                    <p class="width-50 text-right sub-headings">
                        {!! $receipt_details->total_label !!}
                    </p>
                    <p class="width-50 text-right sub-headings">
                        {{ $receipt_details->total }}
                    </p>
                </div>
                @if (!empty($receipt_details->total_in_words))
                    <p colspan="2" class="text-right mb-0">
                        <small>
                            ({{ $receipt_details->total_in_words }})
                        </small>
                    </p>
                @endif
                @if (!empty($receipt_details->payments))
                    @foreach ($receipt_details->payments as $payment)
                        <div class="flex-box">
                            <p class="width-50 text-right">{{ $payment['method'] }} ({{ $payment['date'] }}) </p>
                            <p class="width-50 text-right">{{ $payment['amount'] }}</p>
                        </div>
                    @endforeach
                @endif

                <!-- Total Paid-->
                @if (!empty($receipt_details->total_paid))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->total_paid_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_paid }}
                        </p>
                    </div>
                @endif

                <!-- Total Due-->
                @if (!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            <!--{!! $receipt_details->total_due_label !!}-->
                            Current Due
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->total_due }}
                        </p>
                    </div>
                @endif

                @if (!empty($receipt_details->all_due))
                    <div class="flex-box">
                        <p class="width-50 text-right">
                            {!! $receipt_details->all_bal_label !!}
                        </p>
                        <p class="width-50 text-right">
                            {{ $receipt_details->all_due }}
                        </p>
                    </div>
                @endif
            @endif
            <div class="border-bottom width-100">&nbsp;</div>
            @if (empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label))
                <!-- tax -->
                @if (!empty($receipt_details->taxes))
                    <table class="border-bottom width-100 table-f-12">
                        <tr>
                            <th colspan="2" class="text-center">{{ $receipt_details->tax_summary_label }}</th>
                        </tr>
                        @foreach ($receipt_details->taxes as $key => $val)
                            <tr>
                                <td class="left">{{ $key }}</td>
                                <td class="right">{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            @endif

            @if (!empty($receipt_details->additional_notes))
                <p class="centered">
                    {!! nl2br($receipt_details->additional_notes) !!}
                </p>
            @endif

            {{-- Barcode --}}
            @if ($receipt_details->show_barcode)
                <br />
                <img class="center-block"
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
            @endif

            @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <img class="center-block mt-5"
                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE') }}">
            @endif

            @if (!empty($receipt_details->footer_text))
                <p class="centered">
                    {!! $receipt_details->footer_text !!}<br>
                </p>
            @endif
            {!! $receipt_details->footercompany_text !!}

        </div> 
        @endif
    @endif
    <!-- <button id="btnPrint" class="hidden-print">Print</button>
        <script src="script.js"></script> -->
</body>

</html>

<style type="text/css">
    .f-8 {
        font-size: 8px !important;
    }

    body {
        color: #000000;
    }

    @media print {
        * {
            font-size: 12px;
            font-family: 'Times New Roman';
            word-break: break-all;
        }

        .f-8 {
            font-size: 8px !important;
        }

        .headings {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sub-headings {
            font-size: 15px !important;
            font-weight: 700 !important;
        }

        .border-top {
            border-top: 1px solid #242424;
        }

        .border-bottom {
            border-bottom: 1px solid #242424;
        }

        .border-bottom-dotted {
            border-bottom: 1px dotted darkgray;
        }

        td.serial_number,
        th.serial_number {
            width: 2%;
            max-width: 2%;
        }

        td.description,
        th.description {
            width: 20%;
            max-width: 20%;
        }

        td.quantity,
        th.quantity {
            width: 10%;
            max-width: 10%;
            word-break: break-all;
        }

        td.unit_price,
        th.unit_price {
            width: 15%;
            max-width: 15%;
            word-break: break-all;
        }

        td.price,
        th.price {
            width: 20%;
            max-width: 20%;
            word-break: break-all;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: 100%;
            max-width: 100%;
        }

        img {
            max-width: inherit;
            width: auto;
        }

        .hidden-print,
        .hidden-print * {
            display: none !important;
        }
    }

    .table-info {
        width: 100%;
    }

    .table-info tr:first-child td,
    .table-info tr:first-child th {
        padding-top: 8px;
    }

    .table-info th {
        text-align: left;
    }

    .table-info td {
        text-align: right;
    }

    .logo {
        float: left;
        width: 35%;
        padding: 10px;
    }

    .text-with-image {
        float: left;
        width: 65%;
    }

    .text-box {
        width: 100%;
        height: auto;
    }

    .textbox-info {
        clear: both;
    }

    .textbox-info p {
        margin-bottom: 0px
    }

    .flex-box {
        display: flex;
        width: 100%;
    }

    .flex-box p {
        width: 50%;
        margin-bottom: 0px;
        white-space: nowrap;
    }

    .table-f-12 th,
    .table-f-12 td {
        font-size: 12px;
        word-break: break-word;
    }

    .bw {
        word-break: break-word;
    }
    
     .testCenter{
        margin-left: -48px;
       
    }
</style>
