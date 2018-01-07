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

        <div class="row" style="margin-bottom: 5px">
            <div class="col-md-10">{!! trans('GPlane\PluginsMarket::market.tip') !!}</div>

            <div class="col-md-2">
                <a href="https://github.com/g-plane" target="_blank" class="btn btn-primary">Follow me on GitHub</a>
            </div>
        </div>

        <div class="box">
            <div class="box-body table-bordered">
                <table id="market-table" class="table table-hover"></table>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript" src="{{ plugin_assets('unofficial-plugins-market', 'assets/js/dist/market.js') }}"></script>
@endsection
