/**
 * Created by Vladislav on 07.10.2016.
 */
var UDP = UDP || {};
"use strict";

UDP.SlideManager = {
    name: "SlideManager",
    slides: {
        length: 0
    },
    activeSlide: null,
    checkInterval: null,
    encoding: false,


    addSlide: function (slide) {
        UDP.SlideManager.slides[slide.id] = slide;
        ++UDP.SlideManager.slides.length;
        if (slide.realNum == 0) {
            slide.realNum = this.slides.length;
        }

        UDP.logger.log(this.name, "slide add", slide);
        UDP.SlideManager.slideNumRecount();
    },


    removeSlide: function (slide) {

        UDP.logger.log(this.name, "remove", slide);
        slide.html.remove();
        var sm = UDP.SlideManager;

        sm.questionSlideRemoveIndex(slide);

        sm.slideNumOffsetRecount(slide);

        var num = +slide.realNum;
        delete sm.slides[slide.id];
        --sm.slides.length;
        sm.slideNumRecount();

        if (sm.activeSlide == slide) {
            if (num >= sm.slides.length) {
                num = sm.slides.length
            }

            if (num > 0) {
                var newSlide = sm.getSlideByNum(num);
                newSlide.html.find('.slide').trigger('click')
            }
            else {
                var $slideAria = $('.slide-full');
                $slideAria.html('');
                sm.activeSlide = null;
            }

        }

        UDP.Communicator.sendSlide(slide.id, {command: "remove"}, function (data) {
            UDP.logger.log(sm.name, "ok", data);
        });
    },


    slideAddText: function () {
        var $slideAria = $('.slide-full');
        var $tx = $('<textarea class="slide-text-area form-control" maxlength="1000"></textarea>');
        var slide = UDP.SlideManager.activeSlide;
        /*if (slide.type == UDP.SlideType.TEXT) {
         $($tx[0]).val(slide.text)
         }*/
        $slideAria.html($tx);
        slide.type = UDP.SlideType.TEXT;
        slide.imgHtml = $tx;
        /*$slideAria.on('input propertychange', ".slide-text-area", function (event) {
         UDP.SlideManager.activeSlide.text = this.value;
         });*/
        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
            slide: slide
        });
    },

    slideAddNarration: function () {
        console.trace('slideAddNarration');
        var $fileManager = $('.slide-narration-file');
        $fileManager.trigger('click');
        $fileManager.off('change');
        $fileManager.change(function () {
            UDP.SlideManager.encoding = true;
            var slide = UDP.SlideManager.activeSlide;
            if ($fileManager[0].files.length > 0 || $fileManager.val() != '') {
                var Extension = $fileManager.val().substring($fileManager.val().lastIndexOf('.') + 1).toLowerCase();
                var size = $fileManager[0].files[0].size;

                if ((Extension == "mp3" || Extension == "ogg" ) && ((size / 1048576) <= 20)) {

                    var fData = new FormData();

                    fData.append('command', "add_narration");
                    fData.append('file', $fileManager[0].files[0]);

                    $fileManager.val('');
                    $fileManager[0].files = null;
                    var oldHtml = $('.slide-full').children().detach();
                    $('.slide-full').html('<img class="slide-loading" src="/bundles/fsjsextra/font/spinner/loading.svg?r='+$.now()+'">');
                    UDP.Communicator.sendSlide(slide.id, fData, function (data) {
                        var slide = UDP.SlideManager.activeSlide;
                        slide.attr = data.data.extraFields;
                        if (slide.attr.encoding_id) {
                            UDP.SlideManager.checkInterval = setInterval(function () {
                                UDP.Communicator.send(
                                    Routing.generate('get_encoding_status', {item: slide.attr.encoding_id}),
                                    null,
                                    function (response) {
                                        if (response && response.status === 'encoded') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            slide.attr.audio = response.path;
                                            onNarrationReady();
                                        } else if (response && response.status === 'error') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            alert('Media conversion error');
                                            onNarrationReady();
                                        }
                                    }
                                )
                            }, 1000);
                        } else {
                            onNarrationReady();
                        }
                        function onNarrationReady() {
                            UDP.SlideManager.encoding = false;
                            $('.slide-full').empty().append(oldHtml);
                            var $slideAria = $('.slide-narration');
                            var $img = $('<audio src="' + slide.attr.audio + '" controls></audio>');

                            $slideAria.html($img);
                        }
                    });
                } else {
                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('#modal-error-content').html('Only audio (MP3, OGG), up to 20MB');
                    UDP.SlideManager.encoding = false;
                    $('#modal-error').modal();
                }
            }
        });
    },

    slideAddImage: function () {
        var $fileManager = $('.slide-add-image-file');
        $fileManager.trigger('click');
        $fileManager.off('change');
        $fileManager.change(function () {
            var slide = UDP.SlideManager.activeSlide;
            if ($fileManager[0].files.length > 0 || $fileManager.val() != '') {
                var Extension = $fileManager.val().substring($fileManager.val().lastIndexOf('.') + 1).toLowerCase();
                var size = $fileManager[0].files[0].size;

                if ((Extension == "gif" || Extension == "png" || Extension == "bmp"
                    || Extension == "jpeg" || Extension == "jpg") && ((size / 1048576) <= 2)) {

                    var fData = new FormData();

                    fData.append('command', "add_image");
                    fData.append('file', $fileManager[0].files[0]);

                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('.slide-full').html('<img class="slide-loading" src="/bundles/fsjsextra/font/spinner/loading.svg?r='+$.now()+'">');
                    UDP.Communicator.sendSlide(slide.id, fData, function (data) {
                        var slide = UDP.SlideManager.activeSlide;
                        slide.type = UDP.SlideType.IMAGE;
                        slide.img = data.data.slideData;

                        slide.attr = data.data.extraFields;

                        var $slideAria = $('.slide-full');
                        var $img = $('' +
                            '<img class="slide-image-type" style="' +
                            'height: ' + slide.attr.height + 'px; width: ' + slide.attr.width + 'px; top:' + slide.attr.top + 'px;" ' +
                            //'height: ' + 100 + '%; width: ' + 100 + '%; top:' + slide.attr.top + 'px;" ' +
                            'src="' + slide.img + '"/>');
                        $slideAria.html($img);
                        var maxWidth = $slideAria.width(); // Max width for the image
                        var maxHeight = $slideAria.height();    // Max height for the image
                        var ratio = 0;  // Used for aspect ratio
                        var width = slide.attr.width;    // Current image width
                        var height = slide.attr.height;  // Current image height

                        if (width > maxWidth) {
                            ratio = maxWidth / width;   // get ratio for scaling image
                            $img.css("width", maxWidth); // Set new width
                            $img.css("height", height * ratio);  // Scale height based on ratio
                            height = height * ratio;    // Reset height to match scaled image
                            width = width * ratio;    // Reset width to match scaled image
                        }

                        // Check if current height is larger than max
                        if (height > maxHeight) {
                            ratio = maxHeight / height; // get ratio for scaling image
                            $img.css("height", maxHeight);   // Set new height
                            $img.css("width", width * ratio);    // Scale width based on ratio
                            width = width * ratio;    // Reset width to match scaled image
                            height = height * ratio;    // Reset height to match scaled image
                        }

                        if (height < maxHeight) {
                            var imgMiddle = height / 2;
                            var maxHeightMiddle = maxHeight / 2;
                            $img.css("top", maxHeightMiddle - imgMiddle);
                        }


                        $img.resizable({
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

                        var eastbar = $img.parent().find(".ui-resizable-handle.ui-resizable-e").first();
                        var pageX = eastbar.offset().left;
                        var pageY = eastbar.offset().top;

                        (eastbar.trigger("mouseover")
                            .trigger({type: "mousedown", which: 1, pageX: pageX, pageY: pageY})
                            .trigger({type: "mousemove", which: 1, pageX: pageX - 1, pageY: pageY})
                            .trigger({type: "mousemove", which: 1, pageX: pageX, pageY: pageY})
                            .trigger({type: "mouseup", which: 1, pageX: pageX, pageY: pageY}));

                        slide.imgHtml = $img;

                        $.event.trigger({
                            type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
                            slide: slide
                        });
                    });
                } else {
                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('#modal-error-content').html('Only image (JPG, PNG, BMP), up to 2MB');
                    $('#modal-error').modal();
                }
            }
        })
    },

    slideAddVideo: function () {
        var $fileManager = $('.slide-add-video-file');
        $fileManager.trigger('click');
        $fileManager.off('change');
        $fileManager.change(function () {
            UDP.SlideManager.encoding = true;
            var slide = UDP.SlideManager.activeSlide;
            if ($fileManager[0].files.length > 0 || $fileManager.val() != '') {
                var Extension = $fileManager.val().substring($fileManager.val().lastIndexOf('.') + 1).toLowerCase();
                var size = $fileManager[0].files[0].size;

                if ((Extension == "mp4" || Extension == "ogv" || Extension == "ogg" || Extension == "webm" ) && ((size / 1048576) <= 20)) {

                    var fData = new FormData();

                    fData.append('command', "add_video");
                    fData.append('file', $fileManager[0].files[0]);

                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('.slide-full').html('<img class="slide-loading" src="/bundles/fsjsextra/font/spinner/loading.svg?r='+$.now()+'">');
                    UDP.Communicator.sendSlide(slide.id, fData, function (data) {
                        var slide = UDP.SlideManager.activeSlide;
                        slide.type = UDP.SlideType.VIDEO;
                        slide.video = data.data.slideData;

                        slide.attr = data.data.extraFields;

                        if (slide.attr.encoding_id) {
                            UDP.SlideManager.checkInterval = setInterval(function () {
                                UDP.Communicator.send(
                                    Routing.generate('get_encoding_status', {item: slide.attr.encoding_id}),
                                    null,
                                    function (response) {
                                        if (response && response.status === 'encoded') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            slide.video = response.path;
                                            onVideoReady();
                                        } else if (response && response.status === 'error') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            alert('Media conversion error');
                                            onVideoReady();
                                        }
                                    }
                                )
                            }, 1000);
                        } else {
                            onVideoReady();
                        }

                        function onVideoReady() {
                            UDP.SlideManager.encoding = false;
                            var $slideAria = $('.slide-full');
                            var $img = $('<div><video class="slide-video-type" style="' +
                                'height: ' + slide.attr.height + '%; width: ' + slide.attr.width + '%; top:' + slide.attr.top + 'px;" ' +
                                ' controls>' +
                                '<source type="video/mp4" src="' + slide.video.replace('.webm', '.mp4') + '">' +
                                '<source type="video/webm" src="' + slide.video + '">' +
                                '</video></div>');
                            $slideAria.html($img);
                            $img.resizable({
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
                            var eastbar = $img.find(".ui-resizable-handle.ui-resizable-e").first();
                            var pageX = eastbar.offset().left;
                            var pageY = eastbar.offset().top;

                            (eastbar.trigger("mouseover")
                                .trigger({type: "mousedown", which: 1, pageX: pageX, pageY: pageY})
                                .trigger({type: "mousemove", which: 1, pageX: pageX - 1, pageY: pageY})
                                .trigger({type: "mousemove", which: 1, pageX: pageX, pageY: pageY})
                                .trigger({type: "mouseup", which: 1, pageX: pageX, pageY: pageY}));

                            slide.imgHtml = $img;

                            if (slide.timeLimit > 0) {
                                $('.timeout-manipulator').trigger('input');
                            }

                            $.event.trigger({
                                type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
                                slide: slide
                            });
                        }
                    });
                } else {
                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('#modal-error-content').html('Only video (MP4, WebM, OGV, OGG), up to 20MB');
                    UDP.SlideManager.encoding = false;
                    $('#modal-error').modal();
                }
            }
        })
    },

    slideAddAudio: function () {
        var $fileManager = $('.slide-add-audio-file');
        $fileManager.trigger('click');
        $fileManager.off('change');
        $fileManager.change(function () {
            UDP.SlideManager.encoding = true;
            var slide = UDP.SlideManager.activeSlide;
            if ($fileManager[0].files.length > 0 || $fileManager.val() != '') {
                var Extension = $fileManager.val().substring($fileManager.val().lastIndexOf('.') + 1).toLowerCase();
                var size = $fileManager[0].files[0].size;

                if ((Extension == "mp3" || Extension == "ogg" ) && ((size / 1048576) <= 20)) {

                    var fData = new FormData();

                    fData.append('command', "add_audio");
                    fData.append('file', $fileManager[0].files[0]);

                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('.slide-full').html('<img class="slide-loading" src="/bundles/fsjsextra/font/spinner/loading.svg?r='+$.now()+'">');
                    UDP.Communicator.sendSlide(slide.id, fData, function (data) {
                        var slide = UDP.SlideManager.activeSlide;
                        slide.type = UDP.SlideType.AUDIO;
                        slide.audio = data.data.slideData;

                        slide.attr = data.data.extraFields;
                        if (slide.attr.encoding_id) {
                            UDP.SlideManager.checkInterval = setInterval(function () {
                                UDP.Communicator.send(
                                    Routing.generate('get_encoding_status', {item: slide.attr.encoding_id}),
                                    null,
                                    function (response) {
                                        if (response && response.status === 'encoded') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            slide.audio = response.path;
                                            onAudioReady();
                                        } else if (response && response.status === 'error') {
                                            clearInterval(UDP.SlideManager.checkInterval);
                                            alert('Media conversion error');
                                            onAudioReady();
                                        }
                                    }
                                )
                            }, 1000);
                        } else {
                            onAudioReady();
                        }
                        function onAudioReady() {
                            UDP.SlideManager.encoding = false;
                            var $slideAria = $('.slide-full');
                            var $img = $('<div class="slide-audio-type"><p>' + slide.attr.audioName + '</p><audio src="' + slide.audio + '" controls>' + '</audio></div>');

                            $slideAria.html($img);

                            slide.imgHtml = $img;

                            if (slide.timeLimit > 0) {
                                $('.timeout-manipulator').trigger('input');
                            }
                            $.event.trigger({
                                type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
                                slide: slide
                            });
                        }
                    });
                } else {
                    $fileManager.val('');
                    $fileManager[0].files = null;
                    $('#modal-error-content').html('Only audio (MP3, OGG), up to 20MB');
                    UDP.SlideManager.encoding = false;
                    $('#modal-error').modal();
                }
            }
        })
    },

    slideAddQuestion: function () {
        var html = '<div class="slide-question-type"> <div class="question-area"><p><b>Question</b></p> <textarea id="question" class="form-control" rows="7" maxlength="512"></textarea></div> <div class="answers-area"> <div class="answers-input"> <div class="row"> <div class="col-sm-6"><p><b>Answers</b></p></div> <div id="answer-label" class="col-sm-6"><p><b style="float: right">Correct</b></p></div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q1" id="q1" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa1"> <label for="qa1"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q2" id="q2" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa2"> <label for="qa2"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q3" id="q3" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa3"> <label for="qa3"></label></div> </div> </div> <div class="row answer-col"> <div class="col-sm-11"><input type="text" class="form-control" name="q4" id="q4" maxlength="64"></div> <div class="col-sm-1"> <div class="checkbox"><input type="radio" name="qa" class="radio" id="qa4"> <label for="qa4"></label></div> </div> </div> </div> </div> <div class="bottom-area row"><label for="goto" class="col-sm-5 control-label"> If answered incorrect, user should be moved to slide number </label> <div class="col-sm-2"><input type="number" min="1" class="form-control" id="goto"></div> </div> <div> <div class="alert alert-danger question-error"> <div class="question-error-empty">Please, fill empty fields</div> <div class="question-error-answer">Please, select the correct answer(s)</div> <div class="question-error-goto">Please, select the valid slide number</div> <div class="question-error-duplicate">Duplicate answers</div> </div> </div> </div>';
        var $slideAria = $('.slide-full');
        var $tx = $(html);
        var slide = UDP.SlideManager.activeSlide;

        $slideAria.html($tx);
        slide.type = UDP.SlideType.QUESTION;
        slide.imgHtml = $tx;

        $.event.trigger({
            type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
            slide: slide
        });

    },

    checkShowNarration: function (slide) {
        if (UDP.SlideManager.activeSlide && slide.type === UDP.SlideType.IMAGE) {
            var $narration = $('.slide-narration');
            $narration.show();
            if (slide.attr && slide.attr.audio) {
                $narration.html("<audio controls src='" + slide.attr.audio + "'></audio>");
            } else {
                $narration.html($narration.data('oldHtml'));
            }
        } else {
            $('.slide-narration').hide();
        }
    },

    cancelEncoding: function () {
        UDP.SlideManager.encoding = false;
        if (UDP.SlideManager.activeSlide &&
            (UDP.SlideManager.activeSlide.type === UDP.SlideType.VIDEO || UDP.SlideManager.activeSlide === UDP.SlideType.AUDIO)) {
            UDP.SlideManager.activeSlide.type = UDP.SlideType.BLANK;
            $.event.trigger({
                type: UDP.SlideEvent.SLIDE_TYPE_CHANGED,
                slide: UDP.SlideManager.activeSlide
            });
        }
        clearInterval(UDP.SlideManager.checkInterval);
        UDP.UIManager.clearSlideArea();
    },

    init: function () {
        UDP.logger.log(this.name, "init");
        this.initEvent();

        var $narration = $('.slide-narration');
        $narration.data('oldHtml', $narration.html());
    },


    initEvent: function () {
        UDP.logger.log(this.name, "init event");
        $(document).on(UDP.SlideEvent.SLIDE_ADD, function (event) {
            UDP.SlideManager.addSlide(event.slide);
        }).on(UDP.SlideEvent.SLIDE_ACTIVE, function (event) {
            UDP.SlideManager.activeSlide = event.slide;
            UDP.logger.log(UDP.SlideManager.name, "active slide", event.slide);
            UDP.SlideManager.checkShowNarration(event.slide);
        }).on(UDP.SlideEvent.SLIDE_REMOVE, function (event) {
            UDP.logger.log(UDP.SlideManager.name, "slide remove", event.slide);
            UDP.SlideManager.removeSlide(event.slide);
        }).on(UDP.SlideEvent.SLIDE_TYPE_CHANGED, function (event) {
            UDP.SlideManager.checkShowNarration(event.slide);
        }).on(UDP.SlideEvent.SLIDE_SORT, function (event) {
            var id = event.id;
            var index = event.index + 1;

            var slide = UDP.SlideManager.slides[id];

            var oldIndex = slide.realNum;

            //UDP.SlideManager.questionSlideNewIndex(oldIndex, index);
            var $slides = $('.slides');
            slide.realNum = index;

            $slides.slick('slickRemove', index - 1);
            $slides.slick('slickAdd', slide.html, index - 1, true);

            if (oldIndex > index) {
                UDP.SlideManager.slideNumOffsetRecountPlus(slide, oldIndex);
            } else {
                UDP.SlideManager.slideNumOffsetRecountMinus(slide, oldIndex);
            }


            UDP.SlideManager.slideNumRecount();

        });

        $('.slide-time').on('input change propertychange', ".timeout-manipulator", function (event) {
            var $this = $(this);
            var type = $this.data('type');
            var $timeLimit = $('#slide-time-limit');
            var timeLimit = $timeLimit.val().split(':');
            var min = timeLimit[0];
            var sec = timeLimit[1];
            if (+min > 300) {
                min = 300;
            }
            if (+min == 300) {
                sec = 0;
            }
            if (+sec > 59) {
                sec = 59;
            }

            var slide = UDP.SlideManager.activeSlide;
            slide.timeLimit = (+min * 60) + (+sec);
            var str = "_%minute%_:_%seconds%_";

            if (slide.type == UDP.SlideType.VIDEO || slide.type == UDP.SlideType.AUDIO || (slide.type == UDP.SlideType.IMAGE && slide.attr.time)) {
                if (Math.floor(slide.attr.time) >= slide.timeLimit) {
                    var time = Math.floor(slide.attr.time);
                    var minutes = Math.floor(time / 60);
                    var seconds = Math.floor(time - minutes * 60);

                    slide.timeLimit = (+minutes * 60) + (+seconds);

                    min = minutes;
                    sec = seconds;
                }

            }

            if (min.toString().length < 2) {
                min = "0" + min;
            }
            if (sec.toString().length < 2) {
                sec = "0" + sec;
            }
            var timeText = str.replace('_%minute%_', min).replace("_%seconds%_", sec);

            slide.html.find('.slide-time-limit').html(timeText);
            $timeLimit.val(timeText);

            if (min == 0 && sec == 0) {
                slide.html.find('.slide-time-limit').hide()
            } else {
                slide.html.find('.slide-time-limit').show()
            }
        });

        $('.slide-full').on('input propertychange', '#goto', function () {
            //if(UDP.SlideValidator.validateSlides()){
            var slide = UDP.SlideManager.activeSlide;
            var findSlide = UDP.SlideManager.getSlideByNum(this.value);
            slide.attr = {
                id: findSlide.id
            };
            //}

        });
    },

    questionSlideNewIndex: function (id) {
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.type == UDP.SlideType.QUESTION) {
                    if (value.attr) {
                        var data = UDP.SlideManager.slides[value.attr.id];
                        if (data) {
                            value.imgHtml.find('#goto').val(data.realNum);
                        }

                    }

                }
            }
        }
    },

    questionSlideRemoveIndex: function (slide) {
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.type == UDP.SlideType.QUESTION) {
                    if (value.attr) {
                        if (value.attr.id == slide.id) {
                            value.imgHtml.find('#goto').val(0);
                            delete value.attr.id;
                        }

                    }

                }
            }
        }
    },

    slideNumRecount: function () {
        UDP.logger.log(this.name, "slides recount", UDP.SlideManager.slides);
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                value.html.find(".slide-number").html(value.realNum);
                value.html.attr('data-slick-index', value.realNum - 1);
                var str = '';
                if (value.realNum - 1 > 10) {
                    str = '0' + value.realNum - 1
                } else {
                    str = value.realNum - 1
                }
                value.html.attr('aria-describedby', "slick-slide" + str);
                UDP.SlideManager.questionSlideNewIndex(value.id);
            }

        }
    },

    getSlideByNum: function (num) {
        UDP.logger.log(this.name, "slides recount", UDP.SlideManager.slides);
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.realNum == num) {
                    return value;
                }
            }
        }
    },

    slideNumOffsetRecount: function (slide) {
        UDP.logger.log(this.name, "slides offset", UDP.SlideManager.slides);
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.realNum > slide.realNum) {
                    value.realNum -= 1;
                }
            }
        }
    },

    slideNumOffsetRecountMinus: function (slide, oldIndex) {
        UDP.logger.log(this.name, "slides offset", UDP.SlideManager.slides);
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.realNum <= slide.realNum && value != slide && value.realNum >= oldIndex) {
                    value.realNum -= 1;
                }
            }
        }
    },

    slideNumOffsetRecountPlus: function (slide, oldIndex) {
        UDP.logger.log(this.name, "slides offset plus", UDP.SlideManager.slides);
        for (var index in UDP.SlideManager.slides) {
            if (index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)) {
                var value = UDP.SlideManager.slides[index];
                if (value.realNum >= slide.realNum && value != slide && value.realNum <= oldIndex) {
                    value.realNum += 1;
                }
            }
        }
    }

};

UDP.SlideManager.init();