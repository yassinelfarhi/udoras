/**
 * Created by Vladislav on 07.10.2016.
 */
var UDP = UDP || {};
"use strict";

UDP.UIManager = {
    name: "UIManager",


    addSlideClickHandler: function (event) {
        UDP.UIManager.log('add slide click');
        UDP.Communicator.sendPresentation({command: "add_slide"}, function (data) {
            var $slides = $('.slides');
            var $addSlide = $('.add-slide');

            var slide = {
                id: data.data,
                type: UDP.SlideType.BLANK,
                timeLimit: 0,
                realNum: 0,
                html: $('<div class="slide-warp">' +
                    '<div class="slide " data-id="' + data.data + '" >' +
                    '<div class="slide-number">1</div>' +
                    '<span class="slide-remove" data-id="' + data.data + '" data-op="remove">&times;</span>' +
                    '<div class="slide-type">Blank Slide</div>' +
                    '<div class="slide-time-limit">00:00</div>' +
                    '</div>' +
                    '</div>')
            };
            var index = UDP.SlideManager.slides.length;

            $addSlide.data('slick-index', UDP.SlideManager.slides.length);

            $slides.slick('slickAdd', slide.html, index, true);
            $.event.trigger({
                type: UDP.SlideEvent.SLIDE_ADD,
                slide: slide
            });
            slide.html.find('.slide').click();

            return false;
        })
    },


    selectSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        // set "active"
        var $this = $(this),
            $slideTL = $('#slide-time-limit'),
            slickTrack = $('.slick-track');

        $slideTL.prop('disabled', false);
        // This row for fix bug, when "Add Slide" block jumps
        slickTrack.css({width: (parseInt(slickTrack.width()) + 10) + 'px'});
        $('.active').removeClass('active');
        $this.parent().addClass("active");
        UDP.UIManager.log('get slide');
        //here we make slide active and add to global active;
        var id = $this.data('id');
        UDP.settings.globalActive = id;
        var slide = UDP.SlideManager.slides[id];
        var $slideAria = $('.slide-full');

        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_ACTIVE,
            slide: slide
        });
        var time = slide.timeLimit;
        var minutes = Math.floor(time / 60);
        var seconds = Math.floor(time - minutes * 60);

        if (minutes.toString().length < 2) {
            minutes = "0" + minutes;
        }
        if (seconds.toString().length < 2) {
            seconds = "0" + seconds;
        }
        $slideTL.mask("999:99");
        $slideTL.val(minutes + ":" + seconds);
        if (slide.timeLimit > 0) {
            slide.html.find('.slide-time-limit').show();
        }
        switch (slide.type) {
            case UDP.SlideType.VIDEO:
                $slideAria.html(slide.imgHtml);
                slide.imgHtml.resizable({
                    containment: ".slide-full",
                    handles: 'n, s, e, se, ne',
                    create: function (e, ui) {
                        var parent = $(this).parent();
                        var video = $(this).find('video');
                        var width = $(this).width() / parent.width() * 100 + "%";
                        var height = $(this).height() / parent.height() * 100 + "%";

                        $(this).css({
                            width: width,
                            height: height
                        });

                        var top = parseInt($(this).css('top')) / parent.height() * 100;
                        $(this).css('top', top + "%");
                        video.css({
                            width: 100 + "%",
                            height: 100 + "%",
                            top: 0 + '%'
                        });
                    },
                    stop: function (e, ui) {
                        var parent = ui.element.parent();
                        var video = ui.element.find('video');
                        var width = ui.element.width() / parent.width() * 100 + "%";
                        var height = ui.element.height() / parent.height() * 100 + "%";

                        var top = parseInt(ui.element.css('top')) / parent.height() * 100;
                        ui.element.css('top', top + "%");

                        ui.element.css({
                            width: width,
                            height: height
                        });

                        video.css({
                            width: 100 + "%",
                            height: 100 + "%",
                            top: 0 + '%'
                        });
                    }
                });
                break;
            case UDP.SlideType.IMAGE:
                $slideAria.html(slide.imgHtml);
                slide.imgHtml.resizable({
                    containment: ".slide-full",
                    handles: 'n, s, e, se, ne',
                    create: function (e, ui) {
                        var parent = $(this).parent();
                        var img = $(this).find('img');
                        var width = $(this).width() / parent.width() * 100 + "%";
                        var height = $(this).height() / parent.height() * 100 + "%";

                        $(this).css({
                            width: width,
                            height: height
                        });

                        var top = parseInt($(this).css('top')) / parent.height() * 100;
                        $(this).css('top', top + "%");
                        img.css({
                            width: 100 + "%",
                            height: 100 + "%",
                            top: 0 + '%'
                        });

                    },
                    stop: function (e, ui) {
                        var parent = ui.element.parent();
                        var img = ui.element.find('img');
                        var width = ui.element.width() / parent.width() * 100 + "%";
                        var height = ui.element.height() / parent.height() * 100 + "%";

                        var top = parseInt(ui.element.css('top')) / parent.height() * 100;
                        ui.element.css('top', top + "%");

                        img.css({
                            width: 100 + "%",
                            height: 100 + "%",
                            top: 0 + '%'
                        });

                        ui.element.css({
                            width: width,
                            height: height
                        });
                    }
                });

                break;
            case UDP.SlideType.TEXT:
            case UDP.SlideType.AUDIO:
            case UDP.SlideType.QUESTION:
                $slideAria.html(slide.imgHtml);
                break;
            case UDP.SlideType.BLANK:
            default:
                break
        }
        return false;
    },

    removeSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        var $this = $(this);
        var id = $this.data('id');
        UDP.logger.log(UDP.UIManager.name, "remove slide");

        var $slides = $('.slides');
        var slide = UDP.SlideManager.slides[id];
        $slides.slick('slickRemove', slide.realNum - 1);

        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_REMOVE,
            slide: slide
        });
        return false;
    },

    addTextSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add text');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddText();
        }

    },

    addImageSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add image');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddImage();
        }
    },

    addVideoSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add video');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddVideo();
        }
    },

    addAudioSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add audio');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddAudio();
        }
    },

    addNarrationClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add narration');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddNarration();
        }
    },

    addQuestionSlideClickHandler: function (event) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        UDP.UIManager.log('slide add question');
        if (UDP.SlideManager.activeSlide) {
            UDP.SlideManager.slideAddQuestion();
        }
    },

    clearSlideArea: function (event) {
        if (UDP.SlideManager.encoding) {
            UDP.UIManager.showCancelDialog(function () {
                UDP.SlideManager.cancelEncoding();
                $(event.target).trigger(event);
            });
        } else {
            var $slideAria = $('.slide-full');
            $slideAria.html('');
        }
    },

    showCancelDialog: function (cbYes) {
        $('#modal-sure-cancel-encoding').modal();
        $('#modal-sure-cancel-encoding-yes').off('click').on('click', cbYes);
    },


    init: function () {
        this.log(self.name, "init");
        this.initControl();
        this.initSlides();
        this.initOther();
    },

    initControl: function () {
        var $slideControl = $('.slide-control');
        // init cleaner
        $slideControl
            .on('click', '.slide-add-text', this.clearSlideArea)
            .on('click', '.slide-add-image', this.clearSlideArea)
            .on('click', '.slide-add-video', this.clearSlideArea)
            .on('click', '.slide-add-audio', this.clearSlideArea)
            .on('click', '.slide-add-question', this.clearSlideArea)
        ;
        $slideControl
            .on('click', '.slide-add-text', this.addTextSlideClickHandler)
            .on('click', '.slide-add-image', this.addImageSlideClickHandler)
            .on('click', '.slide-add-video', this.addVideoSlideClickHandler)
            .on('click', '.slide-add-audio', this.addAudioSlideClickHandler)
            .on('click', '.slide-add-question', this.addQuestionSlideClickHandler);
        $(document).on('click', '.slide-add-narration', this.addNarrationClickHandler);

    },

    initSlides: function () {
        var $slides = $('.slides');
        // init cleaner
        $slides
            .on('click', '.slide', this.clearSlideArea)
        ;
        $slides
            .on('click', '.add-slide', this.addSlideClickHandler)
            .on('click', '.slide', this.selectSlideClickHandler)
            .on('click', '.slide-remove', this.removeSlideClickHandler)
        ;

    },

    initOther: function () {
        $(document).on(UDP.SlideEvent.SLIDE_TYPE_CHANGED, function (event) {
            var slide = event.slide;
            var $slideType = slide.html.find('.slide-type');
            UDP.logger.log(UDP.UIManager.name, "type change", slide);
            switch (slide.type) {
                case UDP.SlideType.TEXT:
                    $slideType.html('<span class="slide-icon-text slide-icon" aria-hidden="true"></span>');
                    break;
                case UDP.SlideType.IMAGE:
                    $slideType.html('<span class="slide-icon-image slide-icon" aria-hidden="true"></span>');
                    break;
                case UDP.SlideType.VIDEO:
                    $slideType.html('<span class="slide-icon-video slide-icon" aria-hidden="true"></span>');
                    break;
                case UDP.SlideType.AUDIO:
                    $slideType.html('<span class="slide-icon-audio slide-icon" aria-hidden="true"></span>');
                    break;
                case UDP.SlideType.QUESTION:
                    $slideType.html('<span class="slide-icon-question slide-icon" aria-hidden="true"></span>');
                    break;
                case UDP.SlideType.BLANK:
                    $slideType.html('Blank Slide');
                    break;
                default:
                    break;
            }
        });
        $(window).on('beforeunload', function () {
            if (UDP.SlideManager.encoding) {
                return "Your file is still uploading. Are you sure you want to cancel upload?";
            }
        });

    },


    log: function (message) {
        UDP.logger.log(UDP.UIManager.name, message);
    }
};
UDP.UIManager.init();