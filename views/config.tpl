@extends('admin.master')

@section('title', trans('GPlane\PluginsMarket::config.title'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      {{ trans('GPlane\PluginsMarket::config.title') }}
    </h1>
    <div class="breadcrumb"></div>
  </section>

  <!-- Main content -->
  <section class="content">
    <?php
      $form = Option::form('market_source_title', trans('GPlane\PluginsMarket::config.options.title'), function($form) {
        $form->text('market_source', trans('GPlane\PluginsMarket::config.options.source-text'))->hint(trans('GPlane\PluginsMarket::config.options.source-hint'));
        $form->checkbox('auto_enable_plugin', trans('GPlane\PluginsMarket::config.options.auto-enable-text'))->label(trans('GPlane\PluginsMarket::config.options.auto-enable-label'));
        $form->checkbox('replace_default_market', trans('GPlane\PluginsMarket::config.options.replace-default-market-text'))->label(trans('GPlane\PluginsMarket::config.options.replace-default-market-label'));
        $form->select('plugin_update_notification', trans('GPlane\PluginsMarket::config.options.update-notif-text'))
            ->option('none', trans('GPlane\PluginsMarket::config.options.update-none'))
            ->option('release_only', trans('GPlane\PluginsMarket::config.options.update-release-only'))
            ->option('both', trans('GPlane\PluginsMarket::config.options.update-both'));
      })->handle();
    ?>

    <div class="row">
        <div class="col-md-6">
            {!! $form->render() !!}
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('GPlane\PluginsMarket::config.readme.title') }}</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <blockquote><p><em>高考不简单</em></p></blockquote>
                    {!! trans('GPlane\PluginsMarket::config.readme.text') !!}
                    <br><br>
                    <span style="float: right;">Powered by <a href="https://github.com/g-plane" target="_blank">GPlane</a></span>
                </div><!-- /.box-body -->
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('GPlane\PluginsMarket::config.list.title') }}</h3>
                </div><!-- /.box-header -->

                <div class="box-body">
                    {!! trans('GPlane\PluginsMarket::config.list.text') !!}
                    <br>
                    <ul>
                        <li>{{ trans('GPlane\PluginsMarket::config.list.source1') }}&nbsp;--&gt;&nbsp;<a href="http://to.be.filled/" target="_blank">http://to.be.filled/</a></li>
                    </ul>
                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
    
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

