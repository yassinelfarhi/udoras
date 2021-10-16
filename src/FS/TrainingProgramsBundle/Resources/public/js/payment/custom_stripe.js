/**
 * Customize Stripe
 */
(function () {
    var modal = $('<div id="ajax-modal" class="modal" tabindex="-1" style="display: none;"></div>');
    var link = $('a.pay-for-training');
    var key = link.attr('data-key');
    var email = link.attr('data-email');

    var handler = StripeCheckout.configure({
        key: key,
        locale: 'auto',
        email: email,
        token: function (token) {
            var link = $('a.pay-for-training.current');
            $('a.pay-for-training').removeClass('current');
            var url = link.attr('href'),
                amount = getAmount('input.training-amount');

            $.ajax({
                url: url,
                method: 'POST',
                data: {stripeToken: token.id, amount: amount}
            })
            .done(function (response) {
                var status = response.status,
                    content = response.content;

                switch (status) {
                    case 'success-vendor':
                        $('.training-status').html(content.trainingStatus);
                        showModal(modal, content.modal);
                        break;
                    case 'success-employee':
                        showModal(modal, content.modal);
                        updateRow(link, content.row);
                        updateTrainingAction(link, content.trainingAction);
                        break;
                    default:
                        location.reload();
                }
            })
            .fail(function () {
                location.reload();
            });
        }
    });

    /**
     * Customize Stripe - add handler to button for showing Stripe modal
     */
    $(document).on('click', 'a.pay-for-training', function (event) {
        event.preventDefault();
        $(this).addClass('current');

        if ($(this).attr('disabled') != 'disabled') {
            var amount = $('span.total-price').html();
            amount = amount > 0 ? amount :  $(this).attr('data-amount');
            amount *= 100;

            handler.open({
                name: 'uDoras',
                description: 'Pay for training',
                amount: amount
            });
        }
    });
})();


/**
 * Helper functions
 */
function getAmount(selector) {
    var amount = Math.round($(selector).val());
    return amount > 0 ? amount : 1;
}

function showModal(modal, content) {
    modal.html(content);
    modal.modal('show');
}

function updateRow(currentLink, row) {
    if (currentLink && row) {
        var oldRow = currentLink.closest('.training-row');

        if (oldRow.length > 0) {
            $(oldRow).replaceWith(row);
        }
    }
}

function updateTrainingAction(currentLink, trainingAction) {
    if (currentLink && trainingAction) {
        var oldTrainingAction = currentLink.closest('.training-action');

        if (oldTrainingAction.length > 0) {
            $(oldTrainingAction).replaceWith(trainingAction)
        }
    }
}
