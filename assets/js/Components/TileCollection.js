'use strict';

import Tile from "./Tile";

class TileCollection {
    constructor() {
        this.tiles = [];
    }

    hasTile(x, y) {
        if(this.tiles[x] === undefined){
            return(false);
        }else if(this.tiles[x][y] === undefined){
            return(false);
        }else{
            return(true);
        }
    };

    setTile(x, y, tileInstance) {
        if(this.tiles[x] === undefined){
            this.tiles[x] = [];
        }

        return(this.tiles[x][y] = tileInstance);
    };

    getTile(x, y) {
        let tileInstance;

        if(this.hasTile(x, y)){
            tileInstance = this.tiles[x][y];
        }else{
            tileInstance = this.setTile(x, y, new Tile(x, y));

            let tilesCoordPairs = [];
            tilesCoordPairs.push({'x': x, 'y': y});

            window.action.getTiles(tilesCoordPairs, function(data){
                for(let key in data){
                    if (data.hasOwnProperty(key)) {
                        let tile = data[key];
                        let instance = window.tileCollection.getTile(tile['x'], tile['y']);
                        //instance.loaded = 1;
                        instance.setTileData(data[key]);
                        instance.refreshProjections();
                    }
                }
            });
        }

        return(tileInstance);
    };

    getTiles(tilesList) {
        let tilesCoordPairs = [];
        let instance;
        let instances = [];

        for(let key in tilesList){
            if (tilesList.hasOwnProperty(key)) {
                let tileData = tilesList[key];
                let x = tileData['offsetX'] + window.map.x;
                let y = tileData['offsetY'] + window.map.y;

                if(!this.hasTile(x, y)){
                    tilesCoordPairs.push({'x': x, 'y': y});
                    instance = this.setTile(x, y, new Tile(x, y));
                }else{
                    instance = this.getTile(x, y);
                }

                instances.push(instance);
            }
        }

        //get them from server and fill in instances
        if(tilesCoordPairs.length > 0){
            window.action.getTiles(tilesCoordPairs, function(data){
                for(let key in data){
                    if (data.hasOwnProperty(key)) {
                        let tile = data[key];
                        let instance = window.tileCollection.getTile(tile['x'], tile['y']);
                        //instance.loaded = 1;
                        instance.setTileData(data[key]);
                        instance.refreshProjections();
                    }
                }
            });
        }

        return(instances);
    };
}

export default TileCollection;
