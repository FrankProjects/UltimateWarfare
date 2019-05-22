'use strict';

import Tile from "./Tile";

class Map {
    constructor() {
        this.tileWidth = 194;
        this.tileHeight = 98;
        this.airHeight = 158;

        this.tileList = [];

        this.width = 0;
        this.height = 0;

        this.x = 0;
        this.y = 0;

        this.rootNode = null;
        this.parentNode = null;
    }

    init(parentNode) {
        this.parentNode = parentNode;

        this.width = Math.ceil( (this.parentNode.offsetWidth + 2) / (this.tileWidth + 2) ); //2 is space of 2px between each  horizontaly relative tile
        this.height = Math.ceil( this.parentNode.offsetHeight / this.tileHeight ) + 1; //1 is to fill empty "half-diamond" places

        if(! (this.width % 2)){
            this.width++
        }

        if(! (this.height % 2)){
            this.height++
        }

        let x = -(this.width + this.height-2)/2; //coordinates of very first (top left) tile
        let y = (this.height - this.width)/2; //coordinates of very first (top left) tile

        for(let i = 0; i < this.height + this.height - 1; i++){
            let top = i * (this.tileHeight / 2) - this.airHeight - this.tileHeight/2;

            for(let j = 0; j < ( this.width + (i % 2) ); j++){
                let left = j * (this.tileWidth + 2) - (this.tileWidth + 2) / 2 * (i % 2);

                let tileData = [];
                tileData['offsetX'] = x+j;
                tileData['offsetY'] = y+j;
                tileData['top'] = top;
                tileData['left'] = left;
                tileData['tileInstance'] = null;
                tileData['projectionIndex'] = null;

                this.tileList.push(tileData);
            }

            if( i % 2 ){ //if true than next row is "short", and x should decrease
                x++;
            }else{
                y--;
            }
        }

        let rootNode = document.createElement('div');
        rootNode.id = 'map';
        rootNode.style.width = this.width * (this.tileWidth + 2) - 2+'px'; //2 is "horizontal gap" between tiles
        rootNode.style.height = (this.height - 1) * this.tileHeight+'px';
        rootNode.style.position = 'relative';
        rootNode.style.overflow = 'hidden';
        rootNode.style.backgroundColor = '#41390f';

        this.rootNode = rootNode;
        this.parentNode.appendChild(rootNode);

        return(this);
    };

    move(x, y) {
        this.x = x;
        this.y = y;
        this.fill(false); //false to not to load data, data will be loaded separatelly on mousekeyup
        this.draw();
    };

    /*
     *	Function fill() fill empty cells with data - tiles of given coordinates.
     *	Param "load" - to fetch data from server or not to.
     *	If false, the tile is empty(grey), and without cachind. It's used only on map scroll, when you
     *	don't need to load tiles on every mouse movement, but only on mousekeyup.
     */
    fill(load) {
        if(load === undefined){
            load = true;
        }

        this.unfill();

        if(load){
            window.tileCollection.getTiles(this.tileList);
        }

        for(let key in this.tileList){
            let tileData = this.tileList[key];

            let x = tileData['offsetX'] + this.x;
            let y = tileData['offsetY'] + this.y;
            let tileInstance;

            if(window.tileCollection.hasTile(x, y) || load){
                tileInstance = window.tileCollection.getTile(x, y);
            }else{
                tileInstance = new Tile(x, y);
            }

            tileData['tileInstance'] = tileInstance;
            tileData['projectionIndex'] = tileInstance.createProjection();
        }

        return(this);
    };

    unfill() {
        for(let key in this.tileList){
            let tileData = this.tileList[key];

            if(tileData['tileInstance']){
                let tile = tileData['tileInstance'];
                let index = tileData['projectionIndex'];

                tile.deleteProjection(index);

                tileData['tileInstance'] = null;
            }
        }

        return(this);
    };

    draw() {
        if(this.rootNode.childNodes.length){
            this.flushScreen();
        }

        for(let key in this.tileList){
            let tileData = this.tileList[key];

            let tile = tileData['tileInstance'];
            let pIndex = tileData['projectionIndex'];

            let tileNode = tile.projections[pIndex];
            tileNode.style.top = tileData['top']+'px';
            tileNode.style.left = tileData['left']+'px';

            this.rootNode.appendChild(tileNode);
        }
    };

    flushScreen() {
        while(this.rootNode.firstChild){
            let child = this.rootNode.firstChild;
            this.rootNode.removeChild(child);
        }

        return(this);
    };
}

export default Map;
