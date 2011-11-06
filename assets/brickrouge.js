/*!
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
var BrickRouge={Utils:{Busy:new Class({startBusy:function(){if(++this.busyNest==1){return;
}this.element.addClass("busy");},finishBusy:function(){if(--this.busyNest){return;
}this.element.removeClass("busy");}})},Widget:{},Form:new Class({Implements:[Options,Events],options:{url:null,useXHR:false},initialize:function(b,a){this.element=document.id(b);
this.setOptions(a);if(this.options.useXHR||(a&&(a.onRequest||a.onComplete||a.onFailure||a.onSuccess))){this.element.addEvent("submit",function(c){c.stop();
this.submit();}.bind(this));}},alert:function(c,b){var a,d=this.element.getElement("div.alert-message."+b)||new Element("div.alert-message."+b,{html:'<a href="#close" class="close">×</a>'});
if(typeOf(c)=="string"){c=[c];}else{if(typeOf(c)=="object"){a=c;c=[];Object.each(a,function(h,l){if(typeOf(l)=="string"&&l!="_base"){var g,k,f=this.element.elements[l],e;
if(typeOf(f)=="collection"){g=f[0].getParent("div.radio-group");k=g.getParent(".field");
if(g){g.addClass("error");}else{for(e=0,j=f.length;e<j;e++){f[e].addClass("error");
}}}else{f.addClass("error");k=f.getParent(".field");}if(k){k.addClass("error");}}if(!h||h===true){return;
}c.push(h);},this);}}if(!c.length){return;}c.each(function(e){d.adopt(new Element("p",{html:e}));
});if(!d.parentNode){d.inject(this.element,"top");}},clearAlert:function(){var a=this.element.getElements("div.alert-message");
if(a){a.destroy();}this.element.getElements(".error").removeClass("error");},submit:function(){this.fireEvent("submit",{});
this.getOperation().send(this.element);},getOperation:function(){if(this.operation){return this.operation;
}return this.operation=new Request.JSON({url:this.options.url||this.element.action,onRequest:this.request.bind(this),onComplete:this.complete.bind(this),onSuccess:this.success.bind(this),onFailure:this.failure.bind(this)});
},request:function(){this.clearAlert();this.fireEvent("request",arguments);},complete:function(){this.fireEvent("complete",arguments);
},success:function(a){if(a.success){this.alert(a.success,"success");}this.onSuccess(a);
},onSuccess:function(a){this.fireEvent("complete",arguments).fireEvent("success",arguments).callChain();
},failure:function(b){var a=JSON.decode(b.responseText);if(a&&a.errors){this.alert(a.errors,"error");
}this.fireEvent("failure",arguments);}}),updateAssets:(function(){var b=null,a=null;
return function(e,c){var d=new Array(),f=new Array(),g;if(b===null){b=new Array();
if(typeof(document_cached_css_assets)!=="undefined"){b=document_cached_css_assets;
}document.id(document.head).getElements('link[type="text/css"]').each(function(h){b.push(h.get("href"));
});}if(a===null){a=new Array();if(typeof(brickrouge_cached_js_assets)!=="undefined"){a=brickrouge_cached_js_assets;
}document.id(document.html).getElements("script").each(function(h){var i=h.get("src");
if(i){a.push(i);}});}e.css.each(function(h){if(b.indexOf(h)!=-1){return;}d.push(h);
});d.each(function(h){new Asset.css(h);b.push(h);});e.js.each(function(h){if(a.indexOf(h)!=-1){return;
}f.push(h);});g=f.length;if(!g){c();return;}f.each(function(h){new Asset.javascript(h,{onload:function(){a.push(h);
if(!--g){c();}}});});};})()};if(Request.API){Request.Element=new Class({Extends:Request.API,onSuccess:function(a,c){var b=Elements.from(a.rc).shift();
if(!a.assets){this.parent(b,a,c);return;}BrickRouge.updateAssets(a.assets,function(){this.fireEvent("complete",[a,c]).fireEvent("success",[b,a,c]).callChain();
}.bind(this));}});Request.Widget=new Class({Extends:Request.Element,initialize:function(a,c,b){if(b==undefined){b={};
}b.url="widgets/"+a;b.onSuccess=c;this.parent(b);}});}Element.Properties.widget={get:function(){var d=this.retrieve("widget"),c,a,b;
if(!d){c=this.className.match(/widget(-\S+)/);if(c&&c.length){a=c[1].camelCase();
b=BrickRouge.Widget[a];if(!b){throw'Constructor "'+b+'"is not defined to create widgets of type "'+c+'"';
}d=new b(this,this.get("dataset"));this.store("widget",d);}}return d;}};document.addEvent("elementsready",function(){Object.each(BrickRouge.Widget,(function(c,b){var a=".widget"+b.hyphenate();
$$(a).each(function(d){if(d.retrieve("widget")){return;}var e=new c(d,d.get("dataset"));
d.store("widget",e);});}));});window.addEvent("domready",function(){document.fireEvent("elementsready",{target:document.id(document.body)});
});document.id(document.body).addEvent("click:relay(div.alert-message a.close)",function(b,c){b.stop();
var a=c.getParent("form");if(a){a.getElements(".error").removeClass("error");}c.getParent("div.alert-message").destroy();
});document.id(document.body).addEvent("click:relay(div.alert-message a.close)",function(b,c){b.stop();
var a=c.getParent("form");if(a){a.getElements(".error").removeClass("error");}c.getParent("div.alert-message").destroy();
});BrickRouge.Widget.Searchbox=new Class({Implements:BrickRouge.Utils.Busy,initialize:function(b,a){this.element=document.id(b);
}});