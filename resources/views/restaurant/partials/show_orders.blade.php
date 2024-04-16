@forelse($orders as $order)
	<div class="col-md-3 col-xs-6 order_div">
		<div class="small-box bg-gray">
			
            <div class="inner">
            	<h4 class="text-center">#{{$order->invoice_no}}</h4>
            	<table class="table no-margin no-border table-slim">
            		<tr><th>@lang('restaurant.placed_at')</th><td>{{@format_date($order->created_at)}} {{ @format_time($order->created_at)}}</td></tr>
            		<tr><th>@lang('restaurant.order_status')</th>
                              @php
                                    $count_sell_line = count($order->sell_lines);
                                    $count_cooked = count($order->sell_lines->where('res_line_order_status', 'cooked'));
                                    $count_served = count($order->sell_lines->where('res_line_order_status', 'served'));
                                    $order_status =  'received';
                                    if($count_cooked == $count_sell_line) {
                                          $order_status =  'cooked';
                                    } else if($count_served == $count_sell_line) {
                                          $order_status =  'served';
                                    } else if ($count_served > 0 && $count_served < $count_sell_line) {
                                          $order_status =  'partial_served';
                                    } else if ($count_cooked > 0 && $count_cooked < $count_sell_line) {
                                          $order_status =  'partial_cooked';
                                    }
                                    
                              @endphp
                              <td><span class="label @if($order_status == 'cooked' ) bg-red @elseif($order_status == 'served') bg-green @elseif($order_status == 'partial_cooked') bg-orange @else bg-light-blue @endif">@lang('restaurant.order_statuses.' . $order_status) </span></td>
                        </tr>
            		<tr><th>@lang('contact.customer')</th><td>{{$order->customer_name}}</td></tr>
            		<tr><th>@lang('restaurant.table')</th><td>{{$order->table_name}}</td></tr>
            		<tr><th>@lang('sale.location')</th><td>{{$order->business_location}}</td></tr>
            		
					<tr>
						<th>Product</th>
						<th>Quantity</th>
						
					</tr>

					

					@foreach($order['sell_lines'] as $key => $product)
								
						<tr>
							<td>{{$product['product']['name'] }}</td>
							<td>{{ $product['quantity'] }}</td>
							
						</tr>
					@endforeach
					
					@if($order->additional_notes)
						<th>Sell Note</th>
						<td style="font-size: {{ strlen($order->additional_notes) > 10 ? 'small' : 'medium' }}; white-space: nowrap;">
							{{ $order->additional_notes }}
						</td>
				    @endif
						
					
            	</table>
            </div>
			
			
            @if($orders_for == 'kitchen')
            	<a href="#" class="btn btn-flat small-box-footer bg-yellow mark_as_cooked_btn" data-href="{{action([\App\Http\Controllers\Restaurant\KitchenController::class, 'markAsCooked'], [$order->id])}}"><i class="fa fa-check-square-o"></i> @lang('restaurant.mark_as_cooked')</a>
            @elseif($orders_for == 'waiter' && $order->res_order_status != 'served')
            	<a href="#" class="btn btn-flat small-box-footer bg-yellow mark_as_served_btn" data-href="{{action([\App\Http\Controllers\Restaurant\OrderController::class, 'markAsServed'], [$order->id])}}"><i class="fa fa-check-square-o"></i> @lang('restaurant.mark_as_served')</a>
            @else
            	<div class="small-box-footer bg-gray">&nbsp;</div>
            @endif
            	{{-- <a href="#" class="btn btn-flat small-box-footer bg-info btn-modal" data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$order->id])}}" data-container=".view_modal">@lang('restaurant.order_details') <i class="fa fa-arrow-circle-right"></i></a> --}}
         </div>
	</div>
	@if($loop->iteration % 4 == 0)
		<div class="hidden-xs">
			<div class="clearfix"></div>
		</div>
	@endif
	@if($loop->iteration % 2 == 0)
		<div class="visible-xs">
			<div class="clearfix"></div>
		</div>
	@endif
@empty
<div class="col-md-12">
	<h4 class="text-center">@lang('restaurant.no_orders_found')</h4>
</div>
@endforelse
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>

$(document).ready(function(){

	$(document).on('click','#cookedd',function(){

		var today = new Date();
		var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
		var ge=$(this).attr('data-id');
		var ordercooked="Cooked";

		// alert(ge);
					$.ajax({
                                type: "get",
                                url:  '/serveredtime/'+ time + '/' +ge + '/'+ordercooked,
                               success:function(data){

                            }

        				})

		})

						$(document).on('click','#served',function(){
						
						var today = new Date();
						var finaltime = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
						// alert(finaltime);

						var arrr=finaltime.split(":");
                            var hours=arrr[0];
                            var minutes=arrr[1];
                            var seconds=arrr[2];
                            var converthours=(hours*60)*60;

                            var convertminutes=minutes*60;
                            // alert(convertminutes)
                            var convertsec=seconds*1;
                            var timetaken=converthours+convertminutes+convertsec;
							// alert(timetaken);

                           
							var orderserved="Served";
						var gar=$(this).attr('data-id');
						// alert(gar);
						var gety=$(this).attr('data-name');
						var arrr=gety.split(":");
                            var ho=arrr[0];
                            var min=arrr[1];
                            var sec=arrr[2];
                            var conho=(ho*60)*60;

                            var conmin=min*60;
                            // alert(convertminutes)
                            var consec=sec*1;
                            var timeconvert=conho+conmin+consec;
							// alert(timeconvert);

						var total=timetaken-timeconvert;
						var ho=Math.floor(total/3600);
                                    var mi= Math.floor(total/60%60);
                                    var ss=Math.floor(total%60);
                                    servedtotal=`${ho} : ${mi} : ${ss}`;
									// alert(a);
								$.ajax({
													type: "get",
													url:  '/serveredtotaltime/'+ finaltime + '/' +gar+ '/'+servedtotal+ '/' +orderserved,
												success:function(data){

												}

										})

						})
	
})
</script>


