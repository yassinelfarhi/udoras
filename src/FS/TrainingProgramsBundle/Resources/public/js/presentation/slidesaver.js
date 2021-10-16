/**
 * Created by Vladislav on 17.10.2016.
 */

var UDP = UDP || {};
"use strict";

UDP.SlideSaver = {
    name: "SlideSaver",

    normalizeSlidesData: function () {
        //here i get slides for json format
        var slides = UDP.SlideManager.slides;
        var res = [];
        for(var index in UDP.SlideManager.slides) {
            if(index != 'length' && UDP.SlideManager.slides.hasOwnProperty(index)){
                var value = UDP.SlideManager.slides[index];
                res.push(UDP.SlideNormalize.normalize(value))
            }
        }
        return res;
    },

    saveClickHandler: function (event, ref) {
        event.preventDefault();
        if(UDP.SlideManager.encoding) {
            UDP.UIManager.showCancelDialog(function(){
                UDP.SlideManager.cancelEncoding();
                $(event.target).trigger(event);
            });
            return;
        }
        UDP.logger.log(UDP.SlideSaver.name, "save click handler");
        var valid = UDP.SlideValidator.validateSlides();
        var spinner = '<img class="spinner-white" src="/bundles/fsjsextra/font/spinner/loading_white.svg" >';
        var $save = $('.save');

        if(valid) {
            if(!ref){
                $save.html("Save"+spinner);
            }
            var data = UDP.SlideSaver.normalizeSlidesData();
            UDP.Communicator.sendPresentation({command: "save_slides", slides: data}, function (data) {
                $('.save').html("Save");
                console.log(data);
                if (ref) {
                    window.location.href = ref;
                }
            });
        } else {
            var slide = UDP.SlideValidator.getFirstInvalid();
            var $slides = $('.slides');
            $slides.slick('slickGoTo', slide.realNum - 1, false);
            slide.html.find('.slide').trigger('click');
        }
    },

    init: function () {
        UDP.logger.log(UDP.SlideSaver.name, "init");
        this.initEvents();
    },

    initEvents: function () {
        $('.control')
            .on('click', '.save', this.saveClickHandler)
            .on('click', '.finish', function () {
                var ref = $(this).data('redirect');
                $('.save').trigger('click', [ref]);
            });
    }
};

UDP.SlideSaver.init();