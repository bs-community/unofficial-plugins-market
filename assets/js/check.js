$.get('/admin/plugins-market/check', function(data) {
  if (data.count.release > 0 || data.count.pre > 0) {
    $('ul.nav').prepend(`
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-plug"></i>
                        <span>${data.count.release + data.count.pre}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <ul class="menu" id="plugin-update-menu"></ul>
                        </li>
                    </ul>
                </li>
            `)
  }
  if (data.count.release > 0) {
    $('ul#plugin-update-menu').append(`
                <li>
                    <a href="${data.url}" title="${trans('market.check.new', {
      count: data.count.release.toString()
    })}">
                        <i class="fa fa-plug text-green"></i>${trans(
                          'market.check.new',
                          { count: data.count.release.toString() }
                        )}
                    </a>
                </li>
            `)
  }
  if (data.count.pre > 0) {
    $('ul#plugin-update-menu').append(`
                <li>
                    <a href="${data.url}" title="${trans('market.check.pre', {
      count: data.count.pre.toString()
    })}">
                        <i class="fa fa-plug text-yellow"></i>${trans(
                          'market.check.pre',
                          { count: data.count.pre.toString() }
                        )}
                    </a>
                </li>
            `)
  }
})
