@php
    $common_settings = session()->get('business.common_settings');
    $multiplier = 1;
@endphp
    
@foreach ($sub_units as $key => $value)
    @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        @php
            $multiplier = $value['multiplier'];
        @endphp
    @endif
@endforeach

<tr class="product_row" data-row_index="{{ $row_count }}"
    @if (!empty($so_line)) data-so_id="{{ $so_line->transaction_id }}" @endif>
    <td>
        @if (!empty($so_line))
            <input type="hidden" name="products[{{ $row_count }}][so_line_id]" value="{{ $so_line->id }}">
        @endif

        @if (!empty($product->imei_number))
        <input type="hidden" name="products[{{ $row_count }}][imei_number]"
        value="{{ $product->imei_number }}">

        @endif
            
        @php
            $product_name = $product->product_name . '<br/>' . $product->sub_sku . '<br/>' .$product->imei_number;
            if (!empty($product->brand)) {
                $product_name .= ' ' . $product->brand;
            }
            $max_qty = number_format($product->qty_available , 2);
        @endphp

        

        @if (($edit_price || $edit_discount) && empty($is_direct_sell))
            <div title="@lang('lang_v1.pos_edit_product_price_help')">
                <span class="text-link text-info cursor-pointer" data-toggle="modal"
                    data-target="#row_edit_product_price_modal_{{ $row_count }}">
                    {!! $product_name !!}
                    &nbsp;<i class="fa fa-info-circle"></i>
                </span>
            </div>
        @else
            {!! $product_name !!}
            <br/><span style="font-size: 10px;" >Current Stock: {{$max_qty}} {{$product->unit}}</span> 
        @endif
        <input type="hidden" class="enable_sr_no" value="{{ $product->enable_sr_no }}">
        <input type="hidden" class="product_type" name="products[{{ $row_count }}][product_type]"
            value="{{ $product->product_type }}">

        @php
            $hide_tax = 'hide';
            if (session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }
            
               $hide_purchase_inc = 'hide';
						if( session()->get('business.enable_unit_purchase_price') == 1){
							$hide_purchase_inc = '';
						}
            
            $tax_id = $product->tax_id;
            $item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
            
            $unit_price_inc_tax =(!empty($product->sell_price_inc_tax)
                    ? $product->sell_price_inc_tax
                    : (!empty($product->default_single_dollar_dsp)
                        ? $product->default_single_dollar_dsp
                        : $product->default_single_aud_dsp));;
                        
                   if (empty($unit_price_inc_tax)){
               $unit_price_inc_tax= (!empty($product->default_sell_price)
                    ? $product->default_sell_price
                    : (!empty($product->default_single_dollar_dsp)
                        ? $product->default_single_dollar_dsp
                        : $product->default_single_aud_dsp));;
            }
            
            if (!empty($so_line)) {
                $tax_id = $so_line->tax_id;
                $item_tax = $so_line->item_tax;
            }
            
            if ($hide_tax == 'hide') {
                $tax_id = null;
                $unit_price_inc_tax = $product->default_sell_price;
            }
            
            $discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
            $discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;
            
            if (!empty($discount)) {
                $discount_type = $discount->discount_type;
                $discount_amount = $discount->discount_amount;
            }
            
            if (!empty($so_line)) {
                $discount_type = $so_line->line_discount_type;
                $discount_amount = $so_line->line_discount_amount;
            }
            
            $sell_line_note = '';
            if (!empty($product->sell_line_note)) {
                $sell_line_note = $product->sell_line_note;
            }
        @endphp

        @if (!empty($discount))
            {!! Form::hidden("products[$row_count][discount_id]", $discount->id) !!}
        @endif

        @php
            $warranty_id = !empty($action) && $action == 'edit' && !empty($product->warranties->first()) ? $product->warranties->first()->id : $product->warranty_id;
            
            if ($discount_type == 'fixed') {
                $discount_amount = $discount_amount * $multiplier;
            }
        @endphp

        @if (empty($is_direct_sell))
            <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{ $row_count }}"
                tabindex="-1" role="dialog">
                @include('sale_pos.partials.row_edit_product_price_modal')
            </div>
        @endif

        <!-- Description modal end -->
        @if (in_array('modifiers', $enabled_modules))
            <div class="modifiers_html">
                @if (!empty($product->product_ms))
                    @include('restaurant.product_modifier_set.modifier_for_product', [
                        'edit_modifiers' => true,
                        'row_count' => $loop->index,
                        'product_ms' => $product->product_ms,
                    ])
                @endif
            </div>
        @endif

        @php
            $max_quantity = $product->qty_available;
            $formatted_max_quantity = $product->formatted_qty_available;
            
            if (!empty($action) && $action == 'edit') {
                if (!empty($so_line)) {
                    $qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
                    $max_quantity = $qty_available;
                    $formatted_max_quantity = number_format($qty_available, session('constants.quantity_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']);
                }
            } else {
                if (!empty($so_line) && $so_line->qty_available <= $max_quantity) {
                    $max_quantity = $so_line->qty_available;
                    $formatted_max_quantity = $so_line->formatted_qty_available;
                }
            }
            
            $max_qty_rule = $max_quantity;
            $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $formatted_max_quantity, 'unit' => $product->unit]);
        @endphp

        @if (session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
            @php
                $lot_enabled = session()->get('business.enable_lot_number');
                $exp_enabled = session()->get('business.enable_product_expiry');
                $lot_no_line_id = '';
                if (!empty($product->lot_no_line_id)) {
                    $lot_no_line_id = $product->lot_no_line_id;
                }
            @endphp
            @if (!empty($product->lot_numbers) && empty($is_sales_order))
                <select class="form-control lot_number input-sm" name="products[{{ $row_count }}][lot_no_line_id]"
                    @if (!empty($product->transaction_sell_lines_id)) disabled @endif>
                    <option value="">@lang('lang_v1.lot_n_expiry')</option>
                    @foreach ($product->lot_numbers as $lot_number)
                        @php
                            $selected = '';
                            if ($lot_number->purchase_line_id == $lot_no_line_id) {
                                $selected = 'selected';
                            
                                $max_qty_rule = $lot_number->qty_available;
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit]);
                            }
                            
                            $expiry_text = '';
                            if ($exp_enabled == 1 && !empty($lot_number->exp_date)) {
                                if (\Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date))) {
                                    $expiry_text = '(' . __('report.expired') . ')';
                                }
                            }
                            
                            //preselected lot number if product searched by lot number
                            if (!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
                                $selected = 'selected';
                            
                                $max_qty_rule = $lot_number->qty_available;
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit]);
                            }
                        @endphp
                        <option value="{{ $lot_number->purchase_line_id }}"
                            data-qty_available="{{ $lot_number->qty_available }}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty' => $lot_number->qty_formated, 'unit' => $product->unit])"
                            {{ $selected }}>
                            @if (!empty($lot_number->lot_number) && $lot_enabled == 1)
                                {{ $lot_number->lot_number }}
                                @endif @if ($lot_enabled == 1 && $exp_enabled == 1)
                                    -
                                    @endif @if ($exp_enabled == 1 && !empty($lot_number->exp_date))
                                        @lang('product.exp_date'): {{ @format_date($lot_number->exp_date) }}
                                    @endif {{ $expiry_text }}
                        </option>
                    @endforeach
                </select>
            @endif
        @endif
        @if (!empty($pos_settings['enable_pos_whole_sale_screen']))
            <textarea class="form-control" name="products[{{ $row_count }}][sell_line_note_whole_sale]" rows="1">{{ $sell_line_note }}</textarea>
        @endif
        @if (!empty($is_direct_sell))
            <br>
            <textarea class="form-control" name="products[{{ $row_count }}][sell_line_note]" rows="1">{{ $sell_line_note }}</textarea>
            <p class="help-block"><small>@lang('lang_v1.sell_line_description_help')</small></p>
        @endif
    </td>
		
    @if ($enable_flex->enable_is_flex || $flexUnit->is_flex)
        @if (!empty($enable_flex->enable_is_flex && $flexUnit->is_flex))

            

           <td>
                {{-- <input type= "number"> --}}
                <span style="font-weight: bold">W =</span> <input type="number" min="0"
                    class="form-control is_flex_width" name="products[{{ $row_count }}][width]"
                    value="{{ $product->width }}" style="width:100px; margin-left:40px; margin-top:-24px" required>
                <span style="font-weight: bold">H =</span> <input type="number" min="1"
                    class="form-control is_flex_height" name="products[{{ $row_count }}][height]"
                    value="{{ $product->height }}" style="width:100px; margin-left:40px; margin-top:-12px" required>
            </td>
            <td>
                {{-- If edit then transaction sell lines will be present --}}
                @if (!empty($product->transaction_sell_lines_id))
                    <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                        class="form-control" value="{{ $product->transaction_sell_lines_id }}">
                @endif

                <input type="hidden" name="products[{{ $row_count }}][product_id]" class="form-control product_id"
                    value="{{ $product->product_id }}">

                <input type="hidden" value="{{ $product->variation_id }}"
                    name="products[{{ $row_count }}][variation_id]" class="row_variation_id">

                <input type="hidden" value="{{ $product->enable_stock }}"
                    name="products[{{ $row_count }}][enable_stock]">

                @if (empty($product->quantity_ordered))
                    @php
                        $product->quantity_ordered = 1;
                    @endphp
                @endif

                @php
                    $allow_decimal = true;
                    if ($product->unit_allow_decimal != 1) {
                        $allow_decimal = false;
                    }
                @endphp
                @foreach ($sub_units as $key => $value)
                    @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                        @php
                            $max_qty_rule = $max_qty_rule / $multiplier;
                            $unit_name = $value['name'];
                            $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                            
                            if (!empty($product->lot_no_line_id)) {
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                            }
                            
                            if ($value['allow_decimal']) {
                                $allow_decimal = true;
                            }
                        @endphp
                    @endif
                @endforeach
                <div class="input-group input-number">
                    {{-- <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i
                                class="fa fa-minus text-danger"></i></button></span> --}}
                    <input type="text" data-min="1"
                        class="form-control pos_quantity input_number mousetrap input_quantity"
                        value="{{ @format_quantity($product->quantity_ordered) }}"
                        name="products[{{ $row_count }}][quantity]"
                        data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                        @if ($allow_decimal) data-decimal=1 
                    @else 
                        data-decimal=0 
                        data-rule-abs_digit="true" 
                        data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif
                        data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                        @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" 
                        data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif>
                    {{-- <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i
                                class="fa fa-plus text-success"></i></button></span> --}}
                </div>

                <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
                    value="{{ $product->unit_id }}">
                @if (count($sub_units) > 0)
                    <br>
                    <select name="products[{{ $row_count }}][sub_unit_id]" class="form-control input-sm sub_unit">
                        @foreach ($sub_units as $key => $value)
                            <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                                data-unit_name="{{ $value['name'] }}"
                                data-allow_decimal="{{ $value['allow_decimal'] }}"
                                @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                                {{ $value['name'] }}
                            </option>
                        @endforeach
                    </select>
                @else
                    {{ $product->unit }}
                @endif


                <input type="hidden" class="base_unit_multiplier"
                    name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">

                <input type="hidden" class="hidden_base_unit_sell_price"
                    value="{{ $product->default_sell_price / $multiplier }}">

                {{-- Hidden fields for combo products --}}
                @if ($product->product_type == 'combo' && !empty($product->combo_products))

                    @foreach ($product->combo_products as $k => $combo_product)
                        @if (isset($action) && $action == 'edit')
                            @php
                                $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                                
                                $qty_total = $combo_product['quantity'];
                            @endphp
                        @else
                            @php
                                $qty_total = $combo_product['qty_required'];
                            @endphp
                        @endif

                        <input type="hidden"
                            name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                            value="{{ $combo_product['product_id'] }}">

                        <input type="hidden"
                            name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                            value="{{ $combo_product['variation_id'] }}">

                        <input type="hidden" class="combo_product_qty"
                            name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                            data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

                        @if (isset($action) && $action == 'edit')
                            <input type="hidden"
                                name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                                value="{{ $combo_product['id'] }}">
                        @endif
                    @endforeach
                @endif
            </td>
            
             <td>
                <input type="number" min="1" value="1" oninput="this.value = Math.abs(this.value)"
                    class="form-control qty_flex" name="products[{{ $row_count }}][qty_flex]"
                    value="{{ $product->qty_flex }}">
            </td>
        @elseif(!empty($enable_flex->enable_is_flex && $product->is_flex))
            @if ($enable_flex->enable_is_flex && $product->is_flex)
                <td>
                    <span style="font-weight: bold">W =</span> <input type="number" min="1"
                     class="form-control is_flex_width"
                        name="products[{{ $row_count }}][width]" value="{{ $product->width }}"
                        style="width:100px; margin-left:40px; margin-top:-24px">
                    <span style="font-weight: bold">H =</span> <input type="number" min="1"
                class="form-control is_flex_height"
                        name="products[{{ $row_count }}][height]" value="{{ $product->height }}"
                        style="width:100px; margin-left:40px; margin-top:-12px">
                </td>
                <td>
                    {{-- If edit then transaction sell lines will be present --}}
                    @if (!empty($product->transaction_sell_lines_id))
                        <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                            class="form-control" value="{{ $product->transaction_sell_lines_id }}">
                    @endif

                    <input type="hidden" name="products[{{ $row_count }}][product_id]"
                        class="form-control product_id" value="{{ $product->product_id }}">

                    <input type="hidden" value="{{ $product->variation_id }}"
                        name="products[{{ $row_count }}][variation_id]" class="row_variation_id">

                    <input type="hidden" value="{{ $product->enable_stock }}"
                        name="products[{{ $row_count }}][enable_stock]">

                    @if (empty($product->quantity_ordered))
                        @php
                            $product->quantity_ordered = 1;
                        @endphp
                    @endif

                    @php
                        $allow_decimal = true;
                        if ($product->unit_allow_decimal != 1) {
                            $allow_decimal = false;
                        }
                    @endphp
                    @foreach ($sub_units as $key => $value)
                        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                            @php
                                $max_qty_rule = $max_qty_rule / $multiplier;
                                $unit_name = $value['name'];
                                $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                                
                                if (!empty($product->lot_no_line_id)) {
                                    $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                                }
                                
                                if ($value['allow_decimal']) {
                                    $allow_decimal = true;
                                }
                            @endphp
                        @endif
                    @endforeach
                    <div class="input-group input-number">
                        {{-- <span class="input-group-btn"><button type="button"
                                class="btn btn-default btn-flat quantity-down"><i
                                    class="fa fa-minus text-danger"></i></button></span> --}}
                        <input type="text" data-min="1"
                            class="form-control pos_quantity input_number mousetrap input_quantity"
                            value="{{ @format_quantity($product->quantity_ordered) }}"
                            name="products[{{ $row_count }}][quantity]"
                            data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                            @if ($allow_decimal) data-decimal=1 
                        @else 
                            data-decimal=0 
                            data-rule-abs_digit="true" 
                            data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif
                            data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                            @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" 
                            data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif>
                        {{-- <span class="input-group-btn"><button type="button"
                                class="btn btn-default btn-flat quantity-up"><i
                                    class="fa fa-plus text-success"></i></button></span> --}}
                    </div>

                    <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
                        value="{{ $product->unit_id }}">
                    @if (count($sub_units) > 0)
                        <br>
                        <select name="products[{{ $row_count }}][sub_unit_id]"
                            class="form-control input-sm sub_unit">
                            @foreach ($sub_units as $key => $value)
                                <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                                    data-unit_name="{{ $value['name'] }}"
                                    data-allow_decimal="{{ $value['allow_decimal'] }}"
                                    @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                                    {{ $value['name'] }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        {{ $product->unit }}
                    @endif

                    <input type="hidden" class="base_unit_multiplier"
                        name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">

                    <input type="hidden" class="hidden_base_unit_sell_price"
                        value="{{ $product->default_sell_price / $multiplier }}">

                    {{-- Hidden fields for combo products --}}
                    @if ($product->product_type == 'combo' && !empty($product->combo_products))

                        @foreach ($product->combo_products as $k => $combo_product)
                            @if (isset($action) && $action == 'edit')
                                @php
                                    $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                                    
                                    $qty_total = $combo_product['quantity'];
                                @endphp
                            @else
                                @php
                                    $qty_total = $combo_product['qty_required'];
                                @endphp
                            @endif

                            <input type="hidden"
                                name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                                value="{{ $combo_product['product_id'] }}">

                            <input type="hidden"
                                name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                                value="{{ $combo_product['variation_id'] }}">

                            <input type="hidden" class="combo_product_qty"
                                name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                                data-unit_quantity="{{ $combo_product['qty_required'] }}"
                                value="{{ $qty_total }}">

                            @if (isset($action) && $action == 'edit')
                                <input type="hidden"
                                    name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                                    value="{{ $combo_product['id'] }}">
                            @endif
                        @endforeach
                    @endif
                </td>
                
<td>

                    <input type="number" min="1" oninput="this.value = Math.abs(this.value)"
                        class="form-control qty_flex" name="products[{{ $row_count }}][qty_flex]"
                        value="{{ $product->qty_flex }}">
                </td>
            @endif
        @else
        @if (!empty($enable_flex->enable_is_flex))
            <td></td>
            <td></td>
            <td>
            {{-- If edit then transaction sell lines will be present --}}
            @if (!empty($product->transaction_sell_lines_id))
                <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                    class="form-control" value="{{ $product->transaction_sell_lines_id }}">
            @endif

            <input type="hidden" name="products[{{ $row_count }}][product_id]"
                class="form-control product_id" value="{{ $product->product_id }}">

            <input type="hidden" value="{{ $product->variation_id }}"
                name="products[{{ $row_count }}][variation_id]" class="row_variation_id">

            <input type="hidden" value="{{ $product->enable_stock }}"
                name="products[{{ $row_count }}][enable_stock]">

            @if (empty($product->quantity_ordered))
                @php
                    $product->quantity_ordered = 1;
                @endphp
            @endif

            @php
                $allow_decimal = true;
                if ($product->unit_allow_decimal != 1) {
                    $allow_decimal = false;
                }
            @endphp
            @foreach ($sub_units as $key => $value)
                @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                    @php
                        $max_qty_rule = $max_qty_rule / $multiplier;
                        $unit_name = $value['name'];
                        $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        
                        if (!empty($product->lot_no_line_id)) {
                            $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        }
                        
                        if ($value['allow_decimal']) {
                            $allow_decimal = true;
                        }
                    @endphp
                @endif
            @endforeach
            <div class="input-group input-number">
                <span class="input-group-btn"><button type="button"
                        class="btn btn-default btn-flat quantity-down"><i
                            class="fa fa-minus text-danger"></i></button></span>
                <input type="text" data-min="1"
                    class="form-control pos_quantity input_number mousetrap input_quantity"
                    value="{{ @format_quantity($product->quantity_ordered) }}"
                    name="products[{{ $row_count }}][quantity]"
                    data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                    @if ($allow_decimal) data-decimal=1 
            @else 
                data-decimal=0 
                data-rule-abs_digit="true" 
                data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif
                    data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                    @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" 
                data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif>
                <span class="input-group-btn"><button type="button"
                        class="btn btn-default btn-flat quantity-up"><i
                            class="fa fa-plus text-success"></i></button></span>
            </div>

            <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
                value="{{ $product->unit_id }}">
            @if (count($sub_units) > 0)
                <br>
                <select name="products[{{ $row_count }}][sub_unit_id]"
                    class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                        <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                            data-unit_name="{{ $value['name'] }}"
                            data-allow_decimal="{{ $value['allow_decimal'] }}"
                            @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                            {{ $value['name'] }}
                        </option>
                    @endforeach
                </select>
            @else
                {{ $product->unit }}
            @endif

            <input type="hidden" class="base_unit_multiplier"
                name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">

            <input type="hidden" class="hidden_base_unit_sell_price"
                value="{{ $product->default_sell_price / $multiplier }}">

            {{-- Hidden fields for combo products --}}
            @if ($product->product_type == 'combo' && !empty($product->combo_products))

                @foreach ($product->combo_products as $k => $combo_product)
                    @if (isset($action) && $action == 'edit')
                        @php
                            $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                            
                            $qty_total = $combo_product['quantity'];
                        @endphp
                    @else
                        @php
                            $qty_total = $combo_product['qty_required'];
                        @endphp
                    @endif

                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                        value="{{ $combo_product['product_id'] }}">

                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                        value="{{ $combo_product['variation_id'] }}">

                    <input type="hidden" class="combo_product_qty"
                        name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                        data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

                    @if (isset($action) && $action == 'edit')
                        <input type="hidden"
                            name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                            value="{{ $combo_product['id'] }}">
                    @endif
                @endforeach
            @endif
         </td>
       
        @else
        <td>
            {{-- If edit then transaction sell lines will be present --}}
            @if (!empty($product->transaction_sell_lines_id))
                <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                    class="form-control" value="{{ $product->transaction_sell_lines_id }}">
            @endif
    
            <input type="hidden" name="products[{{ $row_count }}][product_id]" class="form-control product_id"
                value="{{ $product->product_id }}">
    
            <input type="hidden" value="{{ $product->variation_id }}"
                name="products[{{ $row_count }}][variation_id]" class="row_variation_id">
    
            <input type="hidden" value="{{ $product->enable_stock }}"
                name="products[{{ $row_count }}][enable_stock]">
    
            @if (empty($product->quantity_ordered))
                @php
                    $product->quantity_ordered = 1;
                @endphp
            @endif
    
            @php
                $allow_decimal = true;
                if ($product->unit_allow_decimal != 1) {
                    $allow_decimal = false;
                }
            @endphp
            @foreach ($sub_units as $key => $value)
                @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                    @php
                        $max_qty_rule = $max_qty_rule / $multiplier;
                        $unit_name = $value['name'];
                        $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        
                        if (!empty($product->lot_no_line_id)) {
                            $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        }
                        
                        if ($value['allow_decimal']) {
                            $allow_decimal = true;
                        }
                    @endphp
                @endif
            @endforeach
            <div class="input-group input-number">
                <span class="input-group-btn"><button type="button"
                        class="btn btn-default btn-flat quantity-down"><i
                            class="fa fa-minus text-danger"></i></button></span>
                <input type="text" data-min="1"
                    class="form-control pos_quantity input_number mousetrap input_quantity"
                    value="{{ @format_quantity($product->quantity_ordered) }}"
                    name="products[{{ $row_count }}][quantity]"
                    data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                    @if ($allow_decimal) data-decimal=1 
            @else 
                data-decimal=0 
                data-rule-abs_digit="true" 
                data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif
                    data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                    @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" 
                data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif>
                <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i
                            class="fa fa-plus text-success"></i></button></span>
            </div>
    
            <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
                value="{{ $product->unit_id }}">
            @if (count($sub_units) > 0)
                <br>
                <select name="products[{{ $row_count }}][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                        <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                            data-unit_name="{{ $value['name'] }}"
                            data-allow_decimal="{{ $value['allow_decimal'] }}"
                            @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                            {{ $value['name'] }}
                        </option>
                    @endforeach
                </select>
            @else
                {{ $product->unit }}
            @endif
    

            <input type="hidden" class="base_unit_multiplier"
                name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">
    
            <input type="hidden" class="hidden_base_unit_sell_price"
                value="{{ $product->default_sell_price / $multiplier }}">
    
            {{-- Hidden fields for combo products --}}
            @if ($product->product_type == 'combo' && !empty($product->combo_products))
    
                @foreach ($product->combo_products as $k => $combo_product)
                    @if (isset($action) && $action == 'edit')
                        @php
                            $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                            
                            $qty_total = $combo_product['quantity'];
                        @endphp
                    @else
                        @php
                            $qty_total = $combo_product['qty_required'];
                        @endphp
                    @endif
    
                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                        value="{{ $combo_product['product_id'] }}">
    
                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                        value="{{ $combo_product['variation_id'] }}">
    
                    <input type="hidden" class="combo_product_qty"
                        name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                        data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">
    
                    @if (isset($action) && $action == 'edit')
                        <input type="hidden"
                            name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                            value="{{ $combo_product['id'] }}">
                    @endif
                @endforeach
            @endif
        </td> 
        @endif
            

        @endif
    @elseif (empty($enable_flex->enable_is_flex))
    <td>
        {{-- If edit then transaction sell lines will be present --}}
        @if (!empty($product->transaction_sell_lines_id))
            <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                class="form-control" value="{{ $product->transaction_sell_lines_id }}">
        @endif

        <input type="hidden" name="products[{{ $row_count }}][product_id]" class="form-control product_id"
            value="{{ $product->product_id }}">

        <input type="hidden" value="{{ $product->variation_id }}"
            name="products[{{ $row_count }}][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{ $product->enable_stock }}"
            name="products[{{ $row_count }}][enable_stock]">

        @if (empty($product->quantity_ordered))
            @php
                $product->quantity_ordered = 1;
            @endphp
        @endif

        @php
            $allow_decimal = true;
            if ($product->unit_allow_decimal != 1) {
                $allow_decimal = false;
            }
        @endphp
        @foreach ($sub_units as $key => $value)
            @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                @php
                    $max_qty_rule = $max_qty_rule / $multiplier;
                    $unit_name = $value['name'];
                    $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                    
                    if (!empty($product->lot_no_line_id)) {
                        $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                    }
                    
                    if ($value['allow_decimal']) {
                        $allow_decimal = true;
                    }
                @endphp
            @endif
        @endforeach
        <div class="input-group input-number">
			<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
		<input type="text" data-min="1" 
			class="form-control pos_quantity input_number mousetrap input_quantity" 
			value="{{@format_quantity($product->quantity_ordered)}}" name="products[{{$row_count}}][quantity]" data-allow-overselling="@if(empty($pos_settings['allow_overselling'])){{'false'}}@else{{'true'}}@endif" 
			@if($allow_decimal) 
				data-decimal=1 
			@else 
				data-decimal=0 
				data-rule-abs_digit="true" 
				data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" 
			@endif
			data-rule-required="true" 
			data-msg-required="@lang('validation.custom-messages.this_field_is_required')" 
			
{{-- 	
			@if($checkcount > 0)
			 data-qty_available="{{$product->qty_available}}"
				
				data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" 
			

			@else --}}
            
            @if($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order) )
				data-rule-max-value="{{$max_qty_rule}}" data-qty_available="{{$product->qty_available}}"
				 data-msg-max-value="{{$max_qty_msg}}" 
				data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" 
			@endif
		>
		<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
		</div>

        <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
            value="{{ $product->unit_id }}">
        @if (count($sub_units) > 0)
            <br>
            <select name="products[{{ $row_count }}][sub_unit_id]" class="form-control input-sm sub_unit">
                @foreach ($sub_units as $key => $value)
                    <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                        data-unit_name="{{ $value['name'] }}"
                        data-allow_decimal="{{ $value['allow_decimal'] }}"
                        @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{ $value['name'] }}
                    </option>
                @endforeach
            </select>
        @else
            {{ $product->unit }}
        @endif

