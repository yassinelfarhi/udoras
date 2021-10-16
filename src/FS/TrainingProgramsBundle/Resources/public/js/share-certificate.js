$(document).on('click', '.ajax-submit', function () {
    validateEmails();
});

$(document).on('change keyup input', '.email input[type="email"]', function (event) {
    validateEmails();
});

$(document).on('click', 'button.add-email', function (event) {
    event.preventDefault();

    var emailInputs = $('.email.hidden');

    if (emailInputs.length > 0) {
        $(emailInputs[0]).removeClass('hidden');
        $('button.delete-email.hidden').removeClass('hidden');

        if (emailInputs.length == 1) {
            $(this).attr('disabled', 'disabled');
        }
    }
    $(this).blur();
});

$(document).on('click', 'button.delete-email', function (event) {
    event.preventDefault();

    var emailInputs = $('.email:not(.hidden)');

    if (emailInputs.length > 1) {
        var email = $(this).closest('.form-group.email');

        email.addClass('hidden');
        email.find('input.form-control').val('');
        $('.emails').append(email.clone());
        email.remove();
    }

    if (emailInputs.length == 2) {
        $(emailInputs).find('button.delete-email').addClass('hidden');
    }

    $('button.add-email[disabled="disabled"]').removeAttr('disabled');

    validateEmails();
});

function validateEmails() {
    var submitButton = $('button.ajax-submit'),
        emailInputs = $('.email:not(.hidden) input[type="email"]'),
        validEmailsCounter = 0;

    for (var i = 0; i < emailInputs.length; i++) {
        var emailInput = $(emailInputs[i]),
            emailFormGroup = emailInput.closest('.form-group.email');

        if (isEmailValid(emailInput.val()) && !isEmailRepeats(emailInput.val(), emailInputs)) {
            emailFormGroup.removeClass('has-error');
            validEmailsCounter += 1;
        } else {
            emailFormGroup.addClass('has-error');
        }
    }

    if (emailInputs.length > 0 && emailInputs.length == validEmailsCounter) {
        submitButton.removeAttr('disabled');
    } else {
        submitButton.attr('disabled', 'disabled');
    }
}

function isEmailValid(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function isEmailRepeats(email, emailInputs) {
    var counter = 0;

    for (var i = 0; i < emailInputs.length; i++) {
        var anotherEmail = $(emailInputs[i]).val();

        if (anotherEmail.toLowerCase() == email.toLowerCase()) {
            counter += 1;
        }

        if (counter > 1) {
            return true;
        }
    }

    return false;
}