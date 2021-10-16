/**
 * clear SessionStorage from Employees
 */
$(document).on('click', '.ajax-modal', function () {
    saveItemToStorage('employees', []);
});


/**
 * add Employee to request
 */
$(document).on('click', '.employee', function (event) {
    var checkbox = $(this).find('input.request[type="checkbox"]'),
        employee = checkbox.val();

    checkbox.prop('checked', function(_, attr) { return !attr});

    if (checkbox.is(':checked')) {
        saveEmployeeToStorage(employee);
    } else {
        removeEmployeeFromStorage(employee);
    }

    updateSendButton();
});

$(document).on('click', 'input.request[type="checkbox"]', function (event) {
    updateSendButton();
});

/**
 * add class to selected vendor
 */
$(document).on('click', '.vendor', function (event) {
    $('.vendor').parent('.item').removeClass('checked');
    $(this).parent('.item').addClass('checked');
});

/**
 * update Employees by selecting vendor
 */
$(document).on('click', 'a.vendor-ajax-link', function (event) {
    var url = $(this).attr('href'),
        currentLink = $(this);


    $.ajax({
        url: url
    }).done(function (result) {
        $('.employees').html(result);
        updateCheckBoxes();
        updateSendButton();
        $('.vendors .item').removeClass('checked');
        currentLink.closest('.item').addClass('checked');
    });

    event.preventDefault();
});

$(document).on('click', '.create-training-requests', function (event) {
    event.preventDefault();
    var url = $(this).attr('href'),
        vendors = $('.vendor input.request[type="checkbox"]:checked'),
        employees = $('.employee input.request[type="checkbox"]:checked'),
        storageEmployees = getItemFromStorage('employees', true);

    if (vendors.length || employees.length || storageEmployees.length) {
        vendors = getIds(vendors);
        employees = mergeArrays(getIds(employees), storageEmployees);

        $.ajax({
            url: url,
            method: 'POST',
            data: {vendors: vendors, employees: employees}
        }).done(function(response) {
            $('.requests').html(response);
        }).fail(function() {
            location.reload();
        });
    }
});

/**
 * Helper function
 */
function updateSendButton() {
    if ($('input.request[type="checkbox"]:checked').length || getItemFromStorage('employees', true).length) {
        $('.create-training-requests').removeAttr('disabled');
    } else {
        $('.create-training-requests').attr('disabled', 'disabled');
    }
}

function updateCheckBoxes() {
    var checkBoxes = $('.employees input.request[type="checkbox"]');

    checkBoxes.each(function (index) {
        var checkBox = $(checkBoxes[index]),
            employees = getItemFromStorage('employees', true);

        if (employees.indexOf(checkBox.val()) != -1) {
            checkBox.attr('checked', true);
            checkBox.prop('checked', true);
        }
    });
}

function saveEmployeeToStorage(employee) {
    var employees = getItemFromStorage('employees', true);

    if (employees.indexOf(employee) == -1) {
        employees.push(employee);
        saveItemToStorage('employees', employees);
    }
}

function removeEmployeeFromStorage(employee) {
    var employees = getItemFromStorage('employees', true),
        index = employees.indexOf(employee);

    if (index > -1) {
        employees.splice(index);
        saveItemToStorage('employees', employees);
    }
}

function getItemFromStorage(itemName, isArray) {
    var item = JSON.parse(window.sessionStorage.getItem(itemName));

    if (item == null) {
        item = isArray ? [] : {};
    }

    return item;
}

function saveItemToStorage(itemName, data) {
    window.sessionStorage.setItem(itemName, JSON.stringify(data));
}

function getIds(elements) {
    var data = [];

    elements.each(function (index) {
        var elem = elements[index],
            id = elem.value;

        if (id && data.indexOf(id) == -1) {
            data.push(id);
        }
    });

    return data;
}

function mergeArrays(first, second) {
    return first.concat(second.filter(function (item) {
        return first.indexOf(item) == -1;
    }));
}