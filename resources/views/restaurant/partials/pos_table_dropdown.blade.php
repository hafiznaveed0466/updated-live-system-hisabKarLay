<!--@if($tables_enabled)-->
<!--<div class="col-sm-4">-->
<!--	<div class="form-group">-->
<!--		<div class="input-group" id="res_table_id" style="display:none">-->
<!--			<span class="input-group-addon">-->
<!--				<i class="fa fa-table"></i>-->
<!--			</span>-->
<!--			{!! Form::select('res_table_id', $tables, $view_data['res_table_id'], ['class' => 'form-control', 'placeholder' => __('restaurant.select_table')]); !!}-->
<!--		</div>-->
<!--	</div>-->
<!--</div>-->
<!--@endif-->
<!--@if($waiters_enabled)-->
<!--<div class="col-sm-4">-->
<!--	<div class="form-group">-->
<!--		<div class="input-group">-->
<!--			<span class="input-group-addon">-->
<!--				<i class="fa fa-user-secret"></i>-->
<!--			</span>-->
<!--			{!! Form::select('res_waiter_id', $waiters, $view_data['res_waiter_id'], ['class' => 'form-control', 'placeholder' => __('restaurant.select_service_staff'), 'id' => 'res_waiter_id',  'style'=>'display:block', 'required' => $is_service_staff_required ? true : false]); !!}-->
<!--			@if(!empty($pos_settings['inline_service_staff']))-->
<!--			<div class="input-group-btn">-->
<!--                <button type="button" class="btn btn-default bg-white btn-flat" id="select_all_service_staff" data-toggle="tooltip" title="@lang('lang_v1.select_same_for_all_rows')"><i class="fa fa-check"></i></button>-->
<!--            </div>-->
<!--            @endif-->
<!--		</div>-->
<!--	</div>-->
<!--</div>-->
<!--@endif-->
@if($tables_enabled)
<div class="col-sm-4" >
	<div class="form-group">
		
		<div class="input-group" style="display:none" id="res_table_id" >
			<span class="input-group-addon">
				<i class="fa fa-table"></i>
			</span>
			{!! Form::select('res_table_id', $tables, $view_data['res_table_id'], ['class' => 'form-control ', 'placeholder' => __('restaurant.select_table') ,'id'=> 'res_tables_id']); !!}
		</div>
	</div>
</div>

@endif	
@if($waiters_enabled)
<div class="col-sm-4">
	<div class="form-group">
		<div class="input-group">
			<span class="input-group-addon">
				<i class="fa fa-user-secret"></i>
			</span>
			{!! Form::select('res_waiter_id', $waiters, $view_data['res_waiter_id'], ['class' => 'form-control', 'placeholder' => __('restaurant.select_service_staff'), 'id' => 'res_waiter_id', 'style'=>'display:block', 'required' => $is_service_staff_required ? true : false]); !!}
			@if(!empty($pos_settings['inline_service_staff']))
			<div class="input-group-btn">
                <button type="button" class="btn btn-default bg-white btn-flat" id="select_all_service_staff" data-toggle="tooltip" title="@lang('lang_v1.select_same_for_all_rows')"><i class="fa fa-check"></i></button>
            </div>
            @endif
		</div>
	</div>
</div>
@endif
<script>
	// $(document).ready(function () {
    //     var index = $("#types_of_service_id option:selected").text();
	// 	var data=document.getElementsByClassName('hamza')['0'].value;
		
	// 	if (data == "Dine inn") {
	// 	  //  alert(data);
    //                 $('#res_table_id').show();
    //             }
    //             else {
    //                 $('#res_table_id').hide();
    //             }
    //         $("#types_of_service_id").change(function () {
    //             var index = $("#types_of_service_id option:selected").text();
    //             if (index == "Dine inn") {
    //                 $('#res_table_id').show();
    //             }
    //             else {
    //                 $('#res_table_id').hide();
    //             }
    //         });
    // });
	</script>