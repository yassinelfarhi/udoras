$('input.customer-email').bind("change autocompletechange", function () {
    var email = $(this).val();
   
    $.ajax({
        url: "/admin/customer/show",
        data: {"email": email}
    }).done(function (result) {
        var toInsert = "<Customer Name>";

        if (result.name) {
            toInsert = result.name;
        }

        $('.customer-name').text(toInsert);
    });
});