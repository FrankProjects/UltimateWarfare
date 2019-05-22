'use strict';

class TileMenuProjection {
    constructor(menuStructure) {
        this.id = null;
        this.menuStructure = menuStructure;
        this.submenus = [];
        this.menuHTMLElement = null;
        this.init();
    }

    init() {
        this.id = 'uiMenu'+Math.round(new Date().getTime() * Math.random());

        window.tileMenu.menus[this.id] = this;
        this.menuHTMLElement = document.createElement('div');
        this.menuHTMLElement.id = this.id;
        this.menuHTMLElement.className = 'tileMenu';
        this.menuHTMLElement.style.display = 'none';

        for(let key in this.menuStructure){
            if (this.menuStructure.hasOwnProperty(key)) {
                let itemElementContainer = document.createElement('a');
                let itemElement = document.createElement('div');
                let item = this.menuStructure[key];


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


                    let submenu = new TileMenuProjection(item.value);
                    this.submenus.push(submenu);

                    itemElementContainer.setAttribute('submenu', submenu.id);

                    $(itemElementContainer).hover(
                        function(){
                            let id = $(this).attr('submenu');
                            let top = $(this).offset().top - 1; //-1 correction
                            let left = $(this).offset().left + $(this).outerWidth()+4; //+4 correction

                            //show current submenu
                            let submenu = window.tileMenu.menus[id];
                            $(submenu.insert(left, top)).fadeIn(60);

                            //close all previously opened submenus
                            let oldSubmenus = window.tileMenu.menus[$(this).parent().attr('id')].submenus;
                            for(let key in oldSubmenus){
                                if (oldSubmenus.hasOwnProperty(key)) {
                                    let oldSubmenu = oldSubmenus[key];

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
            this.menuHTMLElement.childNodes[this.menuHTMLElement.childNodes.length - 1].setAttribute('last','true');
        }
    }

    insert(left, top) {
        let body = document.getElementsByTagName('body').item(0);
        let menu = this.menuHTMLElement;
        menu.style.left = left+'px';
        menu.style.top = top+'px';
        body.appendChild(menu);

        return(menu);
    };

    outsert() {
        $(this.menuHTMLElement).fadeOut(60, function(){
            //$(this).remove(); //remove don't revert htmlelement to state as before insert(), so instead of removing element we are just hidding it.
        });

        for(let key in this.submenus){
            let submenu = this.submenus[key];
            submenu.outsert();
        }
    };

    close() {
        $(this.menuHTMLElement).fadeOut(60, function(){
            $(this).remove();
        });

        let submenu;
        while(submenu = this.submenus.pop()){
            submenu.close();
        }
    };
}

export default TileMenuProjection;
