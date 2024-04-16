@extends('layouts.app')
@section('title', 'Service Staff Order Reports')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> Service Staff Order Reports </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('ssr_location_id', __('purchase.business_location') . ':') !!}
                            {!! Form::select('ssr_location_id', $business_locations, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label> User Name</label>
                        <select class="form-control user_id" placeholder="" id="user_id">
                            <option value="" selected>All</option>
                            @foreach ($users as $user)
                                <option id="user_id" value="{{ $user->id }}">{{ $user->first_name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-3">
                        <label> Category Name</label>
                        <select class="form-control user_id" placeholder="" id="category">
                            <option value="" selected>All</option>
                            @foreach ($category as $category)
                                <option id="user_id" value="{{ $category->id }}">{{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-3 mt-3">

                        <label>Date Range</label>
                        <input class="form-control form-control-solid date_range" onchange="datepicker()"
                            placeholder="Pick date rage" id="kt_daterangepicker_4" name="date_range" value="" />
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="oder_reports">
                            <thead>
                                <tr>
                                    <th>User name</th>
                                    <!--<th>Customer Name</th>-->
                                    <th>Product Name</th>
                                    <th>Category Name</th>
                                    <th>Location</th>
                                    <th>Sku</th>
                                    <th>Product Qty</th>
                                    <th>Total</th>
                                    <!--<th>Return</th>-->
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="5"><strong>Total Qty</strong></td>
                                    <td><span class="" id="sell_qty" data-currency_symbol=""></span></td>
                                    <td><span class="" id="total" data-currency_symbol=""></span></td>
                                  
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
@endsection
<!-- /.content -->
@section('javascript')
    <script>
        $(document).ready(function() {
            $("#category").select2();
            $("#user_id").select2();
            $('#oder_reports').DataTable({

                processing: true,
                // serverSide: true,
                ajax: {
                    url: "/reports/oder_reports",
                    data: function(d) {
                        d.location_id = location_id
                        d.user_id = user_id
                        d.category = category
                        d.start_date = start_date,
                            d.end_date = end_date

                    }

                },
                data: {

                    'location_id': location_id
                },
                data: {

                    'user_id': user_id
                },
                data: {

                    'category': category
                },

                columns: [{
                        data: 'waiter',
                        name: 'waiter',
                    },
                    // {
                    //     data: 'customer',
                    //     name: 'customer'
                    // },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'location_name',
                        name: 'location_name',
                    },


                    {
                        data: 'sub_sku',
                        name: 'sub_sku',
                    },
                    {
                        data: 'sell_qty',
                        name: 'sell_qty',
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                    },
                    // {
                    //     data:"";
                    //     name: "",
                    // },



                ],
                "fnDrawCallback": function(oSettings) {
                    $('#sell_qty').text(sum_table_col($('#oder_reports'), 'sell_qty'));
                    $('#total').text(sum_table_col($('#oder_reports'), 'subtotal'));
                    __currency_convert_recursively($('#oder_reports'));
                }
            });
            var location_id = null;
            $('#ssr_location_id').on('change', function() {
                location_id = $(this).find(":selected").val();
                $("#oder_reports").DataTable().ajax.reload();

            });
            var user_id = null;
            $('#user_id').on('change', function() {
                user_id = $(this).find(":selected").val();
                $("#oder_reports").DataTable().ajax.reload();

            });
            var category = null;
            $('#category').on('change', function() {
                category = $(this).find(":selected").val();
                $("#oder_reports").DataTable().ajax.reload();

            });


        });
        var start_date = '';
        var end_date = '';

        function datepicker() {
            var date_range = $('input[name=date_range]').val();
            var data_split = date_range.split("-");
            start_date = data_split[0];
            end_date = data_split[1];
            $("#oder_reports").DataTable().ajax.reload();
        }
        $(document).ready(function() {


            var start = moment().subtract(29, "days");
            var end = moment();

            function cb(start, end) {
                $("#kt_daterangepicker_4").html(start.format("MMMM D, YYYY") + " - " + end.format(
                    "MMMM D, YYYY"));
            }

            $("#kt_daterangepicker_4").daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {

                    "Today": [moment(), moment()],
                    "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "Last 7 Days": [moment().subtract(6, "days"), moment()],
                    "Last 30 Days": [moment().subtract(29, "days"), moment()],
                    "This Month": [moment().startOf("month"), moment().endOf("month")],
                    "Last Month": [moment().subtract(1, "month").startOf("month"), moment()
                        .subtract(1,
                            "month").endOf(
                            "month")
                    ],
                    "This Year": [moment().startOf("year"), moment().endOf("year")]
                }

            }, cb);

            cb(start, end);
        });
    </script>
@endsection
