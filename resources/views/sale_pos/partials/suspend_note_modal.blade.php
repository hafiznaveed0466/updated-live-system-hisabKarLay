<div class="modal fade" tabindex="-1" role="dialog" id="confirmSuspendModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('lang_v1.suspend_sale')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            {!! Form::label('additional_notes', __('lang_v1.suspend_note') . ':') !!}
                            {!! Form::textarea(
                                'additional_notes',
                                !empty($transaction->additional_notes) ? $transaction->additional_notes : null,
                                ['class' => 'form-control', 'rows' => '4'],
                            ) !!}
                            {!! Form::hidden('is_suspend', 0, ['id' => 'is_suspend']) !!}
                        </div>
                        {{-- @dd($transaction); --}}
                    </div>
                </div>
            </div>

            <div class="modal-footer row">
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary pos-suspend"
                        onclick="setInterval()"id="pos-suspend">@lang('messages.save')</button>
                </div>
                @if (!empty($pos_settings['show_kot_button']))
                    <div class="col-md-2">
                        <button style="width: 100%" type="button" id="pos_suspends" class="btn btn-info block">Kot
                            {{-- <i id="{{ $sale }} "
                            class="fas fa-arrow-alt-circle-down pos-suspend"  onclick="setInterval()"></i> --}}
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button id="pos_suspend_bill" style="width:100%" type="button"
                            class=" bill block btn btn-success">Bill
                            {{-- <i
                            class="fas fa-money-bill-alt pos-suspend" onclick="setInterval()"
                            aria-hidden="true"></i> --}}
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button id="pos_kot_bill" style="width:100%" type="button" class="block btn btn-info">Kot/Bill
                            {{-- <i class="fas fa-money-bill-alt pos-suspend" aria-hidden="true"></i> --}}
                        </button>
                    </div>
                @endif
                <div class="col-md-2">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="print_window" style="display: none">
    <div class="col-xs-12 text-center" style="margin-top: 0px">
        <h2>KOT</h2>
    </div>
    <div class="col-xs-12 text-center">
        <h5 class="customer12">
            <h5 class="date12"></h5>
            <h5 class="invoice12"></h5>

        </h5>

        <span class="table_name"></span><br>
        <span class="sevice_name"></span>
        {{-- <p>
            <br />
            <span class="pull-left text-left">
                <strong>
                    <p>Types Of Service:
                </strong>
                <span class="type_of_services"></span>
                <!-- Waiter info -->
        </p> --}}
        </span>
        </p>
    </div>
    <table class="table table-responsive  table-striped">
        <thead>
            <tr>
                <th scope="rowspan"><input checked="true" class="kot_item_checkbox" id="products_check" type="checkbox">
                </th>
                <th scope="rowspan"> Products</th>
                <th scope="rowspan">Quantity</th>
            </tr>
        </thead>

        <tbody class="sus" id="ye">
        </tbody>
    </table>
    <h4 class="note"></h4>
</div>

<div class="print_window_bill" style="display: none">
    <h2 class="text-center business">4 Tech Brothers</h2>
    <!-- business information here -->
    <div class="col-xs-12 text-center">
        <h5 class="address">
            <!-- Shop & Location Name  -->

        </h5>
        <h5 class="mobile">
            <h5 class="customer">
                <h5 class="status"></h5>
                <h5 class="date"></h5>

            </h5>
            <h3>Invoice</h3>
            <hr>
    </div>
    <table class="table table-responsive  table-striped">
        <thead>
            <tr>
                <th scope="col">Product</th>
                <th scope="col">Quantity</th>
                <th scope="col">Unit_price</th>
            </tr>
        </thead>
        <tbody class="billkot">
        </tbody>
    </table>
    <hr>
    <h4 style="text-align:left; float:left">Subtotal</h4>
    <h4 class="Subtotal" style="text-align:right; float:right"></h4>
