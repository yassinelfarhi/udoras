/**
 * Created by Vladislav on 06.10.2016.
 */
var UDP = UDP || {};

"use strict";
UDP.Communicator = {
    path: null,
    slidePath:null,
    presentationId: null,
    name: "communicator",

    init: function (id) {
        this.path = UDP.settings.communicatorPath;
        this.slidePath = UDP.settings.slidePath;
        this.presentationId = id;
        UDP.logger.log(UDP.Communicator.name, "init")
    },


    sendPresentation : function (params, callback) {
        UDP.logger.log(UDP.Communicator.name, "send presentation cmd");
        this.send(this.path, params, callback);
    },

    
    sendSlide: function (id, params, callback) {
        UDP.logger.log(UDP.Communicator.name, "send slide cmd");
        var path = this.slidePath.replace('_slide_',id);
        if(params instanceof FormData){
            this.sendFile(path, params, callback)
        } else {
            this.send(path, params, callback)
        }

    },


    send: function (path, params, callback) {
        $.ajax({
            type:"POST",
            url: path,
            data: params,
            success: callback,
            error: function (data) {
                console.log(data);
            }
        });
    },


    sendFile: function (path, params, callback) {
        $.ajax({
            type:"POST",
            url: path,
            enctype: 'multipart/form-data',
            data: params,
            success: callback,
            contentType: false,
            processData: false,
            error: function (data) {
                console.log(data);
            }
        });
    }
};
