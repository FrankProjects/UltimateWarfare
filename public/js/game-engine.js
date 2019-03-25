var FrankProjects = {};
FrankProjects.UltimateWarfare = {};
FrankProjects.UltimateWarfare.GameServer = {};
FrankProjects.UltimateWarfare.GameServer.prototype = {};
FrankProjects.UltimateWarfare.Map = {};
FrankProjects.UltimateWarfare.Map.prototype = {};

FrankProjects.UltimateWarfare.GameServer.prototype.sendMessage = function(url, message, callback) {
    console.log(message);
    var data = 'json_request=' + JSON.stringify(message);
    var self = this;
    $.post(url, data, function(msg) {
        self.onMessage(msg, callback)
    }, 'json').fail(function(message) {
        self.onError(message, callback)
    });
    console.log("message sent: ", data);

    return true
};

FrankProjects.UltimateWarfare.GameServer.prototype.onMessage = function(message, callback) {
    var data = message.data || message;

    if(typeof callback == "function") {
        callback(data)
    }

};

FrankProjects.UltimateWarfare.GameServer.prototype.onError = function(message, callback) {
    if(typeof callback == "function") {
        var err = new Error("Server request failed");
        callback(err)
    }
};

// This function creates matrix of empty tiles and calculates absolute positioning coordinates of each tile
FrankProjects.UltimateWarfare.Map.prototype.init = function(parentNode) {
    console.log('New map init!');

    map.parentNode = parentNode;

    map.width = Math.ceil( (map.parentNode.offsetWidth + 2) / (map.tileWidth + 2) ); //2 is space of 2px between each  horizontaly relative tile
    map.height = Math.ceil( map.parentNode.offsetHeight / map.tileHeight ) + 1; //1 is to fill empty "half-diamond" places

    if(! (map.width % 2)){
        map.width++
    }

    if(! (map.height % 2)){
        map.height++
    }

    var x = -(map.width+map.height-2)/2; //coordinates of very first (top left) tile
    var y = (map.height-map.width)/2; //coordinates of very first (top left) tile

    for(var i = 0; i < map.height+map.height-1; i++){
        var top = i * (map.tileHeight / 2) - map.airHeight - map.tileHeight/2;

        for(var j = 0; j < ( map.width + (i % 2) ); j++){
            var left = j * (map.tileWidth+2) - (map.tileWidth+2)/2 * (i % 2);

            var tileData = [];
            tileData['offsetX'] = x+j;
            tileData['offsetY'] = y+j;
            tileData['top'] = top;
            tileData['left'] = left;
            tileData['tileInstance'] = null;
            tileData['projectionIndex'] = null;

            map.tileList.push(tileData);
        }

        if( i % 2 ){ //if true than next row is "short", and x should decrease
            x++;
        }else{
            y--;
        }
    }

    map.rootNode = document.createElement('div');
    map.rootNode.id = 'map';
    map.rootNode.style.width = map.width*(map.tileWidth+2)-2+'px'; //2 is "horizontal gap" between tiles
    map.rootNode.style.height = (map.height-1)*map.tileHeight+'px';
    map.rootNode.style.position = 'relative';
    map.rootNode.style.overflow = 'hidden';
    map.rootNode.style.backgroundColor = '#41390f';

    map.parentNode.appendChild(map.rootNode);

    return(map);

};

map = {};
map.tileWidth = 194;
map.tileHeight = 98;
map.undergroundHeight = 10;
map.airHeight = 158;

map.tileList = [];

map.width = 0;
map.height = 0;

map.x = 0;
map.y = 0;

/*
 *  move map
 */
map.move = function(x, y){
    map.x = x;
    map.y = y;
    map.fill(false); //false to not to load data, data will be loaded separatelly on mousekeyup
    map.draw();
};

/*
 *	Function fill() fill empty cells with data - tiles of given coordinates.
 *	Param "load" - to fetch data from server or not to.
 *	If false, the tile is empty(grey), and without cachind. It's used only on map scroll, when you
 *	don't need to load tiles on every mouse movement, but only on mousekeyup.
 */
