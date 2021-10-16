/**
 * Created by Vladislav on 18.10.2016.
 */
var UDP = UDP || {};
"use strict";

UDP.SlideNormalize = {
    name: "SlideNormalize",

    normalize: function (slide) {
        if (UDP.SlideManager.encoding) {
            return;
        }
        var res = {};
        res.type = slide.type;
        res.id = slide.id;
        res.timeLimit = slide.timeLimit;
        res.realNum = slide.realNum;
        var $slideAria = $('.slide-full');
        switch (slide.type) {
            case UDP.SlideType.VIDEO:
                res.slideData = slide.video;
                var img = slide.imgHtml;
                if (img.parent().length != 0) {
                    /** NOP */
                } else {
                    img = img.find('video');
                }
                if (img.css('width').indexOf('%') === -1) {
                    var top = parseInt(img.css('top')) / $slideAria.height() * 100;
                    var width = parseInt(img.css('width')) / $slideAria.width() * 100;
                    var height = parseInt(img.css('height')) / $slideAria.height() * 100;

                    if (top + height > 100 && top > 0) {
                        top = 100 - height;
                    }
                    res.extraFields = {
                        top: top,
                        width: width,
                        height: height
                    };
                } else {
                    res.extraFields = {
                        top: parseInt(img.parent().css('top')),
                        width: parseInt(img.parent().css('width')),
                        height: parseInt(img.parent().css('height'))
                    };
                }
                if (!res.extraFields.top || isNaN(res.extraFields.top)) {
                    res.extraFields.top = slide.attr.top;
                }
                if (!res.extraFields.width || isNaN(res.extraFields.width)) {
                    res.extraFields.width = slide.attr.width;
                }
                if (!res.extraFields.height || isNaN(res.extraFields.height)) {
                    res.extraFields.height = slide.attr.height;
                }
                break;
            case UDP.SlideType.IMAGE:
                res.slideData = slide.img;

                var img = slide.imgHtml;
                if (img.parent().length != 0) {
                    img = img.parent();
                }
                if (img.css('width').indexOf('%') === -1) {
                    var top = parseInt(img.css('top')) / $slideAria.height() * 100;
                    var width = parseInt(img.css('width')) / $slideAria.width() * 100;
                    var height = parseInt(img.css('height')) / $slideAria.height() * 100;

                    if (top + height > 100 && top > 0) {
                        top = 100 - height;
                    }
                    res.extraFields = {
                        top: top,
                        width: width,
                        height: height
                    };
                } else {
                    res.extraFields = {
                        top: parseInt(img.parent().css('top')),
                        width: parseInt(img.parent().css('width')),
                        height: parseInt(img.parent().css('height'))
                    };
                }
                res.extraFields.audio = slide.attr.audio;
                if (!res.extraFields.top || isNaN(res.extraFields.top)) {
                    res.extraFields.top = slide.attr.top;
                }
                if (!res.extraFields.width || isNaN(res.extraFields.width)) {
                    res.extraFields.width = slide.attr.width;
                }
                if (!res.extraFields.height || isNaN(res.extraFields.height)) {
                    res.extraFields.height = slide.attr.height;
                }

                break;
            case UDP.SlideType.TEXT:
                res.slideData = slide.imgHtml.val();
                break;
            case UDP.SlideType.AUDIO:
                res.slideData = slide.audio;
                res.extraFields = {
                    audioName: slide.attr.audioName,
                    time: slide.attr.time
                };
                break;
            case UDP.SlideType.QUESTION:
                var data = slide.imgHtml;
                res.extraFields = {
                    question: data.find('#question').val(),
                    answers: [],
                    goToSlide: data.find('#goto').val(),
                    id: slide.attr.id
                };
                for (var i = 1; i < 5; ++i) {
                    res.extraFields.answers.push({
                        answer: data.find('#q' + i).val(),
                        correct: data.find('#qa' + i).is(':checked')
                    });
                }
                break;
            case UDP.SlideType.BLANK:
            default:
                break
        }

        return res;
    }
};
