/**
 * Created by Vladislav on 25.10.2016.
 */
var UDT = UDT || {};

"use strict";

UDT.UIManager = {
    name: "uimanager",
    buttonsLocked: false,

    setSlide: function (html) {
        UDT.logger.log(UDT.UIManager.name, "setSlide");
        $(".main-slide").html(html);
    },

    progressBarRecount: function (num) {
        var all = UDT.Settings.slideCount;
        var percentage = num / all * 100;
        var $pg = $('.progress-bar');
        $pg.find('span').css('width', percentage + "%");
        var $cs = $('#progress-current-slide');
        $cs.text(num);
    },

    finalSlide: function () {
        var $nextLink = $('.next-slide');
        $nextLink.data('prevHtml', $nextLink.html());
        $nextLink.data('prevOp', $nextLink.data('op'));
        $nextLink.html('Finish Training');
        $nextLink.data('op', 'final');
    },

    noFinalSlide: function () {
        var $nextLink = $('.next-slide');
        $nextLink.html($nextLink.data('prevHtml'));
        $nextLink.data('op', $nextLink.data('prevOp'));
    },

    hidePrevButton: function () {
        var $prevLink = $('.prev-slide');
        $prevLink.parent().hide();
        var $timeLimit = $('.time-limit');
        $timeLimit.parent().removeClass('col-sm-2').addClass('col-sm-5');
    },

    showPrevButton: function () {
        var $prevLink = $('.prev-slide');
        $prevLink.parent().show();
        var $timeLimit = $('.time-limit');
        $timeLimit.parent().removeClass('col-sm-5').addClass('col-sm-2');
    },

    hideNextButton: function () {
        var $nextLink = $('.next-slide');
        $nextLink.parent().hide();
    },

    showNextButton: function () {
        var $nextLink = $('.next-slide');
        $nextLink.parent().show();
    },

    setTimer: function (str) {
        var $timeLimit = $('.time-limit');
        $timeLimit.show();
        $timeLimit.html(str)
    },

    hideTimer: function () {
        var $timeLimit = $('.time-limit');
        $timeLimit.hide();
        $timeLimit.html("00:00");
    },

    init: function () {
        UDT.logger.log(UDT.UIManager.name, "init");
        this.initEvents()
    },

    initEvents: function () {
        UDT.logger.log(UDT.UIManager.name, "init Events");
        var $slide = $('.slide');

        $slide.on('click', '.next-slide', UDT.SlideManager.getNextSlide);
        $slide.on('click', '.prev-slide', UDT.SlideManager.getPrevSlide);
        $('body')
            .on('click', '.continue-next', UDT.UIManager.modalHide)
            .on('click', '.continue-next', UDT.SlideManager.getNextSlide)
            .on('click', '.pause-presentation', UDT.SlideManager.pauseProgram)
            .on('click', '.pause-exit', function () {
                window.location.href = UDT.Communicator.mainPage;
            }).on('click', '.pause-continue', UDT.SlideManager.resumeProgram);
    },

    modalTimerLimitReached: function () {
        $('.main-slide').children().hide();
        var $modal = $('.modal');

        $modal.find('.modal-body-text')
            .html('You may go to next slide by clicking this button:');
        $modal.find('.modal-footer-button')
            .html('<button type="button" class="btn btn-primary continue-next">Continue Training</button>');
        $modal.find('.modal-title').html("Limit reached");

        $modal.modal();
    },

    modalPauseTraining: function () {
        var $modal = $('.modal');
        $('.main-slide').children().hide();

        $modal.find('.modal-body-text')
            .html('Training has been paused. You may resume it or come back to the training later');
        $modal.find('.modal-footer-button')
            .html('' +
                '<button type="button" class="btn btn-primary pause-exit">Exit</button>' +
                '<button type="button" class="btn btn-primary pause-continue">Continue</button>'
            );

        $modal.find('.modal-title').html("Training Paused");

        $modal.modal();
    },
    
    modalHide: function () {
        var $modal = $('.modal');
        $modal.modal('hide');
    },

    showErrorQuestion: function () {
        var $qe = $('.question-error');
        var msg = 'Please answer the question(s)';
        $qe.html(msg);
    },
    hideErrorQuestion: function () {
        var $qe = $('.question-error');
        $qe.html('')
    }
};

UDT.UIManager.init();