map.fill = function(load){
    if(load === undefined){
        load = true;
    }

    map.unfill();

    if(load){
        tileCollection.getTiles(map.tileList);
    }

    for(var key in map.tileList){
        var tileData = map.tileList[key];

        var x = tileData['offsetX']+map.x;
        var y = tileData['offsetY']+map.y;
        var tileInstance;

        if(tileCollection.hasTile(x, y) || load){
            tileInstance = tileCollection.getTile(x, y);
        }else{
            tileInstance = new Tile(x, y);
        }

        tileData['tileInstance'] = tileInstance;
        tileData['projectionIndex'] = tileInstance.createProjection();
    }

    return(map);
};

/*
 *	Reverse of fill()
 */
map.unfill = function(){
    for(var key in map.tileList){
        var tileData = map.tileList[key];

        if(tileData['tileInstance']){
            var tile = tileData['tileInstance'];
            var index = tileData['projectionIndex'];

            tile.deleteProjection(index);

            tileData['tileInstance'] = null;
        }
    }

    return(map);
};

/*
 *	Just shows all filled tiles. (Appends tile nodes in the map rootNode)
 */
map.draw = function(){
    if(map.rootNode.childNodes.length){
        map.flushScreen();
    }

    for(var key in map.tileList){
        var tileData = map.tileList[key];

        var tile = tileData['tileInstance'];
        var pIndex = tileData['projectionIndex'];

        var tileNode = tile.projections[pIndex];
        tileNode.style.top = tileData['top']+'px';
        tileNode.style.left = tileData['left']+'px';

        map.rootNode.appendChild(tileNode);
    }
};

/*
 *	Reverse of draw().
 */
map.flushScreen = function(){
    while(map.rootNode.firstChild){
        var child = map.rootNode.firstChild;
        map.rootNode.removeChild(child);
    }

    return(map);
};


function Tile(x, y){
    this.x = x;
    this.y = y;
    this.landWidth = map.tileWidth;
    this.landHeight = map.tileHeight;
    this.undergroundHeight = map.undergroundHeight;
    this.airHeight = map.airHeight;

    this.tileData = {"type":"none","structures":[]}; //array of tile data
    //this.loaded = 0;
    this.projections = []; //array of tile nodes of current object. - Projection registry

    //this.onRefresh = function(){}

    this.setTileData = function(data){
        this.tileData = data;
        //this.onRefresh();
    };

    /*
     *	Projection - node\element with tile.
     *	Each projection is saved in projections array, so they all can be accessed
     *	at once if needed.
     */
    this.createProjection = function(){
        var tileBlock = document.createElement('div');
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

        var structBlock = document.createElement('div');

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

            var gameUnitBlock = document.createElement('div');

            gameUnitBlock.style.position = 'absolute';
            gameUnitBlock.innerText = this.tileData['owner'];
            tileBlock.appendChild(gameUnitBlock);
        }

        this.projections.push(tileBlock);
        var index = this.projections.length-1;

        return(index);
    };

    this.refreshProjections = function(){
        for(var index in this.projections){
            this.refreshProjection(index);
        }
    };

    this.refreshProjection = function(oldIndex){
        var tileBlock = this.projections[oldIndex];
        tileBlock.style.backgroundImage = 'url(../images/map/'+this.tileData['type']+'.png)';

        var structBlock = tileBlock.childNodes[0];
        if(this.tileData['structures'].length){
            structBlock.style.backgroundImage = 'url('+this.tileData['structures'][0]['name']+'/'+this.tileData['structures'][0]['imageName']+')';
        }
    };

    /*
     *	deletes projection from array
     */
    this.deleteProjection = function(index){
        this.projections.splice(index, 1);

        return(this);
    }
}


tileCollection = Object();
/*
 *	Local collection of all tiles(instances), cache
 */
tileCollection.tiles = [];

tileCollection.hasTile = function(x, y){
    if(tileCollection.tiles[x] === undefined){
        return(false);
    }else if(tileCollection.tiles[x][y] === undefined){
        return(false);
    }else{
        return(true);
    }
};

tileCollection.setTile = function(x, y, tileInstance){
    if(tileCollection.tiles[x] === undefined){
        tileCollection.tiles[x] = [];
    }

    return(tileCollection.tiles[x][y] = tileInstance);
};

