'use strict';

import $ from 'jquery';
import Client from './Components/Webclient/Client';

console.log('Start');

$(document).ready(function(){
    console.log('Ready');

    let client = new Client();
    client.init();

    $(window).resize(function() {
        console.log('resize');
        let bodyHeight = $(this).height();
        $("#screen").height(bodyHeight);
        $("#game").height(bodyHeight);

    }).resize();
});
