'use strict';

import Action from '../Action';
import Map from '../Map';
import MapPad from "../MapPad";
import TileMenu from "../TileMenu";
import TileCollection from "../TileCollection";

class Client {
    constructor() {
        window.map = new Map();
        window.mapPad = new MapPad();
        window.tileMenu = new TileMenu();
        window.tileCollection = new TileCollection();
        window.action = new Action();
    }

    init() {
        console.log('New client init!');

        window.map.init(document.getElementById('screen')).fill().draw();
        window.mapPad.init();

        return(this);
    };
}

export default Client;