tileCollection.getTile = function(x, y){
    var tileInstance;

    if(tileCollection.hasTile(x, y)){
        tileInstance = tileCollection.tiles[x][y];
    }else{
        tileInstance = tileCollection.setTile(x, y, new Tile(x, y));

        action.getTile(x, y, function(data){
            var instance = tileCollection.getTile(x, y);
            //instance.loaded = 1;
            instance.setTileData(data);
            instance.refreshProjections();
        });
    }

    return(tileInstance);
};

tileCollection.getTiles = function(tilesList){
    var tilesCoordPairs = [];
    var instance;
    var instances = [];

    for(var key in tilesList){
        if (tilesList.hasOwnProperty(key)) {
            var tileData = tilesList[key];
            var x = tileData['offsetX']+map.x;
            var y = tileData['offsetY']+map.y;

            if(!tileCollection.hasTile(x, y)){
                tilesCoordPairs.push({'x': x, 'y': y});
                instance = tileCollection.setTile(x, y, new Tile(x, y));
            }else{
                instance = tileCollection.getTile(x, y);
            }

            instances.push(instance);
        }
    }

    //get them from server and fill in instances
    if(tilesCoordPairs.length > 0){
        action.getTiles(tilesCoordPairs, function(data){
            for(var key in data){
                if (data.hasOwnProperty(key)) {
                    var tile = data[key];
                    var instance = tileCollection.getTile(tile['x'], tile['y']);
                    //instance.loaded = 1;
                    instance.setTileData(data[key]);
                    instance.refreshProjections();
                }
            }
        });
    }

    return(instances);
};


var tileMenu = {};
tileMenu.menus = {}; //all menu instances
tileMenu.menu;//current menu instance

//generate elements of list and show it
//if menu was previously opened , then close it
tileMenu.open = function(x, y, event){
    if(tileMenu.menu){
        tileMenu.menu.close();
    }

    tileMenu.menu = new tileMenuProjection(tileMenu.generateStructure(x, y));
    $(tileMenu.menu.insert(event.pageX, event.pageY)).fadeIn(60);
};

tileMenu.close = function(){
    tileMenu.menu.close();
    tileMenu.menus = {};
    tileMenu.menu = null;
};

