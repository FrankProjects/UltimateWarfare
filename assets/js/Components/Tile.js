'use strict';

class Tile {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.landWidth = window.map.tileWidth;
        this.landHeight = window.map.tileHeight;
        this.undergroundHeight = window.map.undergroundHeight;
        this.airHeight = window.map.airHeight;

        this.tileData = {"type":"none","structures":[]}; //array of tile data
        //this.loaded = 0;
        this.projections = []; //array of tile nodes of current object. - Projection registry
    }

    setTileData(data) {
        this.tileData = data;
    };

    createTileBlock() {
        let tileBlock = document.createElement('div');
        tileBlock.className = 'tile';
        tileBlock.setAttribute('x', this.x);
        tileBlock.setAttribute('y', this.y);
        tileBlock.style.width = this.landWidth+'px';
        tileBlock.style.height = this.landHeight+'px';
        tileBlock.style.position = 'absolute';
        tileBlock.style.paddingTop = this.airHeight+'px';
        tileBlock.style.paddingBottom = this.undergroundHeight+'px';
        tileBlock.style.backgroundImage = 'url(../images/map/'+this.tileData['type']+'.png)';
        tileBlock.style.backgroundRepeat = 'no-repeat';
        tileBlock.style.backgroundPosition = '0px '+this.airHeight+'px';

        return tileBlock;
    }

    createStructureBlock() {
        let structureBlock = document.createElement('div');
        structureBlock.style.bottom = this.undergroundHeight+'px';
        structureBlock.style.width = this.landWidth+'px';
        structureBlock.style.height = this.landHeight+'px';
        structureBlock.style.position = 'absolute';
        structureBlock.style.left = '0px';
        structureBlock.style.paddingTop = this.airHeight+'px';
        structureBlock.style.backgroundPosition = 'bottom left';
        structureBlock.style.backgroundRepeat = 'no-repeat';

        return structureBlock;
    }

    createProjection() {
        let tileBlock = this.createTileBlock();
        let structureBlock = this.createStructureBlock();

        if(this.tileData['structures'].length){
            structureBlock.style.backgroundImage = 'url('+this.tileData['structures'][0]['name']+'/'+this.tileData['structures'][0]['imageName']+')';
        }

        tileBlock.appendChild(structureBlock);

        if (this.tileData['owner'] !== undefined && this.tileData['owner'] !== '') {
            let gameUnitBlock = document.createElement('div');
            gameUnitBlock.style.bottom = this.undergroundHeight+'px';
            gameUnitBlock.style.width = this.landWidth+'px';
            gameUnitBlock.style.height = this.landHeight+'px';
            gameUnitBlock.style.position = 'absolute';
            gameUnitBlock.style.textAlign = 'center';
            gameUnitBlock.style.padding = '25px 0';
            gameUnitBlock.style.color= 'red';
            gameUnitBlock.style.fontWeight = 'bold';

            gameUnitBlock.innerText = this.tileData['owner'];
            tileBlock.appendChild(gameUnitBlock);
        }

        this.projections.push(tileBlock);
        let index = this.projections.length-1;

        return(index);
    };

    refreshProjections() {
        for(let index in this.projections){
            this.refreshProjection(index);
        }
    };

    refreshProjection(oldIndex) {
        let tileBlock = this.projections[oldIndex];
        tileBlock.style.backgroundImage = 'url(../images/map/'+this.tileData['type']+'.png)';

        let structureBlock = tileBlock.childNodes[0];
        if(this.tileData['structures'].length){
            structureBlock.style.backgroundImage = 'url('+this.tileData['structures'][0]['name']+'/'+this.tileData['structures'][0]['imageName']+')';
        }
    };

    deleteProjection(index) {
        this.projections.splice(index, 1);
    }
}

export default Tile;