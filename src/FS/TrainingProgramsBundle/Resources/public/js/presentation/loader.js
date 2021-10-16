/**
 * Created by Vladislav on 18.10.2016.
 */
//$.unblockUI


var UDP = UDP || {};
"use strict";

UDP.Loader = {
    name: 'Loader',

    load :function () {
        UDP.Communicator.sendPresentation({command:'slides'}, function (data) {
            var slides = data.data;
            slides.forEach(function (current, index, arr) {
               UDP.Loader.addSlide(current);
            });
            console.log("unblock");
            $.unblockUI();
        });
        ////
    },

    addSlide: function (slideData) {
        var $slides = $('.slides');
        var $addSlide = $('.add-slide');

        var slide = {
            id: slideData.id,
            type: slideData.slideType,
            timeLimit: slideData.timeLimit,
            realNum:slideData.realNum,
            html:
                $('<div class="slide-warp">' +
                    '<div class="slide " data-id="' + slideData.id + '" >' +
                    '<div class="slide-number">1</div>' +
                    '<span class="slide-remove" data-id="' + slideData.id + '" data-op="remove">&times;</span>' +
                    '<div class="slide-type">Blank Slide</div>' +
                    '<div class="slide-time-limit">00:00</div>' +
                    '</div>' +
                    '</div>')
        };

        var index = UDP.SlideManager.slides.length;

        $addSlide.data('slick-index', UDP.SlideManager.slides.length);

        $slides.slick('slickAdd', slide.html, index, true);

        switch(slide.type){
            case UDP.SlideType.TEXT:
                UDP.Loader.addTextSlide(slide, slideData);
                break;
            case UDP.SlideType.IMAGE:
                UDP.Loader.addImgSlide(slide,slideData);
                break;
            case UDP.SlideType.VIDEO:
                UDP.Loader.addVideoSlide(slide, slideData);
                break;
            case UDP.SlideType.AUDIO:
                UDP.Loader.addAudioSlide(slide, slideData);
                break;
            case UDP.SlideType.QUESTION:
                UDP.Loader.addQuestionSlide(slide, slideData);
                break;
            case UDP.SlideType.BLANK:
            default:
                break;
        }
        var time = slide.timeLimit;
        var min = Math.floor(time / 60);
        var sec = Math.floor(time - min * 60);

        var str = "_%minute%_:_%seconds%_";
        if(+min < 10){
            min = "0" + min;
        }
        if(+sec < 10){
            sec = "0" + sec;
        }

        slide.html.find('.slide-time-limit').html(str.replace('_%minute%_', min).replace("_%seconds%_", sec));
        if(slide.timeLimit > 0){
            slide.html.find('.slide-time-limit').show();
        }

        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
            slide: slide
        });
        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_ADD,
            slide: slide
        });

    },

    init: function () {
        UDP.logger.log(UDP.Loader.name, "init");
        this.blockView();
        this.load();
    },

    blockView: function () {
        UDP.logger.log(UDP.Loader.name, "Block");
        $.blockUI({ css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        } });
    },
    
    addTextSlide: function (slide, sd) {
        var $tx = $('<textarea class="slide-text-area form-control" maxlength="1000"></textarea>');
        $tx.val(sd.slideData);
        slide.imgHtml = $tx;
    },



    addVideoSlide: function (slide, sd) {
        slide.attr = sd.extraFields;
        slide.video = sd.slideData;
        if(isNaN(slide.attr.top)){
            slide.attr.top = 0;
        }
        var h = slide.attr.height;
        var w = slide.attr.width;
        var t = slide.attr.top;


        slide.imgHtml = $('<div style="' +
            'height: ' + h + '%; width: ' + w + '%; top:' + t + '%;">' +
            '<video class="slide-video-type" style="' +
            'height: ' + h + '%; width: ' + w + '%; top:' + t + '%;" ' +
            ' controls>' +
            '<source type="video/mp4" src="' + slide.video.replace('.webm', '.mp4') + '">' +
            '<source type="video/webm" src="' + slide.video + '">' +
            '</video></div>');
    },



    addImgSlide: function (slide, sd) {
        slide.attr = sd.extraFields;
        slide.img = sd.slideData;

        slide.imgHtml = $('' +
            '<img class="slide-image-type" style="' +
            'height: ' + slide.attr.height + '%; width: ' + slide.attr.width + '%; top:' + slide.attr.top + '%;" ' +
            'src="' + slide.img + '"/>');

    },


    addAudioSlide: function (slide, sd) {
        slide.attr = sd.extraFields;
        slide.audio = sd.slideData;
        slide.imgHtml = $('<div class="slide-audio-type"><p>' + slide.attr.audioName + '</p><audio src="' + slide.audio + '" controls>' + '</audio></div>');

    },



    addQuestionSlide: function (slide, sd) {
        var html = '<div class="slide-question-type"> <div class="question-area"><p><b>Question</b></p> <textarea id="question" class="form-control" rows="7" maxlength="512"></textarea></div> <div class="answers-area"> <div class="answers-input"> <div class="row"> <div class="col-sm-6"><p><b>Answers</b></p></div> <div id="answer-label" class="col-sm-6"><p><b style="float: right">Correct</b></p></div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q1" id="q1" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa1"> <label for="qa1"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q2" id="q2" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa2"> <label for="qa2"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q3" id="q3" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa3"> <label for="qa3"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q4" id="q4" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa4"> <label for="qa4"></label></div> </div> </div> </div> </div> <div class="bottom-area row"><label for="goto" class="col-sm-5 control-label"> If answered incorrect, user should be moved to slide number </label> <div class="col-sm-2"><input type="number" min="1" class="form-control" id="goto"></div> </div> <div> <div class="alert alert-danger question-error"> <div class="question-error-empty">Please, fill empty fields</div> <div class="question-error-answer">Please, select the correct answer(s)</div> <div class="question-error-goto">Please, select the valid slide number</div> <div class="question-error-duplicate">Duplicate answers</div> </div> </div> </div>';
        var $slideAria = $('.slide-full');
        slide.imgHtml = $(html);

        //here we add data
        slide.attr = sd.extraFields;
        slide.imgHtml.find('#question').val(slide.attr.question);
        slide.imgHtml.find('#goto').val(slide.attr.goToSlide);
        for (var i = 0; i < slide.attr.answers.length; ++i) {
            var data = slide.attr.answers[i];
            slide.imgHtml.find('#q' + (+i+1)).val(data.answer);
            if(data.correct == "true"){
                slide.imgHtml.find('#qa' + (+i+1)).prop('checked', true);
            }

        }
    }


};