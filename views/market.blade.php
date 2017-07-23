@extends('admin.master')

@section('title', trans('GPlane\PluginsMarket::general.name'))

@section('style')
<link rel="stylesheet" type="text/css" href="{{ plugin_assets('unofficial-plugins-market', 'assets/css/market.css') }}">
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('GPlane\PluginsMarket::general.name') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (session()->has('message'))
            <div class="callout callout-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">{!! trans('GPlane\PluginsMarket::market.tip') !!}</div>
        </div>

        <div class="box">
            <div class="box-body table-bordered">
                <table id="plugin-table" class="table table-hover"></table>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript" src="{{ plugin_assets('unofficial-plugins-market', 'assets/js/dist/market.js') }}"></script>
@endsection
