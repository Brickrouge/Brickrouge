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
if(!--g){c();}}});});};})(),awakeWidgets:function(a){a=a||document.id(document.body);
Object.each(BrickRouge.Widget,(function(d,c){var b=".widget"+c.hyphenate();a.getElements(b).each(function(e){if(e.retrieve("widget")){return;
}var f=new d(e,e.get("dataset"));e.store("widget",f);});}));}};if(Request.API){Request.Element=new Class({Extends:Request.API,onSuccess:function(a,c){var b=Elements.from(a.rc).shift();
if(!a.assets){this.parent(b,a,c);return;}BrickRouge.updateAssets(a.assets,function(){this.fireEvent("complete",[a,c]).fireEvent("success",[b,a,c]).callChain();
}.bind(this));}});Request.Widget=new Class({Extends:Request.Element,initialize:function(a,c,b){if(b==undefined){b={};
}b.url="widgets/"+a;b.onSuccess=c;this.parent(b);}});}Element.Properties.widget={get:function(){var d=this.retrieve("widget"),c,a,b;
if(!d){c=this.className.match(/widget(-\S+)/);if(c&&c.length){a=c[1].camelCase();
b=BrickRouge.Widget[a];if(!b){throw'Constructor "'+b+'"is not defined to create widgets of type "'+c+'"';
}d=new b(this,this.get("dataset"));this.store("widget",d);}}return d;}};Element.Properties.dataset={get:function(){var d={},b=this.attributes,c,a;
for(c=0,y=b.length;c<y;c++){a=b[c];if(!a.name.match(/^data-/)){continue;}d[a.name.substring(5).camelCase()]=a.value;
}return d;}};document.addEvent("elementsready",function(a){BrickRouge.awakeWidgets(a.target);
});window.addEvent("domready",function(){document.fireEvent("elementsready",{target:document.id(document.body)});
});document.id(document.body).addEvent("click:relay(div.alert-message a.close)",function(b,c){b.stop();
var a=c.getParent("form");if(a){a.getElements(".error").removeClass("error");}c.getParent("div.alert-message").destroy();
});document.id(document.body).addEvent("click:relay(div.alert-message a.close)",function(b,c){b.stop();
var a=c.getParent("form");if(a){a.getElements(".error").removeClass("error");}c.getParent("div.alert-message").destroy();
});BrickRouge.Popover=new Class({Implements:[Events,Options],options:{anchor:null,position:null},initialize:function(b,a){this.element=$(b);
this.setOptions(a);this.arrow=this.element.getElement(".arrow");this.actions=this.element.getElement("div.actions");
this.repositionCallback=this.reposition.bind(this,false);this.quickRepositionCallback=this.reposition.bind(this,true);
var c=!this.element.hasClass("invisible");this.element.addClass("invisible");this.iframe=null;
if(this.options.anchor){this.attachAnchor(this.options.anchor);}this.element.addEvent("click",this.onClick.bind(this));
if(c){this.open();}},attachAnchor:function(a){this.anchor=$(a);if(!this.anchor){this.anchor=$(document.body).getElement(a);
}},onClick:function(a){var b=a.target;if(b.tagName=="BUTTON"&&b.getParent("div.actions")){this.fireAction({action:b.get("data-action"),popover:this,ev:a});
}},fireAction:function(a){this.fireEvent("action",arguments);},changePositionClass:function(a){this.element.removeClass("before");
this.element.removeClass("after");this.element.removeClass("above");this.element.removeClass("below");
this.element.addClass(a);},open:function(){this.element.addClass("invisible");window.addEvents({load:this.quickRepositionCallback,resize:this.quickRepositionCallback,scroll:this.repositionCallback});
if(this.iframe){$(this.iframe.contentWindow).addEvents({load:this.quickRepositionCallback,resize:this.quickRepositionCallback,scroll:this.repositionCallback});
}this.reposition(true);this.element.removeClass("invisible");},close:function(){this.element.addClass("invisible");
this.element.dispose();window.removeEvent("load",this.quickRepositionCallback);window.removeEvent("resize",this.quickRepositionCallback);
window.removeEvent("scroll",this.repositionCallback);if(this.iframe){var a=$(this.iframe.contentWindow);
a.removeEvent("load",this.quickRepositionCallback);a.removeEvent("resize",this.quickRepositionCallback);
a.removeEvent("scroll",this.repositionCallback);}},computeAnchorBox:function(){var f=this.anchor,h,d=this.iframe,b,a,g,i,e,c;
if(d){b=d.getCoordinates();a=d.contentDocument.documentElement;aX=f.offsetLeft;aY=f.offsetTop;
aW=f.offsetWidth;aH=f.offsetHeight;g=a.clientHeight;e=a.scrollTop;aY-=e;if(aY<0){aH+=aY;
}aY=Math.max(aY,0);aH=Math.min(aH,g);i=a.clientWidth;c=a.scrollLeft;aX-=c;if(aX<0){aW+=aX;
}aX=Math.max(aX,0);aW=Math.min(aW,i);aX+=b.left;aY+=b.top;}else{h=f.getCoordinates();
aX=h.left;aY=h.top;aH=h.height;aW=h.width;}return{x:aX,y:aY,w:aW,h:aH};},computeBestPosition:function(l,m,g){var i=document.body.parentNode,n=i.scrollHeight,b=i.scrollWidth,d=l.x,c=l.y,f=l.w,a=l.h,k=d+1,e="before",o;
o=b-d-f+1;if(o>k){e="after";k=o;}o=c+1;if(o>k){e="above";k=o;}o=n-c-a+1;if(o>k){e="below";
}return e;},reposition:function(z){if(!this.anchor){return;}var B=20,k=this.actions,v,c,b,d,n,f,e,r=this.element.getSize(),l=r.x,A=r.y,i,g,D,m=document.id(document.body),E=m.getSize(),a=m.getScroll(),t=a.x,s=a.y,u=E.x,C=E.y,o={top:null,left:null},q,p;
if(z===undefined){z=this.element.getStyle("visibility")!="visible";}v=this.computeAnchorBox();
c=v.x;b=v.y;d=v.w;n=v.h;f=c+d/2-1;e=b+n/2-1;D=this.options.position||this.computeBestPosition(v,l,A);
this.changePositionClass(D);if(D=="before"||D=="after"){g=Math.round(b+(n-A)/2-1);
i=(D=="before")?c-l+1:c+d-1;i=i.limit(t+B-1,t+u-(l+B)-1);g=g.limit(s+B-1,s+C-(A+B)-1);
p=(b+n/2-1)-g;p=Math.min(A-(k?k.getSize().y:20)-10,p);p=Math.max(50,p);if(p+g-1!=e){g-=(g+p)-e;
}o.top=p;}else{i=Math.round(c+(d-l)/2-1);g=(D=="above")?b-A+1:b+n-1;i=i.limit(t+B-1,t+u-(l+B)-1);
q=((c+d/2-1)-i).limit(B,l-B);if(q+l-1!=f){i-=(i+q)-f;}o.left=q;}if(z){this.element.setStyles({left:i,top:g});
this.arrow.setStyles(o);}else{this.element.morph({left:i,top:g});this.arrow.morph(o);
}}});BrickRouge.Popover.from=function(b){var g=b.title,c=b.content,e=b.direction||"auto",f=b.actions,a=new Element("div.inner"),d;
if(g){a.adopt(new Element("h3.title",{html:g}));}a.adopt(new Element("div.content").adopt(c));
if(f=="boolean"){f=[new Element('button.cancel[data-action="cancel"]',{html:"Cancel"}),new Element('button.primary[data-action="ok"]',{html:"Ok"})];
}if(f){a.adopt(new Element("div.actions").adopt(f));}d=new Element("div.popover."+e).adopt([new Element("div.arrow"),a]);
return new BrickRouge.Popover(d,b);};BrickRouge.Widget.Popover=BrickRouge.Popover;
BrickRouge.Widget.Searchbox=new Class({Implements:BrickRouge.Utils.Busy,initialize:function(b,a){this.element=document.id(b);
}});