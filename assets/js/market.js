var pluginsTable

$(document).ready(function() {
  $('.box-body').css(
    'min-height',
    $('.content-wrapper').height() - $('.content-header').outerHeight() - 120
  )
  pluginsTable = $('#plugin-table').DataTable({
    language: trans('vendor.datatables'),
    scrollX: true,
    autoWidth: false,
    processing: true,
    ordering: false,
    serverSide: false, //未知原因，开了这个会有问题
    ajax: {
      url: '/admin/plugins-market/data',
      dataSrc: ''
    },
    createdRow: function(row, data, index) {},
    columnDefs: [
      {
        targets: 0,
        title: trans('market.market.title'),
        data: 'title'
      },
      {
        targets: 1,
        title: trans('market.market.description'),
        data: 'description',
        width: '40%'
      },
      {
        targets: 2,
        title: trans('market.market.author'),
        data: 'author',
        width: '10%'
      },
      {
        targets: 3,
        title: trans('market.market.version'),
        data: 'version',
        width: '9%',
        render: function(data, type, row, meta) {
          options = ''
          for (var i = data.length - 1; i >= 0; i--) {
            options += '<option>' + data[i] + '</option>'
          }
          return (
            '<select id="plugin-' +
            row.name +
            '-vers" class="form-control">' +
            options +
            '</select>'
          )
        }
      },
      {
        targets: 4,
        title: trans('market.market.size'),
        data: 'size',
        width: '8%'
      },
      {
        targets: 5,
        title: trans('market.market.operations'),
        data: 'brief',
        width: '20%',
        render: function(data, type, row, meta) {
          var downloadButtonClass = 'btn-primary'
          var downloadButtonHint = ''
          switch (row.versionStatus) {
            case 'preview':
              downloadButtonClass = 'btn-warning'
              downloadButtonHint = trans('market.market.versionPre')
              break
            case 'new':
              downloadButtonClass = 'btn-success'
              downloadButtonHint = trans('market.market.versionNew')
              break
            default:
              break
          }
          var downloadButton =
            '<input type="button" id="plugin-' +
            row.name +
            '" class="btn ' +
            downloadButtonClass +
            ' btn-sm" title="' +
            downloadButtonHint +
            '"' +
            ' onclick="readyToDownload(\'' +
            row.name +
            "','" +
            row.title +
            "','" +
            row.versionStatus +
            '\');" value="' +
            trans('market.market.download') +
            '">'
          var briefButton =
            '<a class="btn btn-default btn-sm" href="' +
            data +
            '" target="_blank" title="' +
            trans('market.market.briefHint') +
            '">' +
            trans('market.market.viewBrief') +
            '</a>'
          return downloadButton + briefButton
        }
      }
    ]
  })
})

function readyToDownload(pluginName, pluginTitle, versionStatus) {
  if (versionStatus == 'preview') {
    swal({
      title: trans('market.preview.title'),
      text: trans('market.preview.text'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: trans('market.preview.confirmButton'),
      cancelButtonText: trans('market.preview.cancelButton')
    }).then(function() {
      return download(pluginName, pluginTitle)
    })
  } else {
    var version = $('select#plugin-' + pluginName + '-vers').val()
    return download(pluginName, pluginTitle, version)
  }
}

function download(pluginName, pluginTitle, version) {
  $('input#plugin-' + pluginName).attr({
    disabled: true
  })
  $('input#plugin-' + pluginName).val(trans('market.downloading'))
  toastr.info(
    trans('market.readyToDownload', {
      'plugin-name': pluginTitle
    })
  )
  $.post(
    '/admin/plugins-market/download',
    {
      name: pluginName,
      version: version
    },
    function(data) {
      if (data.code === undefined) {
        toastr.error(trans('market.error.unknown'))
      } else {
        switch (data.code) {
          case -1:
            toastr.error(
              trans('market.failedDownload', {
                message: trans('market.error.requestPermission')
              })
            )
            break
          case 0:
            toastr.success(
              trans('market.completeDownload', {
                'plugin-name': pluginTitle
              })
            )
            break
          case 1:
            toastr.error(
              trans('market.failedDownload', {
                message: trans('market.error.writePermission')
              })
            )
            break
          case 2:
            toastr.error(
              trans('market.failedDownload', {
                message: trans('market.error.connection')
              })
            )
            break
          case 3:
            toastr.error(
              trans('market.failedDownload', {
                message: trans('market.error.download')
              })
            )
            break
          case 4:
            toastr.error(
              trans('market.failedDownload', {
                message: trans('market.error.unzip')
              })
            )
            break
          default:
            toastr.error(trans('market.error.unknown'))
            break
        }
      }
      $('input#plugin-' + pluginName).attr({
        disabled: false
      })
      $('input#plugin-' + pluginName).val(trans('market.download'))
    }
  )
}
