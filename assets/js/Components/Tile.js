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

    /*
     *	Projection - node\element with tile.
     *	Each projection is saved in projections array, so they all can be accessed
     *	at once if needed.
     */
    createProjection() {
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

        let structBlock = document.createElement('div');

        structBlock.style.bottom = this.undergroundHeight+'px';
        structBlock.style.width = this.landWidth+'px';
        structBlock.style.height = this.landHeight+'px';
        structBlock.style.position = 'absolute';
        structBlock.style.left = '0px';
        structBlock.style.paddingTop = this.airHeight+'px';
        structBlock.style.backgroundPosition = 'bottom left';
        structBlock.style.backgroundRepeat = 'no-repeat';

        if(this.tileData['structures'].length){
            structBlock.style.backgroundImage = 'url('+this.tileData['structures'][0]['name']+'/'+this.tileData['structures'][0]['imageName']+')';
        }

        tileBlock.appendChild(structBlock);

        if (this.tileData['owner'] !== undefined && this.tileData['owner'] !== '') {

            let gameUnitBlock = document.createElement('div');

            gameUnitBlock.style.position = 'absolute';
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

        let structBlock = tileBlock.childNodes[0];
        if(this.tileData['structures'].length){
            structBlock.style.backgroundImage = 'url('+this.tileData['structures'][0]['name']+'/'+this.tileData['structures'][0]['imageName']+')';
        }
    };

    /*
     *	deletes projection from array
     */
    deleteProjection(index) {
        this.projections.splice(index, 1);

        return(this);
    }
}

export default Tile;