tileMenu.generateStructure = function(x, y){
    var tile = tileCollection.getTile(x, y);

    var menuStructure = [
        {
            'type': 'action',
            'name': 'Close',
            'image': 'closeIcon.png',
            'value': function(){
                tileMenu.close();
            }
        },
        {
            'type': 'submenu',
            'name': 'Info',
            'image': 'infoIcon.png',
            'value': [
                {
                    'type': 'data',
                    'name': 'X',
                    'image': null,
                    'value': tile.x
                },
                {
                    'type': 'data',
                    'name': 'Y',
                    'image': null,
                    'value': tile.y
                },
                {
                    'type': 'data',
                    'name': 'Z',
                    'image': null,
                    'value': tile.tileData.z
                },
                {
                    'type': 'data',
                    'name': 'Type',
                    'image': null,
                    'value': tile.tileData.type
                },
                {
                    'type': 'data',
                    'name': 'Owner',
                    'image': null,
                    'value': tile.tileData.owner
                },
                {
                    'type': 'data',
                    'name': 'Price',
                    'image': null,
                    'value': tile.tileData['price']
                }
            ]
        },
        {
            'type': 'submenu',
            'name': 'Structures',
            'image': 'homeIcon.png',
            'value': 	(function(){
                var structuresMenuStructure = [];
                for(var key in tile.tileData.structures){
                    var structure = tile.tileData.structures[key];

                    var tpl = new Date(structure.createdAt*1000);
                    structure.dateBuilt = tpl.getDate()+'.'+(tpl.getMonth()+1)+'.'+tpl.getFullYear();

                    structuresMenuStructure.push({
                        'type': 'submenu',
                        'name': structure.name,
                        'image': null,
                        'value': [
                            {
                                'type': 'submenu',
                                'name': 'Info',
                                'image': 'infoIcon.png',
                                'value': [
                                    {
                                        'type': 'data',
                                        'name': 'Income',
                                        'image': null,
                                        'value': structure.income
                                    },
                                    {
                                        'type': 'data',
                                        'name': 'Price',
                                        'image': null,
                                        'value': structure.price
                                    },
                                    {
                                        'type': 'data',
                                        'name': 'Built',
                                        'image': null,
                                        'value': structure.dateBuilt
                                    },
                                    {
                                        'type': 'submenu',
                                        'name': 'Product',
                                        'image': null,
                                        'value': (function(){
                                            var products = [];
                                            for(var key in structure.product){
                                                if (structure.product.hasOwnProperty(key)) {
                                                    products.push({
                                                        'type': 'data',
                                                        'name': structure.product[key],
                                                        'image': null,
                                                        'value': key
                                                    });
                                                }
                                            }
                                            if(!products.length){
                                                products.push({
                                                    'type': 'data',
                                                    'name': null,
                                                    'image': null,
                                                    'value': 'nothing'
                                                });
                                            }
                                            return products;
                                        })()
                                    },
                                    {
                                        'type': 'submenu',
                                        'name': 'Require',
                                        'image': null,
                                        'value': (function(){
                                            var requires = [];
                                            for(var key in structure.require){
                                                if (structure.require.hasOwnProperty(key)) {
                                                    requires.push({
                                                        'type': 'data',
                                                        'name': structure.require[key],
                                                        'image': null,
                                                        'value': key
                                                    });
                                                }
                                            }
                                            if(!requires.length){
                                                requires.push({
                                                    'type': 'data',
                                                    'name': null,
                                                    'image': null,
                                                    'value': 'nothing'
                                                });
                                            }
                                            return requires;
                                        })()
                                    }
                                ]
                            },
                            {
                                'type': 'submenu',
                                'name': 'Actions',
                                'image': null,
                                'value': [
                                    {
                                        'type': 'action',
                                        'name': 'Close',
                                        'image': 'closeIcon.png',
                                        'value': function(){
                                            tileMenu.close();
                                        }
                                    }
                                ]
                            }
                        ]
                    });
                }
                return structuresMenuStructure;
            })()
        },
        {
            'type': 'submenu',
            'name': 'Actions',
            'image': null,
            'value': (function(){
                var actions = [];

                if(tile.tileData.owner === ''){
                    actions.push({
                        'type': 'action',
                        'name': 'buy',
                        'image': 'buyIcon.png',
                        'value': function(){
                            action.buyTile(tile.x, tile.y, function(newOwnerName){
                                if(newOwnerName){
                                    tile.tileData.owner = newOwnerName;
                                    tileMenu.close();
                                }
                            });
                        }
                    });
                }

                if(tile.tileData.owner === 'admin'){
                    actions.push({
                        'type': 'action',
                        'name': 'build',
                        'image': 'buildIcon.png',
                        'value': function(event){
                            action.getBuildingList(tile.x, tile.y, function(data){
                                var list = [];
                                for(var key in data){
                                    if (data.hasOwnProperty(key)) {
                                        var building = data[key];
                                        list.push({
                                            'type': 'action',
                                            'name': building.name,
                                            'image': null,
                                            'value': function(){
                                                action.build(tile.x, tile.y, building.classId, function(data){
                                                    alert(data);
                                                });
                                            }
                                        });
                                    }
                                }

                                var buildMenu = new tileMenuProjection(list);
                                tileMenu.menu.submenus.push(buildMenu);
                                $(buildMenu.insert(event.pageX, event.pageY)).fadeIn(60);
                            });
                        }
                    });
                }

                return actions;
            })()
        }
    ];

    return(menuStructure)
};

