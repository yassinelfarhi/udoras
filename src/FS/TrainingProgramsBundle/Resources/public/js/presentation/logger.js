/**
 * Created by Vladislav on 06.10.2016.
 */
var UDP = UDP || {};

"use strict";
UDP.logger = {
    log: function (who, msg, extra) {
        console.log("%s message %s", who, msg);
        if (extra) {
            console.log(extra);
        }
    }
};