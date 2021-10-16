/**
 * Created by Vladislav on 06.10.2016.
 */
var UDT = UDT || {};

"use strict";
UDT.Communicator = {
    name: "communicator",
    trainingState: null,
    mainPage: "",
    route: "",

    init: function (id) {
        this.trainingState = id;
        this.route = Routing.generate('training_state_manage_slide', {trainingState: id});
        this.mainPage = Routing.generate('show_training_program', {link: UDT.Settings.link});
        UDT.logger.log(UDT.Communicator.name, "init")
    },


    send: function (params, callback) {
        UDT.UIManager.buttonsLocked = true;
        $.ajax({
            type: "POST",
            url: this.route,
            data: params,
            success: function (data) {
                UDT.UIManager.buttonsLocked = false;
                callback(data);
            },
            error: function (data) {
                UDT.UIManager.buttonsLocked = false;
                console.log(data);
            }
        });
    }
};