<!--//saad-->
        <input type="hidden" class="previous_quantity" name="previou_quanity" value="{{@format_quantity($product->quantity_ordered)}}">
<!--saad-->
        <input type="hidden" class="base_unit_multiplier"
            name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">

        <input type="hidden" class="hidden_base_unit_sell_price"
            value="{{ $product->default_sell_price / $multiplier }}">

        {{-- Hidden fields for combo products --}}
        @if ($product->product_type == 'combo' && !empty($product->combo_products))

            @foreach ($product->combo_products as $k => $combo_product)
                @if (isset($action) && $action == 'edit')
                    @php
                        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                        
                        $qty_total = $combo_product['quantity'];
                    @endphp
                @else
                    @php
                        $qty_total = $combo_product['qty_required'];
                    @endphp
                @endif

                <input type="hidden"
                    name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                    value="{{ $combo_product['product_id'] }}">

                <input type="hidden"
                    name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                    value="{{ $combo_product['variation_id'] }}">

                <input type="hidden" class="combo_product_qty"
                    name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                    data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

                @if (isset($action) && $action == 'edit')
                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                        value="{{ $combo_product['id'] }}">
                @endif
            @endforeach
        @endif
    </td> 
    @else
        <td>
            {{-- If edit then transaction sell lines will be present --}}
            @if (!empty($product->transaction_sell_lines_id))
                <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]"
                    class="form-control" value="{{ $product->transaction_sell_lines_id }}">
            @endif

            <input type="hidden" name="products[{{ $row_count }}][product_id]" class="form-control product_id"
                value="{{ $product->product_id }}">

            <input type="hidden" value="{{ $product->variation_id }}"
                name="products[{{ $row_count }}][variation_id]" class="row_variation_id">

            <input type="hidden" value="{{ $product->enable_stock }}"
                name="products[{{ $row_count }}][enable_stock]">

            @if (empty($product->quantity_ordered))
                @php
                    $product->quantity_ordered = 1;
                @endphp
            @endif

            @php
                $allow_decimal = true;
                if ($product->unit_allow_decimal != 1) {
                    $allow_decimal = false;
                }
            @endphp
            @foreach ($sub_units as $key => $value)
                @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
                    @php
                        $max_qty_rule = $max_qty_rule / $multiplier;
                        $unit_name = $value['name'];
                        $max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        
                        if (!empty($product->lot_no_line_id)) {
                            $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty' => $max_qty_rule, 'unit' => $unit_name]);
                        }
                        
                        if ($value['allow_decimal']) {
                            $allow_decimal = true;
                        }
                    @endphp
                @endif
            @endforeach
            <div class="input-group input-number">
                <span class="input-group-btn"><button type="button"
                        class="btn btn-default btn-flat quantity-down"><i
                            class="fa fa-minus text-danger"></i></button></span>
                <input type="text" data-min="1"
                    class="form-control pos_quantity input_number mousetrap input_quantity"
                    value="{{ @format_quantity($product->quantity_ordered) }}"
                    name="products[{{ $row_count }}][quantity]"
                    data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) {{ 'false' }}@else{{ 'true' }} @endif"
                    @if ($allow_decimal) data-decimal=1 
			@else 
				data-decimal=0 
				data-rule-abs_digit="true" 
				data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif
                    data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')"
                    @if ($product->enable_stock && empty($pos_settings['allow_overselling']) && empty($is_sales_order)) data-rule-max-value="{{ $max_qty_rule }}" data-qty_available="{{ $product->qty_available }}" data-msg-max-value="{{ $max_qty_msg }}" 
				data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif>
                <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i
                            class="fa fa-plus text-success"></i></button></span>
            </div>

            <input type="hidden" name="products[{{ $row_count }}][product_unit_id]"
                value="{{ $product->unit_id }}">
            @if (count($sub_units) > 0)
                <br>
                <select name="products[{{ $row_count }}][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach ($sub_units as $key => $value)
                        <option value="{{ $key }}" data-multiplier="{{ $value['multiplier'] }}"
                            data-unit_name="{{ $value['name'] }}"
                            data-allow_decimal="{{ $value['allow_decimal'] }}"
                            @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                            {{ $value['name'] }}
                        </option>
                    @endforeach
                </select>
            @else
                {{ $product->unit }}
            @endif

            <input type="hidden" class="base_unit_multiplier"
                name="products[{{ $row_count }}][base_unit_multiplier]" value="{{ $multiplier }}">

            <input type="hidden" class="hidden_base_unit_sell_price"
                value="{{ $product->default_sell_price / $multiplier }}">

            {{-- Hidden fields for combo products --}}
            @if ($product->product_type == 'combo' && !empty($product->combo_products))

                @foreach ($product->combo_products as $k => $combo_product)
                    @if (isset($action) && $action == 'edit')
                        @php
                            $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;
                            
                            $qty_total = $combo_product['quantity'];
                        @endphp
                    @else
                        @php
                            $qty_total = $combo_product['qty_required'];
                        @endphp
                    @endif

                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][product_id]"
                        value="{{ $combo_product['product_id'] }}">

                    <input type="hidden"
                        name="products[{{ $row_count }}][combo][{{ $k }}][variation_id]"
                        value="{{ $combo_product['variation_id'] }}">

                    <input type="hidden" class="combo_product_qty"
                        name="products[{{ $row_count }}][combo][{{ $k }}][quantity]"
                        data-unit_quantity="{{ $combo_product['qty_required'] }}" value="{{ $qty_total }}">

                    @if (isset($action) && $action == 'edit')
                        <input type="hidden"
                            name="products[{{ $row_count }}][combo][{{ $k }}][transaction_sell_lines_id]"
                            value="{{ $combo_product['id'] }}">
                    @endif
                @endforeach
            @endif
        </td>
        <td></td>
        <td></td>

    @endif
    
    

    @if (!empty($is_direct_sell))
        @if (!empty($pos_settings['inline_service_staff']))
            <td>
                <div class="form-group">
                    <div class="input-group">
                        {!! Form::select(
                            'products[' . $row_count . '][res_service_staff_id]',
                            $waiters,
                            !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                            [
                                'class' => 'form-control select2 order_line_service_staff',
                                'placeholder' => __('restaurant.select_service_staff'),
                                'required' =>
                                    !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                                        ? true
                                        : false,
                            ],
                        ) !!}
                    </div>
                </div>
            </td>
        @endif
        @php
        
            $pos_unit_price = !empty($product->unit_price_before_discount) ? $product->unit_price_before_discount: 
                                (!empty($product->default_sell_price)
                                ? $product->default_sell_price
                                : (!empty($product->default_single_dollar_dsp)
                                    ? $product->default_single_dollar_dsp
                                    : $product->default_single_aud_dsp));

                        if (!empty($so_line)) { 
                            $pos_unit_price = $so_line->unit_price_before_discount;
                        }
                    @endphp
                    
           
        <td class="{{$hide_purchase_inc}}">
            <input type="text" readonly ="products[{{ $row_count }}][dpp_inc_tax]"
                class="form-control input_number mousetrap"
                value="{{ @num_format($product->dpp_inc_tax) }}" >
        </td>
        
        <td class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
            <input type="text" name="products[{{ $row_count }}][unit_price]"
                class="form-control pos_unit_price input_number mousetrap"
                value="{{ @num_format($pos_unit_price) }}"

                @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>
        </td>
        <td @if (!$edit_discount) class="hide" @endif>
            {!! Form::text("products[$row_count][line_discount_amount]", @num_format($discount_amount), [
                'class' => 'form-control input_number row_discount_amount',
            ]) !!}<br>
            {!! Form::select(
                "products[$row_count][line_discount_type]",
                ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
                $discount_type,
                ['class' => 'form-control row_discount_type'],
            ) !!}
            @if (!empty($discount))
                <p class="help-block">{!! __('lang_v1.applied_discount_text', [
                    'discount_name' => $discount->name,
                    'starts_at' => $discount->formated_starts_at,
                    'ends_at' => $discount->formated_ends_at,
                ]) !!}</p>
            @endif
        </td>
        <td class="text-center {{ $hide_tax }}">
            {!! Form::hidden("products[$row_count][item_tax]", @num_format($item_tax), ['class' => 'item_tax']) !!}

            {!! Form::select(
                "products[$row_count][tax_id]",
                $tax_dropdown['tax_rates'],
                $tax_id,
                ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
                $tax_dropdown['attributes'],
            ) !!}
        </td>
    @else
        @if (!empty($pos_settings['inline_service_staff']))
            <td>
                <div class="form-group">
                    <div class="input-group">
                        {!! Form::select(
                            'products[' . $row_count . '][res_service_staff_id]',
                            $waiters,
                            !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null,
                            [
                                'class' => 'form-control select2 order_line_service_staff',
                                'placeholder' => __('restaurant.select_service_staff'),
                                'required' =>
                                    !empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1
                                        ? true
                                        : false,
                            ],
                        ) !!}
                    </div>
                </div>
            </td>
        @endif
    @endif
    <td class="{{ $hide_tax }}">
        	@php
    $currency_precision = session('business.currency_precision', 2);
	
  @endphp
  <input type="hidden" name="get_currency_percission" class="form-control get_currency_percission input_number" value="{{$currency_precision}}" >
	  
        <input type="text" name="products[{{ $row_count }}][unit_price_inc_tax]"
            class="form-control pos_unit_price_inc_tax input_number" value="{{ @num_format($unit_price_inc_tax) }}"
            @if (!$edit_price) readonly @endif
            @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $unit_price_inc_tax }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)]) }}" @endif>
    </td>
    @if (!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
        <td>
            {!! Form::select("products[$row_count][warranty_id]", $warranties, $warranty_id, [
                'placeholder' => __('messages.please_select'),
                'class' => 'form-control',
            ]) !!}
        </td>
    @endif
    <?php
    $Unlimited = 'Un-limited';
    ?>
    @if (!empty($pos_settings['enable_pos_whole_sale_screen']))
        <td class="text-center">
            <input type="text" class="form-control @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif"
                @if ($product->qty_available == '') value="{{ $Unlimited }}" @else value="{{ @num_format($product->qty_available) }}" @endif
                readonly>
        </td>
    @endif
    @if (!empty($pos_settings['enable_pos_whole_sale_screen']))
        <div class="form-group col-xs-12 @if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
            <td class="text-center">
                @php
                    $pos_unit_price = !empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price;
                @endphp
                {{-- <label>@lang('sale.unit_price')</label> --}}
                <input type="text" name="products[{{ $row_count }}][unit_price]"
                    class="form-control pos_unit_price input_number mousetrap"
                    value="{{ @num_format($pos_unit_price) }}"
                    @if (!empty($pos_settings['enable_msp'])) data-rule-min-value="{{ $pos_unit_price }}" data-msg-min-value="{{ __('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)]) }}" @endif>
        </div>
        @if (!auth()->user()->can('edit_product_price_from_sale_screen'))
            <div class="form-group col-xs-12">
                <strong>@lang('sale.unit_price'):</strong>
                {{ @num_format(!empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price) }}
            </div>
        @endif
        </td>

        </div>
    @endif

    @if (!empty($pos_settings['enable_pos_whole_sale_screen']))
        <td>
            <div>

                <div class=" col-xs-12 col-sm-12 @if (!$edit_discount) hide @endif"
                    style="width: 124px;">
                    {{-- <label>@lang('sale.discount_type')</label> --}}
                    {!! Form::select(
                        "products[$row_count][line_discount_type]",
                        ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
                        $discount_type,
                    
                        ['class' => 'form-control row_discount_type'],
                    ) !!}
                </div>
                <div class=" col-xs-12 col-sm-12 @if (!$edit_discount) hide @endif">
                    {{-- <label>@lang('sale.discount_amount')</label> --}}
                    {!! Form::text("products[$row_count][line_discount_amount]", @num_format($discount_amount), [
                        'class' => 'form-control input_number row_discount_amount',
                    ]) !!}
                </div>
            </div>

        </td>
    @endif
    {{-- whole pos --}}
    <td class="text-center">
        @php
            $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';
            
        @endphp
        <input type="{{ $subtotal_type }}"
            class="form-control pos_line_total @if (!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif"
            value="{{ @num_format($product->quantity_ordered * $unit_price_inc_tax) }}">
        <span class="display_currency pos_line_total_text @if (!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif"
            data-currency_symbol="true">{{ $product->quantity_ordered * $unit_price_inc_tax }}</span>
    </td>
    <td class="text-center v-center">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
    <td class="{{ $hide_tax }}">
        <input type="hidden" name="products[{{ $row_count }}][item_tax]" class="form-control item_tax">

        {!! Form::select(
            "products[$row_count][tax_id]",
            $tax_dropdown['tax_rates'],
            $tax_id,
            ['placeholder' => 'Select', 'class' => 'form-control tax_id'],
            $tax_dropdown['attributes'],
        ) !!}
    </td>
    
</tr>
