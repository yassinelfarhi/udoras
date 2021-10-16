/**
 * Created by Vladislav on 19.10.2016.
 */
var UDP = UDP || {};
"use strict";

// суть такова, каждый раз при выборе следующего слайда, я валидирую все слайды(вопросы) на заполненность полей

UDP.SlideValidator = {
    name: "SlideValidator",

    validate: function (slide) {
        switch (slide.type) {
            case UDP.SlideType.QUESTION:
                //check for 1 or more correct
                // check for not null
                //check for slide number
                var data = slide.imgHtml;
                var slideGo = data.find('#goto').val();
                if (slideGo == slide.realNum || slideGo == 0 || slideGo == '' || slide.realNum < slideGo || slideGo > UDP.SlideManager.slides.length) {
                    return false;
                }

                var questionsAnswer = false;

                for (var i = 1; i < 5; ++i) {
                    questionsAnswer = data.find('#qa' + i).is(':checked');
                    if (questionsAnswer)
                        break;
                }
                for (var i = 1; i < 5; ++i) {
                    var answerIsChecked = data.find('#qa' + i).is(':checked');
                    var questionIsEmpty = data.find('#q' + i).val().replace(/\s/g, '') === '';
                    if(answerIsChecked && questionIsEmpty){
                        return false;
                    }
                }
                for (var i = 1; i < 3; ++i) {
                    var str = data.find('#q' + i).val();
                    if (str.replace(/\s/g, '') == '') {
                        return false;
                    }
                }
                for (var i = 1; i < 5; ++i) {
                    var str = data.find('#q' + i).val();
                    if (str.replace(/\s/g, '') == '') {
                        continue;
                    }
                    for (var j = i + 1; j < 5; ++j) {
                        if (str == data.find('#q' + j).val()) {
                            return false;
                        }
                    }
                }

                if (!questionsAnswer) {
                    return false;
                }

                if (data.find('#question').val().replace(/\s/g, '') == '') {
                    return false;
                }

                break;
            default:
                return true;
        }
        return true;
    },

    renderValidation: function (slide) {
        UDP.logger.log(UDP.SlideValidator.name, "Ivalid", slide);

        var $slideHtml = slide.html;
        $slideHtml.addClass('slide-error');


        var $slideData = slide.imgHtml;
        var slideGo = $slideData.find('#goto').val();
        $slideData.find('.question-error').show();
        if (slideGo == slide.realNum || slideGo == 0 || slideGo == '' || slide.realNum < slideGo || slideGo > UDP.SlideManager.slides.length) {
            $slideData.find('#goto').parent().addClass('has-error');
            $slideData.find('.question-error-goto').show();
        }

        var questionsAnswer = false;

        for (var i = 1; i < 5; ++i) {
            questionsAnswer = $slideData.find('#qa' + i).is(':checked');
            if (questionsAnswer)
                break;
        }
        for (var i = 1; i < 5; ++i) {
            var $question = $slideData.find('#q' + i);
            var answerIsChecked = $slideData.find('#qa' + i).is(':checked');
            var questionIsEmpty = $question.val().replace(/\s/g, '') === '';
            if (answerIsChecked && questionIsEmpty) {
                $question.parent().addClass('has-error');
                $slideData.find('.question-error-empty').show();
            }
        }
        for (var i = 1; i < 3; ++i) {
            var str = $slideData.find('#q' + i).val();
            if (str.replace(/\s/g, '') == '') {
                $slideData.find('#q' + i).parent().addClass('has-error');
                $slideData.find('.question-error-empty').show();
            }
        }
        for (var i = 1; i < 5; ++i) {
            var str = $slideData.find('#q' + i).val();
            if (str.replace(/\s/g, '') == '') {
                continue;
            }
            for (var j = i + 1; j < 5; ++j) {
                if (str == $slideData.find('#q' + j).val()) {
                    $slideData.find('#q' + i).parent().addClass('has-error');
                    $slideData.find('#q' + j).parent().addClass('has-error');
                    $slideData.find('.question-error-duplicate').show();
                    break;
                }
            }
        }

        if (!questionsAnswer) {
            $slideData.find('#answer-label').addClass('error-has');
            $slideData.find('.question-error-answer').show();
        }
        if ($slideData.find('#question').val().replace(/\s/g, '') == '') {
            $slideData.find('#question').parent().addClass('has-error');
            $slideData.find('.question-error-empty').show();
        }

    },

    removeValidationMessages: function () {
        /** NOP */
    },

    removeValidationMsg: function (slide) {
        UDP.logger.log(UDP.SlideValidator.name, "Remove Validation");
        if (slide.type == UDP.SlideType.BLANK) {
            return;
        }
        var $slideHtml = slide.html;
        var $slideData = slide.imgHtml;

        $slideHtml.removeClass('slide-error');

        if($slideData) {
            $slideData.find('.error-has').removeClass('error-has');
            $slideData.find('.has-error').removeClass('has-error');

            $slideData.find('.question-error').hide();
            $slideData.find('.question-error-empty').hide();
            $slideData.find('.question-error-goto').hide();
            $slideData.find('.question-error-answer').hide();
            $slideData.find('.question-error-duplicate').hide();
        }
    },

    validateSlides: function () {
        UDP.logger.log(UDP.SlideValidator.name, "Validate slides");
        var state = true;
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                UDP.SlideValidator.removeValidationMsg(value);
                if (!UDP.SlideValidator.validate(value)) {
                    state = false;
                    UDP.SlideValidator.renderValidation(value);
                } else {
                    UDP.SlideValidator.removeValidationMsg(value);
                }
            }
        }

        return state;
    },

    getFirstInvalid: function () {
        UDP.logger.log(UDP.SlideValidator.name, "Validate slides");
        var state = true;
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (!UDP.SlideValidator.validate(value)) {
                    return value
                }
            }
        }
        return state;
    },

    init: function () {
        UDP.logger.log(UDP.SlideValidator.name, "init");
        this.initEvent();
    },

    initEvent: function () {
        var $slides = $('.slides');
        $slides
            .on('click', '.slide', this.validateSlides)
        ;
    }
};


UDP.SlideValidator.init();