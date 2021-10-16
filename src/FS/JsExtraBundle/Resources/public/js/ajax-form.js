(function ($) {
    var $modal = $('<div id="ajax-modal" class="modal in animated shake" style="display: none;"></div>');

    $(document).on('click', '.ajax-submit', function (event) {
        var $button = $(this);
        var $form = $(this.form);
        // button loading state
        $button.text('Processing...').attr('disabled', 'disabled');

        // ajax request
        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serializeWithFiles(),
            cache: false,
            contentType: false,
            processData: false,
//            dataType: "json",
            success: function (data) {
                //var newString = '';
                //for (var i = 0; i < data.length; i++) {
                //    newString += data[i];
                //}
                //console.log(newString);
                var status = data[0];
                //alert(data[0]);
                var value = data[1];
                //alert(data[1]);
                switch (status) {
                    case 'error':
                        var $newForm = $(value);
                        // replace with updated form
                        $form.replaceWith($newForm);
                        // place focus on first error element of updated form
//                        $('.form-group.has-error :input:visible:first', $newForm).focus();
                        break;
                    case 'processed':
                        $button.trigger("processed", value);
                        break;
                    case 'show-modal':
                        $modal.html(value);
                        $modal.modal({
                            show: true,
                            keyboard: false
                        });
                    case 'show-modal-static':
                        $modal.html(value);
                        $modal.modal({
                            show: true,
                            keyboard: false,
                            backdrop: 'static'
                        });

                        break;
                    default:
                        // replace with updated form
                        $form.replaceWith($(value));
                        break;
                }
            },
            error: function (data) {
                alert('ajax-form.js error');
                console.error(data);
                location.reload();
            }
        });
        // prevent normal form submission
        event.preventDefault();
    });


    $.fn.serializeWithFiles = function () {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input:file"), function (i, tag) {
            $.each($(tag)[0].files, function (i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };


    $(document).on('processed', '.ajax-submit.inline', function (event, response) {
        var $button = $(this);
        var $form = $(this).closest('form');
        var data = $button.data();
        if (typeof($.ajaxFormOps[data.op]) != "undefined") {
            var target = (data.target != "undefined") ? data.target : null;
            $.ajaxFormOps[data.op]($form, target, response);
        }
    });

    $.ajaxFormOps = {
        redirect: function (form, target) {
            window.location.href = target;
        },
        reload: function () {
            location.reload();
        },
        replace: function (form, target, response) {
            var $target = $(target);
            $target.replaceWith(response);
        },
        nothing: function () {
            console.log('nothing')
        }
    };


})(jQuery);