</div>
<div class="print_window_bill_kot" style="display: none">

    <h2 class="text-center business_kot">4 Tech Brothers</h2>
    <!-- business information here -->
    <div class="col-xs-12 text-center">
        <h5 id="address_kot">
            <!-- Shop & Location Name  -->
        </h5>
        <h5 id="mobile_kot">
            <h5 id="customer_kot">
                <h5 id="status_kot"></h5>
                <h5 id="date_kot"></h5>

            </h5>
            <h3>Invoice</h3>
            <hr>
    </div>
    <table class="table table-responsive  table-striped">
        <thead>
            <tr>
                <th scope="col">Product</th>
                <th scope="col">Quantity</th>
                <th scope="col">Unit_price</th>
            </tr>
        </thead>
        <tbody id="billkot_kot">
        </tbody>
    </table>
    <hr>
    <h4 style="text-align:left; float:left">Subtotal</h4>
    <h4 id="Subtotal_kot" style="text-align:right; float:right"></h4>

    <br><br><br>

</div>
<div class="KOT_IDD" style="display: none">
    <div class="col-xs-12 text-center" style="margin-top: 0px">
        <h2>KOT</h2>
    </div>
    <div class="col-xs-12 text-center">
        <h5 class="customer12">
            <h5 class="date12"></h5>
            <h5 class="invoice12"></h5>

        </h5>

        <span class="table_name"></span><br>
        <span class="sevice_name"></span>

        </span>
        </p>
    </div>
    <table class="table table-responsive  table-striped">
        <thead>
            <tr>
                <th scope="rowspan"><input checked="true" class="kot_item_checkbox" id="products_check"
                        type="checkbox">
                </th>
                <th scope="rowspan"> Products</th>
                <th scope="rowspan">Quantity</th>
            </tr>
        </thead>
        <tbody class="sus" id="sus">
        </tbody>
    </table>
    <h4 class="note"></h4>

</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
       $('button#pos_suspends').click(function() {
		// alert("test");
        $('input#is_suspend').val(1);
        $('div#confirmSuspendModal').modal('hide');
        pos_form_obj.submit();
        $('input#is_suspend').val(0);
        $('.print_window').show();

         $.ajax({
            method: 'GET',
            url: '/kot_last',
            dataType: "json",
            success: function (data) {
                var s = data[0].transaction_date;
                var date = new Date(s)
                var d = date.getDate();
                var m = date.getMonth() + 1;
                var y = date.getFullYear();
                var f = '' + y + '-' + (m <= 9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d);
                $(".customer12").html('Customer:' + ' ' + data[0].name);
                $(".date12").html('Date:' + ' ' + f);
                $(".invoice12").html('Invoice_No:' + ' ' + data[0].invoice_no);
                if (data[0].tabel_name == null) {
                } else {
                    $(".table_name").html('Table Name:' + ' ' + data[0].tabel_name);
                }
                if (data[0].sname == null) {

                } else {
                    $(".sevice_name").html('Type of Service:' + ' ' + data[0].sname);
                }

                var res = '';
             
                var res = '';
                $.each(data, function (key, value) {
                    console.log(value);
                    if (value.var_name == "DUMMY") {
                        res +=
                            '<tr>' +
                            '<td> ' +
                            '<input checked="" class="custom_name" id="kot_item_checkbox_269" type="checkbox">' +
                            '</td>' +
                            '<td>' +
                            value.pro_name +
                            '</td>';

                        if (value.new_qty == 0) {
                            res += '<td>' + parseFloat(value.quantity) + ' P(cs)' + '</td>';

                        } else {
                            res += '<td>' + parseFloat(value.new_qty) + ' P(cs)' + '</td>';
                        }

                        res += '</tr>';
                    } else {
                        res +=
                            '<tr>' +
                            '<td> ' +
                            '<input checked="" class="custom_name" id="kot_item_checkbox_269" type="checkbox">' +
                            '</td>' +
                            '<td>' +
                            value.pro_name + '(' + value.var_name + ')' +
                            '</td>';

                        if (value.new_qty == 0) {
                            res += '<td>' + parseFloat(value.quantity) + ' P(cs)' + '</td>';

                        } else {
                            res += '<td>' + parseFloat(value.new_qty) + ' P(cs)' + '</td>';
                        }

                        res += '</tr>';
                    }
                    if (data[0].additional_notes == null) {
                        // ...
                    } else {
                        $(".note").html('NOTE:' + ' ' + data[0].additional_notes);
                    }
                });
                $('.sus').html(res);
                $('.print_window').printThis({

                });
                setTimeout(function () {
                    $('.print_window').hide();
                }, 5000);
                window.setTimeout(function () {
                    window.location.reload();
                }, 5000);
            }
        });
    });
    
    
    
    
   
</script>
