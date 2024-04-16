<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\App\Http\Controllers\CurrencyChangeController::class, 'store']), 'method' => 'post', 'id' => $quick_add ? 'quick_add_brand_form' : 'currency_add_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">CurrencyChange</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('name', 'Currency Name:*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Currency Change']); !!}
        </div>
  
        <div class="form-group">
          {!! Form::label('changerate', 'Currency Change Rate In Pkr :') !!}
            {!! Form::number('changerate', null, ['class' => 'form-control','placeholder' => 'Currency Change Rate In Pkr']); !!}
        </div>
  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->