var tileMenuProjection = function(menuStructure){

    this.id = 'uiMenu'+Math.round(new Date().getTime()*Math.random());

    tileMenu.menus[this.id] = this;

    this.submenus = [];

    this.menuHTMLElement = document.createElement('div');
    this.menuHTMLElement.id = this.id;
    this.menuHTMLElement.className = 'tileMenu';
    this.menuHTMLElement.style.display = 'none';

    for(var key in menuStructure){
        if (menuStructure.hasOwnProperty(key)) {
            var itemElementContainer = document.createElement('a');
            var itemElement = document.createElement('div');
            var item = menuStructure[key];


            //setting icon
            if(item.image){
                itemElementContainer.style.backgroundImage = 'url(\'i/tileMenu/'+item.image+'\')';
                itemElementContainer.style.backgroundSize = '16px';
            }


            //if data
            if(item.type === 'data'){
                itemElementContainer.setAttribute('hover','false');

                if(item.name){
                    itemElement.innerHTML = item.name+': '+item.value;
                }else{
                    itemElement.innerHTML = item.value;
                }
            }


            //if action
            if(item.type === 'action'){
                itemElementContainer.setAttribute('hover','true');

                itemElement.innerHTML = item.name;
                $(itemElementContainer).click(item.value);
            }


            //if submenu
            if(item.type === 'submenu'){

                //skip item, if submenu has no items.
                if(!item.value.length) continue;


                itemElementContainer.setAttribute('hover','true');

                itemElement.innerHTML = item.name;


                var submenu = new tileMenuProjection(item.value);
                this.submenus.push(submenu);

                itemElementContainer.setAttribute('submenu', submenu.id);

                $(itemElementContainer).hover(
                    function(){
                        var id = $(this).attr('submenu');
                        var top = $(this).offset().top-1; //-1 correction
                        var left = $(this).offset().left + $(this).outerWidth()+4; //+4 correction

                        //show current submenu
                        var submenu = tileMenu.menus[id];
                        $(submenu.insert(left, top)).fadeIn(60);

                        //close all previously opened submenus
                        var oldSubmenus = tileMenu.menus[$(this).parent().attr('id')].submenus;
                        for(var key in oldSubmenus){
                            if (oldSubmenus.hasOwnProperty(key)) {
                                var oldSubmenu = oldSubmenus[key];

                                if(oldSubmenu.id !== id){ //save current submenu from closure
                                    oldSubmenu.outsert();
                                }
                            }
                        }
                    },
                    function(){}
                );
            }

            itemElementContainer.appendChild(itemElement);
            this.menuHTMLElement.appendChild(itemElementContainer);
        }
    }

    //adding attribute "last" for last item in menu.
    if(this.menuHTMLElement.childNodes.length){
        this.menuHTMLElement.childNodes[this.menuHTMLElement.childNodes.length-1].setAttribute('last','true');
    }

    this.insert = function(left, top){
        var body = document.getElementsByTagName('body').item(0);
        var menu = this.menuHTMLElement;
        menu.style.left = left+'px';
        menu.style.top = top+'px';
        body.appendChild(menu);

        return(menu);
    };

    this.outsert = function(){
        $(this.menuHTMLElement).fadeOut(60, function(){
            //$(this).remove(); //remove don't revert htmlelement to state as before insert(), so instead of removing element we are just hidding it.
        });

        for(var key in this.submenus){
            var submenu = this.submenus[key];
            submenu.outsert();
        }
    };

    this.close = function(){
        $(this.menuHTMLElement).fadeOut(60, function(){
            $(this).remove();
        });

        var submenu;
        while(submenu = this.submenus.pop()){
            submenu.close();
        }
    }
};



var mapPad = {};
mapPad.mouseVector = [0, 0]; //default cordinates of mouse
mapPad.mouseIsoVector = [0, 0]; //isometric cordinates of mouse
mapPad.mouseOverTile = [0, 0]; //local map coordinates of tile, on which mouse is pointing
mapPad.tempVector = [0, 0]; //vector for temporary values, to share them between events

mapPad.isClick = true; //defines if event is click or map scroll

mapPad.rad = (Math.PI/180)*(45); //-45 degree angle in radians

