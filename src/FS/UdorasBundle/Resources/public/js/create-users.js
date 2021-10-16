autocompleteName('.customer-email', '.customer-name', '/customer/new/vendor', '<Customer Name>');
autocompleteName('.vendor-email', '.vendor-name', '/vendor/new/employee', '<Vendor Name>');

function autocompleteName(fromSelector, toSelector, path, defaultValue) {
    $(fromSelector).on("change autocompletechange", function () {
        var data = null;
        var value = $(this).val();

        if (value % 1 === 0) {
            data = {'id': value};
        } else {
            data = {"email": value}
        }

        $.ajax({
            url: path,
            data: data
        }).done(function (result) {
            var toInsert = defaultValue;

            if (result.name) {
                toInsert = result.name;
            }

            $(toSelector).text(toInsert);
            $(toSelector).val(toInsert);
        });
    });
}