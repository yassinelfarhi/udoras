(function () {

    $(document).on('click', 'button.validateOn', function (event) {
        var modal = $('#errorsModal'),
            elementsForValidation = $(this).closest('form').find('.validate'),
            pairsForValidation = $(this).closest('form').find('.validate-pair'),
            elementsErrors = validateElements(elementsForValidation),
            pairsErrors = validatePairs(pairsForValidation);

        if (elementsErrors.length > 0 || pairsErrors.length > 0) {
            modal.find('.modal-body').html(renderErrors(pairsErrors.concat(elementsErrors)));
            $(modal).modal({backdrop: false});
            event.preventDefault();
        }
    });

    function validateElements(elements) {
        var errors = [];

        for (var index = 0; index < elements.length; index++) {
            var element = $(elements[index]),
                isValid = validateElement(element);

            if (!isValid) {
                errors.push(element.next('.validate-error').text());
            }
        }

        return errors;
    }

    function validateElement(element) {
        if (element.hasClass('validate-number')) {
            if (isValidNumber(element.val())) {
                return true;
            }

            element.val('');

            return false;
        }

        return true;
    }


    function validatePairs(pairs) {
        var errors = [];

        for (var index = 0; index < pairs.length; index++) {
            var pair = $(pairs[index]),
                isValid = validatePair(pair);

            if (!isValid) {
                errors.push(pair.find('.validate-pair-error').text());
            }
        }

        return errors;
    }

    function validatePair(pair) {
        var forValidation = pair.find('.validate'),
            first = $(forValidation[0]),
            second = $(forValidation[1]);

        if (first.hasClass('validate-number')) {
            var firstFloat = parseFloat(first.val()),
                secondFloat = parseFloat(second.val());

            if (firstFloat > 0 && secondFloat > 0 && firstFloat >= secondFloat) {
                first.val('');
                second.val('');

                return false;
            }

            return true;
        } else if (first.hasClass('validate-date')) {
            var firstTime = (new Date(first.val())).getTime(),
                secondTime = (new Date(second.val())).getTime();

            if (firstTime && secondTime && firstTime > secondTime) {
                first.val('');
                second.val('');

                return false;
            }

            return true;
        }

        return true;
    }

    function isValidNumber(number) {
        var reg = /^[+-]?\d+(\.\d+)?$/;

        return (number.length > 0) ?reg.test(number) : true;
    }

    function renderErrors(errors) {
        var result = $("<ul></ul>");

        for (var index = 0; index < errors.length; index++) {
            if (errors[index].length > 0) {
                result.append($("<li class='text-danger'>" + errors[index] + "</li>"));
            }
        }

        return result;
    }

})();
