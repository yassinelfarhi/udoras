/**
 * Validation for price
 */
$('input.training-amount').on('change keyup', function (event) {
    event.preventDefault();

    var link = $('a.pay-for-training');
    var totalPriceElement = $('span.total-price');
    var defaultValue = totalPriceElement.attr('data-default');
    var amount = getAmount('input.training-amount');
    $(this).val(amount);

    if (amount == 0) {
        totalPriceElement.html(0);
        link.attr('disabled', true);
    } else if (amount > 0) {
        link.removeAttr('disabled');
        totalPriceElement.html(amount * defaultValue);
    } else {
        $(this).val(1);
        totalPriceElement.html(defaultValue);
    }
});

