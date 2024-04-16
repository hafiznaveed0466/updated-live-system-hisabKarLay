@if (!empty($pos_settings['enable_pos_whole_sale_screen']))
    <div class="payment_rows_div">
        {{-- <div class="pos-form-actions"> --}}
        <div class="col-md-12">
            <div class=" box-solid bg-orange">
                <div class="box-body">
                    <div class="col-md-12">
                        <strong>
                            @lang('lang_v1.total_items'):
                        </strong>
                        <br />
                        <span class="lead text-bold total_quantity">0</span>
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <strong>
                            @lang('sale.total_payable'):
                        </strong>
                        <br />
                        <span class="lead text-bold total_payable_span">0</span>
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <strong>
                            @lang('lang_v1.total_paying'):
                        </strong>
                        <br />
                        <span class="lead text-bold total_paying">0</span>
                        <input type="hidden" class="total_paying_input">
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <strong>
                            @lang('lang_v1.change_return'):
                        </strong>
                        <br />
                        <span class="lead text-bold change_return_span">0</span>
                        {!! Form::hidden('change_return', $change_return['amount'], [
                            'class' => 'form-control change_return input_number',
                            'required',
                            'id' => 'change_return',
                        ]) !!}
                     {{-- <span class="lead text-bold total_quantity">0</span> --}}
                            
                        @if (!empty($change_return['id']))
                            <input type="hidden" name="change_return_id" value="{{ $change_return['id'] }}">
                        @endif
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <strong>
                            @lang('lang_v1.balance'):
                        </strong>
                        <br />
                        <span class="lead text-bold balance_due">0</span>
                        <input type="hidden" id="in_balance_due" value=0>
                    </div>



                    @php
                        $row_index = null;
                    @endphp
                    <div class="col-md-12">

                        <div class="form-group">
                            {!! Form::label("amount_$row_index", __('sale.amount') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                </span>
                                <input type="number" name="payment ",
                                    class='form-control net_cash payment-amount input_number' id='net_cash'>
                                <input type="hidden" name="method" value={{ 'cash' }}>
                                {{-- {{!! Form::text("payment[$row_index][amount]", ['class' => 'form-control payment-amount input_number', 'required', 'id' => "amount_$row_index", 'placeholder' => __('sale.amount'), 'readonly']); !!} --}}
                            </div>
                        </div>
                    </div>

                    <!-- /.box-body -->




                    <button type="submit" class="btn btn-primary" id="pos-save">@lang('sale.finalize_payment')</button>
                </div>
            </div>
        </div>
    </div>
@else
    @if (empty($pos_settings['hide_product_suggestion']))
        {{-- whole sale code end --}}
        <div class="row" id="featured_products_box" style="display: none;">
            @if (!empty($featured_products))
                @include('sale_pos.partials.featured_products')
            @endif
        </div>
        <div class="row">
            @if (!empty($categories))

                <div class="col-md-4" id="product_category_div">
                    <select class="select2" id="product_category" style="width:100% !important">
                        <option value="all">@lang('lang_v1.all_category')</option>

                        @foreach ($categories as $category)
                          
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                           
                        @endforeach

                     

                        @foreach ($categories as $category)
                            @if (!empty($category['sub_categories']))
                                <optgroup label="{{ $category['name'] }}">
                                    @foreach ($category['sub_categories'] as $sc)
                                        <i class="fa fa-minus"></i>
                                        <option value="{{ $sc['id'] }}">{{ $sc['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif

            @if (!empty($brands))
                <div class="col-sm-4" id="product_brand_div">
                    {!! Form::select('size', $brands, null, [
                        'id' => 'product_brand',
                        'class' => 'select2',
                        'name' => null,
                        'style' => 'width:100% !important',
                    ]) !!}
                </div>
            @endif

            <!-- used in repair : filter for service/product -->
            <div class="col-md-6 hide" id="product_service_div">
                {!! Form::select(
                    'is_enabled_stock',
                    ['' => __('messages.all'), 'product' => __('sale.product'), 'service' => __('lang_v1.service')],
                    null,
                    ['id' => 'is_enabled_stock', 'class' => 'select2', 'name' => null, 'style' => 'width:100% !important'],
                ) !!}
            </div>

            <div class="col-sm-4 @if (empty($featured_products)) hide @endif" id="feature_product_div">
                <button type="button" class="btn btn-primary btn-flat"
                    id="show_featured_products">@lang('lang_v1.featured_products')</button>
            </div>
        </div>
        <br>
        <div id="category_div">

       </div>
        <input type="hidden" id="suggestion_page" value="1">
        <div class="col-md-12">
            <div class="eq-height-row" id="product_list_body"></div>
            <div class="col-md-12 text-center" id="suggestion_page_loader" style="display: none;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
    @endif
@endif

