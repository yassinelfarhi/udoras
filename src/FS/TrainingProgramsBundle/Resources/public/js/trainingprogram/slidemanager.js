/**
 * Created by Vladislav on 25.10.2016.
 */
var UDT = UDT || {};

"use strict";

UDT.SlideManager = {
    name: "slidemanager",
    timeStart: 0,
    timeEnd: 0,
    timeSec: 0,
    nextSlide: 0,
    trainingState: 0,
    curSlide: 0,
    slideType: "",
    interval: null,
    limitEnd: false,

    init: function (trainingState, curSlide, nextSlide) {
        UDT.logger.log(UDT.SlideManager.name, "init");

        this.trainingState = trainingState;
        this.curSlide = curSlide;
        this.nextSlide = nextSlide;
        this.getCurrentSlide();
    },

    getNextSlide: function () {
        if(UDT.UIManager.buttonsLocked){
            return false;
        }

        UDT.logger.log(UDT.SlideManager.name, "getNextSlide");
        if (UDT.SlideManager.slideType == UDT.SlideType.QUESTION) {
            var answers = UDT.SlideManager.getQuestionResults();
            if(!UDT.SlideManager.validateQuestion(answers)){
                UDT.UIManager.showErrorQuestion();
                return false;
            }
            UDT.Communicator.send({command: "question", answers: answers}, UDT.SlideManager.slideResponseHandler);
        } else if (UDT.SlideManager.slideType == UDT.SlideType.FINAL_QUESTION) {
            var answers = UDT.SlideManager.getFinalQuestionResults();
            if(!UDT.SlideManager.validateQuestions(answers)){
                UDT.UIManager.showErrorQuestion();
                return false;
            }
            UDT.Communicator.send({
                command: "final_question",
                answers: answers,
                offset: UD.settings.offset
            }, UDT.SlideManager.slideResponseHandler);
        } else {
            UDT.Communicator.send({command: "next_slide",offset: UD.settings.offset}, UDT.SlideManager.slideResponseHandler);
        }
        
        UDT.SlideManager.timerStop();
    },
    getPrevSlide: function () {
        if(UDT.UIManager.buttonsLocked){
            return false;
        }

        UDT.logger.log(UDT.SlideManager.name, "getPrevSlide");
        if (UDT.SlideManager.slideType == UDT.SlideType.FINAL_QUESTION) {
            return false;
        } else {
            UDT.Communicator.send({command: "prev_slide", offset: UD.settings.offset}, UDT.SlideManager.slideResponseHandler);
        }

        UDT.SlideManager.timerStop();
    },

    getCurrentSlide: function () {
        if(UDT.UIManager.buttonsLocked){
            return false;
        }
        UDT.logger.log(UDT.SlideManager.name, "getCurrentSlide");
        UDT.Communicator.send({command: "current_slide", offset: UD.settings.offset}, UDT.SlideManager.slideResponseHandler);
    },
    getResumeSlide: function () {
        if(UDT.UIManager.buttonsLocked){
            return false;
        }
        UDT.logger.log(UDT.SlideManager.name, "getResumeSlide");
        UDT.Communicator.send({command: "continue"}, UDT.SlideManager.slideResponseHandler);
    },
    pauseProgramSlide: function () {
        if(UDT.UIManager.buttonsLocked){
            return false;
        }
        UDT.logger.log(UDT.SlideManager.name, "pauseProgramSlide");
        UDT.Communicator.send({command: "pause"}, function (data) {
            console.log('nope')
        });
    },


    validateQuestion:function (answers) {
        console.log(answers);
        for(var i = 0; i < 4; ++i){
            if(answers[i].checked){
                return true;
            }
        }
        return false;
    },

    validateQuestions: function (answersArray) {
        console.log(answersArray);
        for(var index in answersArray) {
            if(answersArray.hasOwnProperty(index)){
                var value = answersArray[index];
                if(!UDT.SlideManager.validateQuestion(value)){
                    return false;
                }
            }
        }
        return true;
    },

    getQuestionResults: function () {
        var $mainSlide = $('.main-slide');
        var answers = [];
        for (var i = 1; i < 5; ++i) {
            answers.push({
                text: $mainSlide.find('#q_1_a_' + i).val(),
                checked: $mainSlide.find('#q_1_a_' + i).is(':checked')
            });
        }
        return answers;

    },

    getFinalQuestionResults: function () {
        var $mainSlide = $('.main-slide');
        var $questions = $mainSlide.find('.question');
        var answers = {};
        $questions.each(function (index, item, arr) {
            var id = $(item).data('id');
            if (id !== undefined) {
                answers[id] = [];
                for (var i = 1; i < 5; ++i) {
                    answers[id].push({
                        text: $mainSlide.find('#q_' + id + '_a_' + i).val(),
                        checked: $mainSlide.find('#q_' + id + '_a_' + i).is(':checked')
                    });
                }
            }

        });

        return answers;

    },

    slideResponseHandler: function (data) {
        if (data.op == UDT.SlideOp.NEXT) {
            UDT.SlideManager.limitEnd = false;
            UDT.UIManager.hideErrorQuestion();
            UDT.SlideManager.timeSec = data.timeRemaining;
            UDT.SlideManager.slideType = data.slide.slideType;
            UDT.SlideManager.nextSlide = data.nextSlide;
            if (data.final == true) {
                UDT.UIManager.finalSlide();
            } else {
                UDT.UIManager.noFinalSlide();
            }
            if(data.hidePrev) {
                UDT.UIManager.hidePrevButton();
            } else {
                UDT.UIManager.showPrevButton();
            }
            UDT.UIManager.setSlide(data.html);
            UDT.UIManager.progressBarRecount(data.slide.realNum);
            if(UDT.SlideManager.timeSec > 0){
                UDT.SlideManager.timerStart();
                UDT.UIManager.hideNextButton();
            } else if(UDT.SlideManager.timeSec === 0) {
                UDT.UIManager.hideTimer();
                UDT.UIManager.showNextButton();
            } else {
                UDT.SlideManager.limitEnd = true;
                UDT.UIManager.showNextButton();
                UDT.SlideManager.timerStop();
                UDT.UIManager.hideTimer();
            }
        } else if (data.op == UDT.SlideOp.REDIRECT) {
            window.location.href = data.target;
        }

    },

    timerStart: function () {
        UDT.SlideManager.interval = setInterval("UDT.SlideManager.timerTick()", 1000);
    },

    timerStop: function () {
        if(UDT.SlideManager.interval)
            clearInterval(UDT.SlideManager.interval);
        UDT.SlideManager.interval = null;
    },

    timerTick: function () {
        var time = Math.floor(UDT.SlideManager.timeSec);
        var minutes = Math.floor(time / 60);
        var seconds = Math.floor(time - minutes * 60);

        if (+minutes < 10) {
            minutes = "0" + minutes;
        }
        if (+seconds < 10) {
            seconds = "0" + seconds;
        }

        var str = "_%minute%_:_%seconds%_";

        str = str.replace('_%minute%_', minutes).replace("_%seconds%_", seconds);

        UDT.UIManager.setTimer(str);

        --UDT.SlideManager.timeSec;
        if (UDT.SlideManager.timeSec <= -1) {
            UDT.SlideManager.limitEnd = true;
            UDT.UIManager.showNextButton();
            UDT.SlideManager.timerStop();
            UDT.UIManager.hideTimer();
        }
    },
    
    pauseProgram: function () {
        UDT.SlideManager.timerStop();
        UDT.UIManager.hideTimer();
        UDT.SlideManager.pauseProgramSlide();
        UDT.UIManager.modalPauseTraining();
    },
    
    resumeProgram: function () {
        UDT.UIManager.modalHide();
        UDT.SlideManager.getResumeSlide();
    }
};