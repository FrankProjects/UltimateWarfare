'use strict';

import $ from 'jquery';

class MapPad {
    constructor() {
        this.mouseIsoVector = [0, 0]; //isometric cordinates of mouse
        this.mouseOverTile = [0, 0]; //local map coordinates of tile, on which mouse is pointing
        this.tempVector = [0, 0]; //vector for temporary values, to share them between events

        this.isClick = true; //defines if event is click or map scroll

        this.rad = (Math.PI/180)*(45); //-45 degree angle in radians
    }

    init() {
        //draw overlay
        let mapPadOverlay = document.createElement('div');
        mapPadOverlay.id = 'mapPad';
        mapPadOverlay.style.width = window.map.width*(window.map.tileWidth + 2) - 2+'px'; //2 is "horizontal gap" between tiles
        mapPadOverlay.style.height = (window.map.height - 1) * window.map.tileHeight+'px';
        mapPadOverlay.style.position = 'absolute';
        mapPadOverlay.style.top = '0px';
        mapPadOverlay.style.left = '0px';
        mapPadOverlay.style.zIndex = '2';

        window.map.parentNode.appendChild(mapPadOverlay);

        //default mouse coordinates transformations in the isometric coordinates
        let mapPadSelector = $('#mapPad');

        mapPadSelector.mousemove(function(e){
            let pageX = e.pageX - $(this).offset().left - ((window.map.tileWidth + 2) * Math.ceil(window.map.width / 2));
            let pageY = e.pageY - $(this).offset().top - (window.map.tileHeight * Math.floor(window.map.height / 2));

            window.mapPad.mouseIsoVector[0] = [pageX * Math.sin(window.mapPad.rad) / 2 + pageY*Math.cos(window.mapPad.rad)];
            window.mapPad.mouseIsoVector[0] = [pageX * Math.sin(window.mapPad.rad) / 2 + pageY*Math.cos(window.mapPad.rad)];
            window.mapPad.mouseIsoVector[0] = [pageX * Math.sin(window.mapPad.rad) / 2 + pageY*Math.cos(window.mapPad.rad)];
            window.mapPad.mouseIsoVector[0] = [pageX * Math.sin(window.mapPad.rad) / 2 + pageY*Math.cos(window.mapPad.rad)];
            window.mapPad.mouseIsoVector[1] = [pageX * Math.cos(window.mapPad.rad) / 2 - pageY*Math.sin(window.mapPad.rad)];

            window.mapPad.mouseOverTile[0] = Math.ceil(window.mapPad.mouseIsoVector[0]/69); //69 is length of one side of tile, in isometric coords
            window.mapPad.mouseOverTile[1] = Math.ceil(window.mapPad.mouseIsoVector[1]/69); //69 is length of one side of tile, in isometric coords

            //$('#topBar div').html(pageX+';'+pageY);
            //$('#topBar div').html(mapPad.mouseIsoVector[0]+';'+mapPad.mouseIsoVector[1]);
            //$('#topBar div').html(mapPad.mouseOverTile[0]+';'+mapPad.mouseOverTile[1]);
        });

        //bind mouse move when mouse is pressed
        mapPadSelector.mousedown(function(e){
            if(window.tileMenu.menu){
                window.tileMenu.menu.close();
            }

            //writing vector root coordinates.
            window.mapPad.tempVector[0] = window.mapPad.mouseOverTile[0];
            window.mapPad.tempVector[1] = window.mapPad.mouseOverTile[1];

            $(this).bind('mousemove', window.mapPad.scrolling);

            return(false); //to avoid text selection
        });

        //unbind when not scrolling
        mapPadSelector.mouseup(function(){
            $(this).unbind('mousemove', window.mapPad.scrolling);
            window.map.fill().draw(); //draw map and load all the tiles
        });

        mapPadSelector.click(function(e){
            if(window.mapPad.isClick){
                let tile = window.tileCollection.getTile(window.mapPad.mouseOverTile[0] + window.map.x, window.mapPad.mouseOverTile[1] + window.map.y);

                if (tile.tileData.type !== 'none') {
                    window.tileMenu.open(window.mapPad.mouseOverTile[0] + window.map.x, window.mapPad.mouseOverTile[1] + window.map.y, e);
                }
            }
            window.mapPad.isClick = true;
        });
    };

    //on mouse move, we take default mouse coordinates and transform them by adding 45degree angle,
    //and decrease Y coordinate by 50%. Then coordinates should be isometric.
    scrolling(e) {
        let dX = window.mapPad.tempVector[0] - window.mapPad.mouseOverTile[0];
        let dY = window.mapPad.tempVector[1] - window.mapPad.mouseOverTile[1];

        if( (dX !== 0) || (dY !== 0) ){
            window.mapPad.isClick = false; //if map was scrolled for at least 1 tile, then it's definetely is not click ;D

            window.map.move(window.map.x + dX, window.map.y + dY);
            window.mapPad.tempVector[0] = window.mapPad.mouseOverTile[0];
            window.mapPad.tempVector[1] = window.mapPad.mouseOverTile[1];
        }
    };
}

export default MapPad;
