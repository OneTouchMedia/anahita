/**
 * @package		Gantry Template Framework - RocketTheme
 * @version		3.1.18 October 30, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */ 

var Gantry={init:function(){Gantry.cookie=Cookie.read("gantry-admin");Gantry.slides();Gantry.inputs();Gantry.menu();Gantry.Overlay=new Gantry.Layer();},load:function(){Gantry.tips();},inputs:function(){var a=$$(".text-short, .text-medium, .text-long, .text-color");a.addEvents({attach:function(){this.removeClass("disabled");},detach:function(){this.addClass("disabled");},set:function(b){this.value=b;},keydown:function(b){if(this.hasClass("disabled")){b.stop();return;}},keyup:function(c){if(this.hasClass("disabled")){c.stop();return;}if(Gantry.MenuItemHead){var b=Gantry.MenuItemHead.Cache[Gantry.Selection];if(!b){b=new Hash({});}b.set(this.id.replace("params",""),this.value);}}});},menu:function(){var d=document.id("paramsmenu-type");if(!d){return;}var b=d.getElements("option").getProperty("value");var c=d.getProperty("value");var a=[];d.addEvent("change",function(){var f=$$(".group-"+this.value);$$(a).setStyle("display","none");f.setStyle("display","block");var g=d.getParent().getParent().getParent().getParent().getParent().getParent().getParent().getParent().id;Gantry.adjustSlides(g);if(Gantry.MenuItemHead){Gantry.MenuItemHead.adjustSizes();}});b.each(function(h,g){var f=$$(".group-"+h);a.combine(f);});var e=d.getParent().getParent().getParent().getParent().getParent().getParent().getParent().getParent().id;Gantry.adjustSlides(e);d.fireEvent("change",null,10);},adjustSlides:function(a){Gantry.sliders[a].show();if(Cookie.read("gantry-admin")&&!Cookie.read("gantry-admin").contains(a)){Gantry.sliders[a].hide();}},tips:function(){var a=$$(".hasTip");Gantry.Tips.init(a);},cleanance:function(){var d=$$("table.paramlist tr"),a=$$(".hasTip");d.each(function(f){var e=f.getChildren();if(e.length<2){f.dispose();}else{if(!e[1].innerHTML.length){f.dispose();}}});var c=document.id("diagnostic"),b=document.id("versioncheck");if(c){c.getParent().set("colspan",2).set("id","diagnostic-wrapper").getPrevious().dispose();}if(b){b.getParent().set("colspan",2).set("id","versioncheck-wrapper").getPrevious().dispose();}},slides:function(){var a=$$(".g-title").filter(function(b){return b.getElement(".arrow");});Gantry.sliders={};a.each(function(g,e){var f=g.getNext(),c=g.getProperty("rel");if(f.hasClass("g-inner")){var b=new Fx.Slide(f,{duration:400+((f.offsetHeight/400).toInt()*200),link:"cancel",onStart:function(){if(this.open){this.wrapper.setStyle("overflow","hidden");g.getElement(".arrow").removeClass("active");}else{g.getElement(".arrow").addClass("active");}},onComplete:function(){if(this.to[1]!=0){this.wrapper.setStyle("overflow","visible");this.wrapper.getFirst().setStyle("overflow","visible");Gantry.addToCookie(f.id);}else{g.getElement(".arrow").removeClass("active");Gantry.removeFromCookie(f.id);}}});Gantry.sliders[f.id]=b;if(Cookie.read("gantry-admin")!=false){var d=Gantry.getCookieArray().indexOf(f.id);if(d!=-1){b.wrapper.setStyle("overflow","visible");b.wrapper.getFirst().setStyle("overflow","visible");b.show.delay(1,b,"");g.getElement(".arrow").addClass("active");}else{b.hide();}}else{if(GantrySlideList.indexOf(c)!=-1){b.show().show();g.getElement(".arrow").addClass("active");}else{b.hide();}}}g.addEvent("click",function(){b.toggle();});});if(Cookie.read("gantry-admin")==false){a.each(function(c,b){var d=c.getNext().getFirst().id;if(Gantry.sliders[d].open){Gantry.addToCookie(d);}});}},getCookieArray:function(){var a=Cookie.read("gantry-admin");if(!a){return"";}return a.replace(" ","").split(",");},addToCookie:function(b){var a;if(!Gantry.cookie){Gantry.cookie=Cookie.write("gantry-admin",b+",",{duration:365});}else{if(Cookie.read("gantry-admin")=="-empty-"){Cookie.write("gantry-admin","",{duration:365});}if(Gantry.getCookieArray().indexOf(b)==-1){a=Cookie.read("gantry-admin");Cookie.write("gantry-admin",a+","+b,{duration:365});}}a=Cookie.read("gantry-admin");if(a.substr(-1)==","){Cookie.write("gantry-admin",a.substr(0,a.length-1),{duration:365});}},removeFromCookie:function(b){if(!Gantry.cookie){return;}var a=Gantry.getCookieArray();if(a.indexOf(b)!=-1){a=a.erase(b);Cookie.write("gantry-admin",a.join(","),{duration:365});}if(!Cookie.read("gantry-admin").length){Cookie.write("gantry-admin","-empty-",{duration:365});}}};Gantry.Tips={init:function(a){Gantry.Tips.clearTips(a);Gantry.Tips.doTips(a);},clearTips:function(a){a.each(function(b){b.removeEvents("mouseenter");b.removeEvents("mousemove");b.removeEvents("mouseleave");b.removeEvents("trash");});},doTips:function(a){Gantry.Tips.tip={};Gantry.Tips.tip.wrapper=new Element("div",{"class":"gantry-tip-wrapper"}).inject(document.body);Gantry.Tips.tip.title=new Element("div",{"class":"gantry-tip-title"}).inject(Gantry.Tips.tip.wrapper);Gantry.Tips.tip.text=new Element("div",{"class":"gantry-tip-text"}).inject(Gantry.Tips.tip.wrapper);Gantry.Tips.tip.fx=new Fx.Tween(Gantry.Tips.tip.wrapper,{link:"cancel",duration:300}).set("opacity",0);a.each(function(c){var d=c.getParent().getParent();var b=d.getPosition();d.tooltip=[c.title.split("::")[0],c.title.split("::")[1]];c.removeProperty("title");d.addEvents({mouseenter:function(){Gantry.Tips.show(this,c);},mouseleave:function(){Gantry.Tips.hide(this);}});});},show:function(a,b){if(a){Gantry.Tips.repositionTip(a);Gantry.Tips.tip.title.set("text",a.tooltip[0]);Gantry.Tips.tip.text.set("text",a.tooltip[1]);}Gantry.Tips.tip.fx.start("opacity",1);},hide:function(a){if(a){Gantry.Tips.repositionTip(a);}Gantry.Tips.tip.fx.start("opacity",0);},repositionTip:function(b){var a=b.getCoordinates();Gantry.Tips.tip.wrapper.setStyles({top:a.top,left:a.left-Gantry.Tips.tip.wrapper.getStyle("width").toInt()});}};Gantry.Layer=new Class({Implements:[Events,Options],options:{duration:200,opacity:0.8},initialize:function(b){var a=this;this.setOptions(b);this.id=new Element("div",{id:"gantry-layer"}).inject(document.body);this.fx=new Fx.Tween(this.id,{duration:this.options.duration,link:"cancel",onComplete:function(){if(!this.to[0].value){a.open=false;}else{a.open=true;a.fireEvent("show");}}}).set("opacity",0);this.open=false;},show:function(){this.calcSizes();this.fx.start("opacity",this.options.opacity);},hide:function(){this.fireEvent("hide");this.fx.start("opacity",0);},toggle:function(){this[this.open?"hide":"show"]();},calcSizes:function(){this.id.setStyles({width:window.getScrollSize().x,height:window.getScrollSize().y});}});window.addEvent("domready",Gantry.init);window.addEvent("load",Gantry.load);var Tips=new Class({});