(function ($) {
    $(document).on('click', '.ajax-link', function (event) {
        var $modal = $('<div id="ajax-modal-link" class="modal" tabindex="-1" style="display: none;"></div>');
        var $button = $(this);
        // button loading state
        $button.attr('disabled', 'disabled');
        // ajax request
        $.ajax({
            type: "GET",
            url: $button.attr('href'),
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                //console.log(data);
                var status = data[0];
                var value = data.target;
                $button.attr('disabled', false);
                switch (status) {
                    case 'error':
                        //modal will be here
                        var params = {backdrop: 'static'};
                        $modal.append(data.modal);
                        $modal.modal(params);
                        $modal.data('trigger', this);
                        break;
                    case 'redirect':
                        window.location.href = data[1];
                        break;
                    case 'processed':
                        $button.trigger("processed", value);
                }
            },
            error: function (data) {
                $button.attr('disabled', false);
                //alert('ajax-link.js error');
                console.error(data);
                location.reload();
            }
        });
        // prevent normal form submission
        event.preventDefault();
    });


    $(document).on('processed', '.ajax-link', function (event, response) {
        var $button = $(this);
        var data = $button.data();
        if (typeof($.ajaxLinkOps[data.op]) != "undefined") {
            $.ajaxLinkOps[data.op]($button, response);
        }
    });

    $.ajaxLinkOps = {
        redirect: function (button, target) {
            window.location.href = target;
        },
        reload: function () {
            location.reload();
        },
        replace: function (button, response) {
            var $target = $(button);
            $target.replaceWith(response);
        },
        nothing: function () {

        },
        change: function (button, data) {
            var $target = $(button);
            $target.attr('href', data.href);
            $target.html(data.name);
            if (data.addClass) {
                $target.addClass(data.addClass);
            }
            if (data.removeClass) {
                $target.removeClass(data.removeClass);
            }
            var $extra = $target.parent().find('.alert-danger');
            if (data.extra) {
                $extra.show()
            } else {
                $extra.hide();
            }
        },
        remove: function () {

        }
    };


})(jQuery);