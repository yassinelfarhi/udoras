/**
 * Created by Vladislav on 28.09.2016.
 */
var UD = UD || {};

(function ($) {
    "use strict";
    UD.WebSocketClient = {
        states: {
            STATE_DISCONNECTED: 'disconnected',
            STATE_INIT: 'init',
            STATE_CONNECTED: 'connected'
        },
        socket: null,
        init: function () {
            this.state = UD.WebSocketClient.states.STATE_INIT;
            this.initEvents();
            this.connect(UD.settings.node.host);
        },
        initEvents: function () {
        },
        connect: function (params) {
            this.socket = io.connect(params);
            this.state = UD.WebSocketClient.states.STATE_CONNECTED;
        },
        initUser: function (userId, ssid, communicator, deleteFlush) {
            if (this.state == UD.WebSocketClient.states.STATE_CONNECTED) {
                this.socket.emit('init', userId, ssid, communicator,deleteFlush);
            }
        },
        release: function () {
            if (this.state == UD.WebSocketClient.states.STATE_CONNECTED) {
                console.log('deprecated');
                this.socket.emit('release');
            }
        }
    };

    UD.WebSocketClient.state = UD.WebSocketClient.states.STATE_DISCONNECTED;

}(jQuery));