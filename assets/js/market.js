$(document).ready(function() {
    $('.box-body').css('min-height', $('.content-wrapper').height() - $('.content-header').outerHeight() - 120);
});

$('#plugin-table').DataTable({
    language: trans('vendor.datatables'),
    responsive: true,
    autoWidth: false,
    processing: true,
    ordering: false,
    serverSide: false,   //未知原因，开了这个会有问题
    ajax: '/admin/plugins-market/data',
    createdRow: function (row, data, index) {},
    columns: [
        {data: 'display-name'},
        {data: 'description', 'width': '40%'},
        {data: 'author', 'width': '15%'},
        {data: 'version', 'width': '7%'},
        {data: 'size', 'width': '10%'},
        {data: 'operations', 'width': '15%'}
    ]
});

function readyToDownload(pluginId, displayName) {
    if ($('#plugin_' + pluginId).hasClass('btn-warning')) {
        swal({
            title: trans('market.preview.title'),
            text: trans('market.preview.text'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: trans('market.preview.confirmButton'),
            cancelButtonText: trans('market.preview.cancelButton')
        }).then(function () { return download(pluginId, displayName); });
    } else {
        return download(pluginId, displayName);
    }
};

function download(pluginId, displayName) {
    $('input#plugin-' + pluginId).attr({ disabled: true });
    $('input#plugin-' + pluginId).val(trans('market.downloading'));
    toastr.info(trans('market.readyToDownload', { 'plugin-name': displayName }));
    $.post(
        '/admin/plugins-market/download',
        { id: pluginId },
        function (data) {
            switch (data.code) {
                case -1:
                    toastr.error(trans('market.failedDownload', { 'message': trans('market.error.requestPermission') }));
                    break;
                case 0:
                    if (data.enable) {
                        //会不会有Callback Hell啊？
                        $.post('/admin/plugins/manage', { action: 'enable', id: pluginId }, function (data) {
                            if (data.errno == 0) {
                                toastr.success(data.msg);
                                $.post('/admin/plugins-market/first-run', { id: pluginId }, function (data) {});
                            }
                        });
                    }
                    toastr.success(trans('market.completeDownload', { 'plugin-name': displayName }));
                    break;
                case 1:
                    toastr.error(trans('market.failedDownload', { 'message': trans('market.error.writePermission') }));
                    break;
                case 2:
                    toastr.error(trans('market.failedDownload', { 'message': trans('market.error.connection') }));
                    break;
                case 3:
                    toastr.error(trans('market.failedDownload', { 'message': trans('market.error.download') }));
                    break;
                case 4:
                    toastr.error(trans('market.failedDownload', { 'message': trans('market.error.unzip') }));
                    break;
                default:
                    toastr.error(trans('market.error.unknown'));
                    break;
        };
        $('input#plugin-' + pluginId).attr({ disabled: false });
        $('input#plugin-' + pluginId).val(trans('market.download'));
    });
};
