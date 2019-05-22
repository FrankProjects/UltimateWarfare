'use strict';

import TileMenuProjection from "./TileMenuProjection";

class TileMenu {
    constructor() {
        this.menu = null;
        this.menus = [];
    }

    //generate elements of list and show it
    //if menu was previously opened , then close it
    open(x, y, event) {
        if(this.menu){
            this.menu.close();
        }

        this.menu = new TileMenuProjection(this.generateStructure(x, y));
        $(this.menu.insert(event.pageX, event.pageY)).fadeIn(60);
    };

    close() {
        this.menu.close();
        this.menus = {};
        this.menu = null;
    };

    generateStructure(x, y) {
        let tile = window.tileCollection.getTile(x, y);

        let menuStructure = [
            {
                'type': 'action',
                'name': 'Close',
                'image': 'closeIcon.png',
                'value': function(){
                    window.tileMenu.close();
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
                    let structuresMenuStructure = [];
                    for(let key in tile.tileData.structures){
                        let structure = tile.tileData.structures[key];

                        let tpl = new Date(structure.createdAt*1000);
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
                                                let products = [];
                                                for(let key in structure.product){
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
                                                let requires = [];
                                                for(let key in structure.require){
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
                                                close();
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
                    let actions = [];

                    if(tile.tileData.owner === ''){
                        actions.push({
                            'type': 'action',
                            'name': 'buy',
                            'image': 'buyIcon.png',
                            'value': function(){
                                window.action.buyTile(tile.x, tile.y, function(newOwnerName){
                                    if(newOwnerName){
                                        tile.tileData.owner = newOwnerName;
                                        close();
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
                                window.action.getBuildingList(tile.x, tile.y, function(data){
                                    let list = [];
                                    for(let key in data){
                                        if (data.hasOwnProperty(key)) {
                                            let building = data[key];
                                            list.push({
                                                'type': 'action',
                                                'name': building.name,
                                                'image': null,
                                                'value': function(){
                                                    window.action.build(tile.x, tile.y, building.classId, function(data){
                                                        alert(data);
                                                    });
                                                }
                                            });
                                        }
                                    }

                                    let buildMenu = new TileMenuProjection(list);
                                    this.menu.submenus.push(buildMenu);
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
}

export default TileMenu;
