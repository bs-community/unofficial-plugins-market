$.get(
    '/admin/plugins-market/check',
    function (data) {
        if (data.number.release > 0 && data.number.pre > 0) {
            $('a[href="' + data.url + '"]').append(
                '<span class="pull-right-container"><small class="label pull-right label-warning">' + 
                data.number.pre + '</small>' + 
                '<small class="label pull-right label-success">' + data.number.release + '</small></span>');
        } else if (data.number.release > 0) {
            $('a[href="' + data.url + '"]').append(
                '<span class="pull-right-container"><small class="label pull-right label-success">' + data.number.release + '</small></span>');
        } else if (data.number.pre > 0) {
            $('a[href="' + data.url + '"]').append(
                '<span class="pull-right-container"><small class="label pull-right label-warning">' + data.number.pre + '</small></span>');
        }
    });