mapPad.init = function(){
    //draw overlay
    var mapPadOverlay = document.createElement('div');
    mapPadOverlay.id = 'mapPad';
    mapPadOverlay.style.width = map.width*(map.tileWidth+2)-2+'px'; //2 is "horizontal gap" between tiles
    mapPadOverlay.style.height = (map.height-1)*map.tileHeight+'px';
    mapPadOverlay.style.position = 'absolute';
    mapPadOverlay.style.top = '0px';
    mapPadOverlay.style.left = '0px';
    mapPadOverlay.style.zIndex = '2';


    //mapPadContainer.appendChild(mapPadOverlay);
    map.parentNode.appendChild(mapPadOverlay);



    //default mouse coordinates transformations in the isometric coordinates
    var mapPadSelector = $('#mapPad');

    mapPadSelector.mousemove(function(e){
        var pageX = e.pageX-$(this).offset().left - ((map.tileWidth+2)*Math.ceil(map.width/2));
        var pageY = e.pageY-$(this).offset().top - (map.tileHeight*Math.floor(map.height/2));

        mapPad.mouseIsoVector[0] = [pageX*Math.sin(mapPad.rad)/2 + pageY*Math.cos(mapPad.rad)];
        mapPad.mouseIsoVector[1] = [pageX*Math.cos(mapPad.rad)/2 - pageY*Math.sin(mapPad.rad)];

        mapPad.mouseOverTile[0] = Math.ceil(mapPad.mouseIsoVector[0]/69); //69 is length of one side of tile, in isometric coords
        mapPad.mouseOverTile[1] = Math.ceil(mapPad.mouseIsoVector[1]/69); //69 is length of one side of tile, in isometric coords

        //$('#topBar div').html(pageX+';'+pageY);
        //$('#topBar div').html(mapPad.mouseIsoVector[0]+';'+mapPad.mouseIsoVector[1]);
        //$('#topBar div').html(mapPad.mouseOverTile[0]+';'+mapPad.mouseOverTile[1]);
    });



    //bind mouse move when mouse is pressed
    mapPadSelector.mousedown(function(e){
        if(tileMenu.menu){
            tileMenu.menu.close();
        }

        //writing vector root coordinates.
        mapPad.tempVector[0] = mapPad.mouseOverTile[0];
        mapPad.tempVector[1] = mapPad.mouseOverTile[1];

        $(this).bind('mousemove', mapPad.scrolling);

        return(false); //to avoid text selection
    });

    //unbind when not scrolling
    mapPadSelector.mouseup(function(){
        $(this).unbind('mousemove', mapPad.scrolling);
        map.fill().draw(); //draw map and load all the tiles
    });



    mapPadSelector.click(function(e){
        if(mapPad.isClick){
            var tile = tileCollection.getTile(mapPad.mouseOverTile[0]+map.x, mapPad.mouseOverTile[1]+map.y);

            if (tile.tileData.type !== 'none') {
                tileMenu.open(mapPad.mouseOverTile[0]+map.x, mapPad.mouseOverTile[1]+map.y, e);
            }
        }
        mapPad.isClick = true;
    });
};

//on mouse move, we take default mouse coordinates and transform them by adding 45degree angle,
//and decrease Y coordinate by 50%. Then coordinates should be isometric.
mapPad.scrolling = function(e){
    var dX = mapPad.tempVector[0] - mapPad.mouseOverTile[0];
    var dY = mapPad.tempVector[1] - mapPad.mouseOverTile[1];

    if( (dX !== 0) || (dY !== 0) ){
        mapPad.isClick = false; //if map was scrolled for at least 1 tile, then it's definetely is not click ;D

        map.move(map.x+dX, map.y+dY);
        mapPad.tempVector[0] = mapPad.mouseOverTile[0];
        mapPad.tempVector[1] = mapPad.mouseOverTile[1];
    }
};

action = {};

action.getTiles = function(coords, onResponse){
    var params = {"coords": coords};

    FrankProjects.UltimateWarfare.GameServer.prototype.sendMessage('../game/world/get-tiles', params, onResponse);
};

action.buyTile = function(x, y, onResponse){
    var params = {'x': x, 'y': y};

    FrankProjects.UltimateWarfare.GameServer.prototype.sendMessage('../game/world/buy-tile', params, onResponse);
};

action.getBuildingList = function(x, y, onResponse){
    var params = {'x': x, 'y': y};

    FrankProjects.UltimateWarfare.GameServer.prototype.sendMessage('../game/world/get-building-list', params, onResponse);
};

action.build = function(x, y, cid, onResponse){
    var params = {'x': x, 'y': y, 'cid': cid};

    FrankProjects.UltimateWarfare.GameServer.prototype.sendMessage('../game/world/build', params, onResponse);
};

