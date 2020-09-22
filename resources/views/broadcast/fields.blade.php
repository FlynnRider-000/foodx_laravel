<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <!-- Name Field -->
    <div class="form-group row ">
        {!! Form::label('message', "Message", ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('message', null,  ['class' => 'form-control','placeholder'=>  "Insert Message Content"]) !!}
            <div class="form-text text-muted">
                Insert Message
            </div>
        </div>
    </div>
</div>
<!-- Submit Field -->
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-send"></i> Send </button>
</div>