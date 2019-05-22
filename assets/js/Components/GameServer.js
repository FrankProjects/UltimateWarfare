'use strict';

class GameServer {
    sendMessage(url, message, callback) {
        let data = 'json_request=' + JSON.stringify(message);
        $.post(url, data, function(msg) {
            GameServer.onMessage(msg, callback)
        }, 'json').fail(function(message) {
            GameServer.onError(message, callback)
        });
        console.log("message sent: ", data);

        return true
    };

    static onMessage(message, callback) {
        let data = message.data || message;

        if(typeof callback == "function") {
            callback(data)
        }
    };

    static onError(message, callback) {
        if(typeof callback == "function") {
            let err = new Error("Server request failed");
            callback(err)
        }
    };
}

export default GameServer;
