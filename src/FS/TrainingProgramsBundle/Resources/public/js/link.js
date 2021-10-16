/**
 * get form for create links
 */

$(document).on('click', 'a.new-link', function (event) {
    event.preventDefault();

    if ($('.new-link-form').length == 0) {
        var url = $(this).attr('href');

        $.ajax({
            url: url
        }).done(function (result) {
            $('.new-link-form-place').html(result[1]);
            $('a.new-link').hide();
        });
    }
});

/**
 * cancel creation of link for edit training program page
 */
$(document).on('click', '.cancel-link-creation', function (event) {
    event.preventDefault();
    $('.new-link-form-place').empty();
    $('a.new-link').show();
});

/**
 * send ajax request for saving link
 */
$(document).on('click', '.save-link', function (event) {
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
        success: function (data) {
            var status = data[0];
            var value = data[1];

            switch (status) {
                case 'success':
                    $('.new-link-form-place').empty();
                    $('a.new-link').show();
                    $('.links').html(value);
                    break;
                case 'error':
                    var $newForm = $(value);
                    $form.replaceWith($newForm);
                    break;
                case 'processed':
                    $button.trigger("processed", value);
                    break;
                default:
                    $form.replaceWith($(value));
                    break;
            }
        },
        error: function (data) {
            alert('ajax-formerror');
            console.error(data);
            location.reload();
        }
    });
    // prevent normal form submission
    event.preventDefault();
});


/**
 * delete link
 */
$(document).on('click', '.delete-link', function (event) {
    event.preventDefault();
    var url = $(this).attr('href');

    $.ajax({
        url: url
    }).done(function (result) {
        $('#ajax-modal').modal('hide');
        $('.links').html(result[1]);
    });
});
