(function ($) {
    $.fn.modal.defaults.spinner = $.fn.modalmanager.defaults.spinner =
        '<div class="loading-spinner" style="width: 200px; margin-left: -100px;">' +
        '<div class="progress progress-striped active">' +
        '<div class="progress-bar" style="width: 100%;"></div>' +
        '</div>' +
        '</div>';

    var $modal = $('<div id="ajax-modal" class="modal" tabindex="-1" style="display: none;"></div>');

    $(document).on('click', '.ajax-modal', function (e) {
        e.preventDefault();

        // create the backdrop and wait for next modal to be triggered
//        $modal.modal('show').modal('loading');
        var link = $(this),
            url = link.attr('href');
        $modal.load(url, '', function (response, status, xhr) {
            if (status == 'error') {
                location.reload();
                return;
            }

            var params = {backdrop: 'static'};
            var width = link.data('width');
            var height = link.data('height');
            if (undefined !== width) {
                params.width = width;
            }
            if (undefined !== height) {
                params.height = height;
            }
            $modal.modal(params);
//            $(':input:visible:first', $modal).focus();
        });
        $modal.data('trigger', this);
    });

    $(document).on('processed', '.modal .ajax-submit', function (event, response) {
        var $modal = $(this).closest('.modal');
        var triggerData = $($modal.data('trigger')).data();
        if (typeof($.ajaxModalFormOps[triggerData.op]) != "undefined") {
            $.ajaxModalFormOps[triggerData.op]($modal, triggerData.target, response);
        }
    });
    $.ajaxModalFormOps = {
        redirect: function (modal, target, response) {
            window.location.href = response ? response : target;
        },
        reload: function () {
            location.reload();
        },
        replace: function (modal, target, response) {
            var $target = $(target);
            $target.replaceWith(response);
        }
    };
})(jQuery);