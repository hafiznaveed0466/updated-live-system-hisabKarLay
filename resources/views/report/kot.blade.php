@extends('layouts.app')
@section('title', __('Kot - Report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{{ __('Kot - Report') }}</h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    {{-- <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('al_users_filter', __( 'lang_v1.by' ) . ':') !!}
                        {!! Form::select('al_users_filter', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'al_users_filter', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div> --}}

                    {{-- <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('subject_type', __( 'lang_v1.subject_type' ) . ':') !!}
                        {!! Form::select('subject_type', $transaction_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'subject_type', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div> --}}

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('al_date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('al_date_filter', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="KOT_REPORTS">
                            <thead>
                                <tr>
                                    <th> Customer Name</th>
                                    <th> Inovice No</th>
                                    <th> Date</th>
                                    <th>Location</th>
                                    <th>Types Of Service</th>
                                    <th>Table</th>
                                    <th>Qty</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#al_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                $('#al_date_filter').val(
                    start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                );
                KOT_REPORTS.ajax.reload();
            });
            $('#al_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $('#al_date_filter').val('');
                KOT_REPORTS.ajax.reload();
            });

            KOT_REPORTS = $('table#KOT_REPORTS').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'desc']
                ],
                "ajax": {
                    "url": ' {{action([\App\Http\Controllers\ReportController::class, "getkot"])}}',
                    //  url: '/reports/product-sell-grouped-by',
                    "data": function(d) {
                        var start_date = '';
                        var end_date = '';
                        if ($('#al_date_filter').val()) {
                            d.start_date = $('input#al_date_filter')
                                .data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            d.end_date = $('input#al_date_filter')
                                .data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                        }

                        // d.user_id = $('#al_users_filter').val();
                        // d.subject_type = $('#subject_type').val();
                    }
                },
                columns: [
                    {
                        data: 'cname',
                        name: 'cname',
                        searchable: false,
                        orderable: false
                
                    
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no',
                         searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'location_id',
                        name: 'location_id',
                        searchable: false,
                        orderable: false
                      
                    },
                    {
                        data: 'name',
                        name: 'name',
                        searchable: false,
                        orderable: false
                    
                    },
                    {
                        data: 'table',
                        name: 'table',
                   
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'sell_qty',
                        name: 'sell_qty',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes',
                        searchable: false,
                        orderable: false
                    },
                ],
               
            });
            $(document).on('change', '#al_users_filter, #subject_type', function() {
                KOT_REPORTS.ajax.reload();
            })
        });
    </script>
@endsection
