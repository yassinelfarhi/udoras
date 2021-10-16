/**
 * Created by Vladislav on 06.10.2016.
 */
//-------------------BEWARE STRANGER-------------------//
var UDP = UDP || {'behaviors': {}};
UDP.SlideEvent = {
    SLIDE_ADD: "slideAdd",
    SLIDE_REMOVE: "slideRemove",
    SLIDE_ACTIVE: "slideActive",
    SLIDE_TYPE_CHANGED: "slideTypeChanged",
    SLIDE_SORT: "slideSort"
};

UDP.SlideType = {
    BLANK: "blank",
    TEXT: "text",
    IMAGE: "image",
    VIDEO: "video",
    AUDIO: "audio",
    QUESTION: "question"
};

(function ($) {
    "use strict";
    $(".btn").mouseup(function(){
        $(this).blur();
    });
    UDP.settings = {};
    var $slides = $('.slides');
    $slides.slick({
        dots: false,
        prevArrow: $('.next-left'),
        nextArrow: $('.next-right'),
        infinite: false,
        //arrows: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 1,
        variableWidth: false,
        swipe: false,
        touchMove: false,
        responsive: [
            {
                breakpoint: 1830,
                settings: {
                    variableWidth: false,
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    infinite: false
                }
            },
            {
                breakpoint: 1730,
                settings: {
                    variableWidth: false,
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    infinite: false
                }
            },
            {
                breakpoint: 1280,
                settings: {
                    variableWidth: false,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: false
                }
            },

            {
                breakpoint: 1024,
                settings: {
                    variableWidth: false,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: false
                }
            },
            {
                breakpoint: 600,
                settings: {
                    variableWidth: false,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    infinite: false
                }
            },
            {
                breakpoint: 480,
                settings: {
                    variableWidth: false,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    infinite: false
                }
            }
        ]
    });


    function sortableInit() {
        $('.slick-track').sortable({
            placeholder: "slide-state-highlight",
            items: ".slide-warp:not(.add-slide-handle)",
            /* start: function (e, ui) {
             var oldIndex = ui.item.index();
             console.log(oldIndex);
             },*/
            update: function (e, ui) {
                // gets the new and old index then removes the temporary attribute
                var newIndex = ui.item.index();
                var id = $(ui.item).find('.slide').data('id');
                console.log(id);

                

                $.event.trigger({
                    type: UDP.SlideEvent.SLIDE_SORT,
                    id: id,
                    index: newIndex
                });
            }
        });
    }

    sortableInit();

    $slides.on('breakpoint', function () {
        sortableInit();
    });
}(jQuery));