/**
 * update Employees by selecting vendor
 */
$(document).on('click', 'a.ajax-book-training', function (event) {
    var currentLink = $(this);

    if (currentLink.attr('disabled') == null) {
        currentLink.attr('disabled', 'disabled');
        var modal = $('<div id="ajax-modal" class="modal" tabindex="-1" style="display: none;"></div>');
        var url = $(this).attr('href');

        $.ajax({
            url: url
        })
        .done(function (result) {
            var status = result.status;

            if (status) {
                switch (status) {
                    case 'redirect':
                        window.location.href = result.content;
                        break;
                    case 'success':
                    case 'error':
                        showModal(modal, result.content);
                        currentLink.removeAttr('disabled');
                        updateRow(currentLink, result.row);
                        updateTrainingAction(currentLink, result.trainingAction);
                        break;
                }
            } else {
                location.reload();
            }
        })
        .fail(function () {
            location.reload();
        });
    }

    event.preventDefault();
});