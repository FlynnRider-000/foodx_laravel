@extends('layouts.app')
@push('css_lib')
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-material-datetimepicker.min.css')}}" integrity="sha512-uLI05NEY4Yj4tbrsvcBHTcRJBT4gZaxENUHwjWMcLIK0xaVzpr4ScBA5Wc7dgw/wVTzKLGWsq0MeXQp0SkXpIQ=="/>
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
{{--dropzone--}}
<link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.market_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.market_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('markets.index') !!}">{{trans('lang.market_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.market_create')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        @can('markets.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('markets.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.market_table')}}</a>
        </li>
        @endcan
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.market_create')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::open(['route' => 'markets.store']) !!}
      <div class="row">
        @include('markets.fields')
      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@include('layouts.media_modal')
@endsection
@push('scripts_lib')
<script type="text/javascript" src="{{asset('plugins/datetimepicker/material.min.js')}}"></script>
<script type="text/javascript" src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-material-datetimepicker.min.js')}}" integrity="sha512-LRkOtikKE2LFHPWiWh0/bfFynswxRwCZ5O7PkXTVFPcprw376xfOemiEHEOmCCmiwS6eLFUh2fb+Gqxc0waTSg=="></script>
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('plugins/select2/select2.min.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
{{--dropzone--}}
<script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
    $(document).ready(function()
		{
			$('#market_open_time').bootstrapMaterialDatePicker
			({
        date: false,
        format : 'HH:mm'
      });
      $('#market_close_time').bootstrapMaterialDatePicker
			({
        date: false,
        format : 'HH:mm'
      });
    });
</script>
@endpush