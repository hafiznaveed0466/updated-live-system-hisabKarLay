@extends('layouts.app')
@section('title', 'CurrencyChange')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Currency Change
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' =>'Currency Change'])
    
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{action('App\Http\Controllers\CurrencyChangeController@create')}}" 
                        data-container=".currency_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        
        @can('brand.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="currencyss_table">
                    <thead>
                        <tr>
                            <th>Currency Name</th>
                            <th>Currency Change Rate In Pkr</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade currency_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
