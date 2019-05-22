'use strict';

import GameServer from './GameServer';

class Action {
    constructor() {
        this.gameServer = new GameServer;
    }

    getTiles(coords, onResponse) {
        let params = {"coords": coords};

        this.gameServer.sendMessage('../game/world/get-tiles', params, onResponse);
    };

    buyTile(x, y, onResponse) {
        let params = {'x': x, 'y': y};

        this.gameServer.sendMessage('../game/world/buy-tile', params, onResponse);
    };

    getBuildingList(x, y, onResponse) {
        let params = {'x': x, 'y': y};

        this.gameServer.sendMessage('../game/world/get-building-list', params, onResponse);
    };

    build(x, y, cid, onResponse) {
        let params = {'x': x, 'y': y, 'cid': cid};

        this.gameServer.sendMessage('../game/world/build', params, onResponse);
    };
}

export default Action;