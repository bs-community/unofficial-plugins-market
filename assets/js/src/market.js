'use strict'

function download (pluginName, pluginTitle, version) {
  $(`input#plugin-${pluginName}`).attr({
    disabled: true
  })
  $(`input#plugin-${pluginName}`).val(trans('market.downloading'))
  toastr.info(
    trans('market.readyToDownload', {
      'plugin-name': pluginTitle
    })
  )
  $.ajax({
    type: 'POST',
    url: '/admin/plugins-market/download',
    data: {
      name: pluginName,
      version
    },
    success (data) {
      if (!data.code) {
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
          if ($(`input#plugin-${pluginName}`).hasClass('btn-success')) {
            $(`input#plugin-${pluginName}`)
              .removeClass('btn-success')
              .addClass('btn-primary')
          }
          if ($(`input#plugin-${pluginName}`).hasClass('btn-warning')) {
            $(`input#plugin-${pluginName}`)
              .removeClass('btn-warning')
              .addClass('btn-primary')
          }
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
      $(`input#plugin-${pluginName}`).attr({
        disabled: false
      })
      $(`input#plugin-${pluginName}`).val(trans('market.download'))
    },
    error () {
      toastr.error(trans('market.error.unknown'))
      $(`input#plugin-${pluginName}`).attr('disabled', false)
    }
  })
}

function readyToDownload (pluginName, pluginTitle, versionStatus) {
  const version = $(`select#plugin-${pluginName}-vers`).val()
  if (versionStatus === 'preview') {
    swal({
      title: trans('market.preview.title'),
      text: trans('market.preview.text'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: trans('market.preview.confirmButton'),
      cancelButtonText: trans('market.preview.cancelButton')
    }).then(() => download(pluginName, pluginTitle, version))
  } else {
    return download(pluginName, pluginTitle, version)
  }
}

$(document).ready(() => {
  $('.box-body').css(
    'min-height',
    $('.content-wrapper').height() - $('.content-header').outerHeight() - 120
  )
  // eslint-disable-next-line new-cap
  $('#market-table').DataTable({
    language: trans('vendor.datatables'),
    scrollX: true,
    autoWidth: false,
    processing: true,
    ordering: false,
    serverSide: false, // 未知原因，开了这个会有问题
    ajax: {
      url: '/admin/plugins-market/data',
      dataSrc: ''
    },
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
        render (data, type, row) {
          let options = ''
          for (let i = data.length - 1; i >= 0; i--) {
            options += `<option>${data[i]}</option>`
          }
          return `
            <select id="plugin-${row.name}-vers" class="form-control">
              ${options}
            </select>`
        }
      },
      {
        targets: 4,
        title: trans('market.market.operations'),
        data: 'brief',
        width: '20%',
        render (data, type, row) {
          let downloadButtonClass = 'btn-primary'
          let downloadButtonHint = ''
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
          const downloadButton
            = `<input
              type="button"
              id="plugin-${row.name}"
              class="btn ${downloadButtonClass} btn-sm"
              title="${downloadButtonHint}"`
            + ' onclick="readyToDownload('
            + `'${row.name}','${row.title}','${row.versionStatus}'`
            + `);" value="${trans('market.market.download')}">`
          const briefButton = `<a class="btn btn-default btn-sm"
            href="${data}"
            target="_blank"
            title="${trans('market.market.briefHint')}">
              ${trans('market.market.viewBrief')}
            </a>`
          return downloadButton + briefButton
        }
      }
    ]
  })
})

window.readyToDownload = readyToDownload
